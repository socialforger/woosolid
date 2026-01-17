<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Importer {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu_page' ] );
        add_action( 'admin_post_woosolid_import', [ __CLASS__, 'handle_import' ] );
    }

    /*--------------------------------------------------------------
    # ADMIN PAGE
    --------------------------------------------------------------*/

    public static function add_menu_page() {
        add_submenu_page(
            'woosolid-settings',
            __( 'Importa dati WooSolid', 'woosolid' ),
            __( 'Importa', 'woosolid' ),
            'manage_options',
            'woosolid-importer',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Importazione dati WooSolid', 'woosolid' ); ?></h1>
            <p><?php esc_html_e( 'Carica un file CSV per importare prodotti solidali, campagne o punti di ritiro.', 'woosolid' ); ?></p>

            <form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <?php wp_nonce_field( 'woosolid_import', 'woosolid_import_nonce' ); ?>
                <input type="hidden" name="action" value="woosolid_import">

                <table class="form-table">
                    <tr>
                        <th><label><?php esc_html_e( 'Tipo di importazione', 'woosolid' ); ?></label></th>
                        <td>
                            <select name="woosolid_import_type" required>
                                <option value="products"><?php esc_html_e( 'Prodotti solidali', 'woosolid' ); ?></option>
                                <option value="campaigns"><?php esc_html_e( 'Campagne Charitable', 'woosolid' ); ?></option>
                                <option value="pickup"><?php esc_html_e( 'Punti di ritiro', 'woosolid' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label><?php esc_html_e( 'File CSV', 'woosolid' ); ?></label></th>
                        <td><input type="file" name="woosolid_import_file" accept=".csv" required></td>
                    </tr>
                </table>

                <?php submit_button( __( 'Importa', 'woosolid' ) ); ?>
            </form>
        </div>
        <?php
    }

    /*--------------------------------------------------------------
    # HANDLE IMPORT
    --------------------------------------------------------------*/

    public static function handle_import() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Permessi insufficienti.', 'woosolid' ) );
        }

        if ( ! isset( $_POST['woosolid_import_nonce'] ) ||
             ! wp_verify_nonce( $_POST['woosolid_import_nonce'], 'woosolid_import' ) ) {
            wp_die( __( 'Nonce non valido.', 'woosolid' ) );
        }

        if ( empty( $_FILES['woosolid_import_file']['tmp_name'] ) ) {
            wp_die( __( 'Nessun file caricato.', 'woosolid' ) );
        }

        $type = sanitize_text_field( $_POST['woosolid_import_type'] );
        $file = $_FILES['woosolid_import_file']['tmp_name'];

        $rows = self::read_csv( $file );
        if ( empty( $rows ) ) {
            wp_die( __( 'Il file CSV è vuoto o non valido.', 'woosolid' ) );
        }

        switch ( $type ) {
            case 'products':
                $report = self::import_products( $rows );
                break;

            case 'campaigns':
                $report = self::import_campaigns( $rows );
                break;

            case 'pickup':
                $report = self::import_pickup_points( $rows );
                break;

            default:
                wp_die( __( 'Tipo di importazione non valido.', 'woosolid' ) );
        }

        self::render_report( $report );
        exit;
    }

    /*--------------------------------------------------------------
    # CSV READER
    --------------------------------------------------------------*/

    protected static function read_csv( $file ) {
        $rows = [];
        if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {
            $header = null;
            while ( ( $data = fgetcsv( $handle, 10000, ';' ) ) !== false ) {
                if ( ! $header ) {
                    $header = $data;
                    continue;
                }
                $rows[] = array_combine( $header, $data );
            }
            fclose( $handle );
        }
        return $rows;
    }

    /*--------------------------------------------------------------
    # IMPORT PRODUCTS (FEE SOLIDALI)
    --------------------------------------------------------------*/

    protected static function import_products( $rows ) {
        $report = [
            'created' => 0,
            'updated' => 0,
            'errors'  => [],
        ];

        foreach ( $rows as $i => $row ) {
            $line = $i + 2;

            $title       = sanitize_text_field( $row['title'] ?? '' );
            $sku         = sanitize_text_field( $row['sku'] ?? '' );
            $price       = floatval( $row['price'] ?? 0 );
            $campaign_id = intval( $row['campaign_id'] ?? 0 );
            $fee_mode    = sanitize_text_field( $row['fee_mode'] ?? 'fixed' );
            $fee_amount  = floatval( $row['fee_amount'] ?? 0 );

            if ( ! $title || $price <= 0 || $fee_amount <= 0 ) {
                $report['errors'][] = "Linea $line: dati mancanti o non validi.";
                continue;
            }

            $product_id = wc_get_product_id_by_sku( $sku );

            if ( ! $product_id ) {
                $product_id = wp_insert_post([
                    'post_title'  => $title,
                    'post_type'   => 'product',
                    'post_status' => 'publish',
                ]);
                $report['created']++;
            } else {
                $report['updated']++;
            }

            update_post_meta( $product_id, '_price', $price );
            update_post_meta( $product_id, '_regular_price', $price );

            if ( $sku ) {
                update_post_meta( $product_id, '_sku', $sku );
            }

            update_post_meta( $product_id, '_woosolid_campaign_id', $campaign_id );
            update_post_meta( $product_id, '_woosolid_fee_mode', $fee_mode );
            update_post_meta( $product_id, '_woosolid_fee_amount', $fee_amount );
        }

        return $report;
    }

    /*--------------------------------------------------------------
    # IMPORT CAMPAIGNS
    --------------------------------------------------------------*/

    protected static function import_campaigns( $rows ) {
        $report = [
            'created' => 0,
            'updated' => 0,
            'errors'  => [],
        ];

        foreach ( $rows as $i => $row ) {
            $line = $i + 2;

            $title = sanitize_text_field( $row['title'] ?? '' );
            $goal  = floatval( $row['goal'] ?? 0 );

            if ( ! $title ) {
                $report['errors'][] = "Linea $line: titolo mancante.";
                continue;
            }

            $existing = get_page_by_title( $title, OBJECT, 'campaign' );

            if ( $existing ) {
                $campaign_id = $existing->ID;
                $report['updated']++;
            } else {
                $campaign_id = wp_insert_post([
                    'post_title'  => $title,
                    'post_type'   => 'campaign',
                    'post_status' => 'publish',
                ]);
                $report['created']++;
            }

            if ( $goal > 0 ) {
                update_post_meta( $campaign_id, '_campaign_goal', $goal );
            }
        }

        return $report;
    }

    /*--------------------------------------------------------------
    # IMPORT PICKUP POINTS
    --------------------------------------------------------------*/

    protected static function import_pickup_points( $rows ) {
        $report = [
            'created' => 0,
            'updated' => 0,
            'errors'  => [],
        ];

        foreach ( $rows as $i => $row ) {
            $line = $i + 2;

            $name    = sanitize_text_field( $row['name'] ?? '' );
            $address = sanitize_text_field( $row['address'] ?? '' );
            $hours   = sanitize_text_field( $row['hours'] ?? '' );

            if ( ! $name ) {
                $report['errors'][] = "Linea $line: nome punto ritiro mancante.";
                continue;
            }

            $existing = get_page_by_title( $name, OBJECT, 'woosolid_pickup' );

            if ( $existing ) {
                $pickup_id = $existing->ID;
                $report['updated']++;
            } else {
                $pickup_id = wp_insert_post([
                    'post_title'  => $name,
                    'post_type'   => 'woosolid_pickup',
                    'post_status' => 'publish',
                ]);
                $report['created']++;
            }

            update_post_meta( $pickup_id, '_pickup_address', $address );
            update_post_meta( $pickup_id, '_pickup_hours', $hours );
        }

        return $report;
    }

    /*--------------------------------------------------------------
    # REPORT
    --------------------------------------------------------------*/

    protected static function render_report( $report ) {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Risultato importazione', 'woosolid' ) . '</h1>';

        echo '<p><strong>' . __( 'Creati:', 'woosolid' ) . '</strong> ' . intval( $report['created'] ) . '</p>';
        echo '<p><strong>' . __( 'Aggiornati:', 'woosolid' ) . '</strong> ' . intval( $report['updated'] ) . '</p>';

        if ( ! empty( $report['errors'] ) ) {
            echo '<h2>' . __( 'Errori', 'woosolid' ) . '</h2>';
            echo '<ul>';
            foreach ( $report['errors'] as $error ) {
                echo '<li>' . esc_html( $error ) . '</li>';
            }
            echo '</ul>';
        }

        echo '<p><a href="' . admin_url( 'admin.php?page=woosolid-importer' ) . '" class="button">' . __( 'Torna all’importazione', 'woosolid' ) . '</a></p>';
        echo '</div>';
    }
}

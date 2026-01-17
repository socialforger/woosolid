<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Settings {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu_pages' ] );
    }

    /**
     * MENU WOO SOLID
     *
     * WooSolid
     *   └── Impostazioni
     *         ├── Ente gestore
     *         ├── Logistica
     *         └── Listino
     */
    public static function add_menu_pages() {

        // Menu principale WooSolid → Impostazioni
        add_menu_page(
            __( 'WooSolid', 'woosolid' ),
            'WooSolid',
            'manage_options',
            'woosolid-settings',
            [ __CLASS__, 'render_main_page' ],
            'dashicons-groups',
            56
        );

        // Ente gestore
        add_submenu_page(
            'woosolid-settings',
            __( 'Ente gestore', 'woosolid' ),
            __( 'Ente gestore', 'woosolid' ),
            'manage_options',
            'woosolid-ente',
            [ 'WooSolid_Ente', 'render_page' ]
        );

        // Logistica (spedizione + punti di ritiro)
        add_submenu_page(
            'woosolid-settings',
            __( 'Logistica', 'woosolid' ),
            __( 'Logistica', 'woosolid' ),
            'manage_options',
            'woosolid-logistica',
            [ __CLASS__, 'render_logistica_page' ]
        );

        // Listino (importer CSV)
        add_submenu_page(
            'woosolid-settings',
            __( 'Listino', 'woosolid' ),
            __( 'Listino', 'woosolid' ),
            'manage_options',
            'woosolid-importer',
            [ 'WooSolid_Importer', 'render_page' ]
        );
    }

    /**
     * Pagina principale WooSolid
     */
    public static function render_main_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Impostazioni WooSolid', 'woosolid' ); ?></h1>
            <p><?php esc_html_e( 'Gestisci tutte le configurazioni del tuo Ente.', 'woosolid' ); ?></p>
        </div>
        <?php
    }

    /**
     * Pagina Logistica (spedizione + punti di ritiro)
     */
    public static function render_logistica_page() {

        if ( isset( $_POST['woosolid_logistica_submit'] ) && check_admin_referer( 'woosolid_logistica', 'woosolid_logistica_nonce' ) ) {

            update_option( 'woosolid_enable_shipping', isset( $_POST['woosolid_enable_shipping'] ) ? 'yes' : 'no' );
            update_option( 'woosolid_enable_pickup', isset( $_POST['woosolid_enable_pickup'] ) ? 'yes' : 'no' );

            echo '<div class="updated"><p>' . esc_html__( 'Impostazioni logistica salvate.', 'woosolid' ) . '</p></div>';
        }

        $shipping = get_option( 'woosolid_enable_shipping', 'yes' );
        $pickup   = get_option( 'woosolid_enable_pickup', 'yes' );
        ?>

        <div class="wrap">
            <h1><?php esc_html_e( 'Logistica WooSolid', 'woosolid' ); ?></h1>

            <form method="post">
                <?php wp_nonce_field( 'woosolid_logistica', 'woosolid_logistica_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Abilita spedizione', 'woosolid' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woosolid_enable_shipping" value="yes" <?php checked( $shipping, 'yes' ); ?> />
                                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th><?php esc_html_e( 'Abilita punti di ritiro', 'woosolid' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woosolid_enable_pickup" value="yes" <?php checked( $pickup, 'yes' ); ?> />
                                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button( __( 'Salva impostazioni', 'woosolid' ), 'primary', 'woosolid_logistica_submit' ); ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Punti di ritiro', 'woosolid' ); ?></h2>

            <p>
                <a href="<?php echo admin_url( 'post-new.php?post_type=woosolid_pickup' ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Aggiungi nuovo punto di ritiro', 'woosolid' ); ?>
                </a>
            </p>

            <?php
            $pickup_posts = get_posts([
                'post_type'      => 'woosolid_pickup',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]);

            if ( empty( $pickup_posts ) ) {
                echo '<p>' . esc_html__( 'Nessun punto di ritiro configurato.', 'woosolid' ) . '</p>';
            } else {
                echo '<table class="widefat striped">';
                echo '<thead><tr>';
                echo '<th>' . esc_html__( 'Nome', 'woosolid' ) . '</th>';
                echo '<th>' . esc_html__( 'Indirizzo', 'woosolid' ) . '</th>';
                echo '<th>' . esc_html__( 'Azioni', 'woosolid' ) . '</th>';
                echo '</tr></thead><tbody>';

                foreach ( $pickup_posts as $pickup ) {
                    $address = get_post_meta( $pickup->ID, '_woosolid_pickup_address', true );
                    echo '<tr>';
                    echo '<td>' . esc_html( $pickup->post_title ) . '</td>';
                    echo '<td>' . esc_html( $address ) . '</td>';
                    echo '<td>';
                    echo '<a href="' . admin_url( 'post.php?post=' . $pickup->ID . '&action=edit' ) . '">' . esc_html__( 'Modifica', 'woosolid' ) . '</a> | ';
                    echo '<a href="' . get_delete_post_link( $pickup->ID ) . '">' . esc_html__( 'Elimina', 'woosolid' ) . '</a>';
                    echo '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
            }
            ?>
        </div>

        <?php
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Importer {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
    }

    public static function menu() {
        add_submenu_page(
            'woosolid-settings',
            __( 'Import listino', 'woosolid' ),
            __( 'Import listino', 'woosolid' ),
            'manage_options',
            'woosolid-import',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function render_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Import listino WooSolid', 'woosolid' ) . '</h1>';

        if ( ! empty( $_FILES['woosolid_csv']['tmp_name'] ) && check_admin_referer( 'woosolid_import' ) ) {
            self::process_csv( $_FILES['woosolid_csv']['tmp_name'] );
        }

        echo '<form method="post" enctype="multipart/form-data">';
        wp_nonce_field( 'woosolid_import' );
        ?>
        <p>
            <input type="file" name="woosolid_csv" accept=".csv" required />
        </p>
        <?php
        submit_button( __( 'Importa', 'woosolid' ) );
        echo '</form></div>';
    }

    private static function process_csv( $file ) {
        $handle = fopen( $file, 'r' );
        if ( ! $handle ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Impossibile leggere il file CSV.', 'woosolid' ) . '</p></div>';
            return;
        }

        $header = fgetcsv( $handle, 0, ';' );
        if ( ! $header ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Header CSV non valido.', 'woosolid' ) . '</p></div>';
            fclose( $handle );
            return;
        }

        $count = 0;
        while ( ( $row = fgetcsv( $handle, 0, ';' ) ) !== false ) {
            $data = array_combine( $header, $row );
            if ( ! $data ) continue;

            self::create_product_from_row( $data );
            $count++;
        }

        fclose( $handle );

        echo '<div class="notice notice-success"><p>' . sprintf( esc_html__( 'Importati %d prodotti.', 'woosolid' ), $count ) . '</p></div>';
    }

    private static function create_product_from_row( $data ) {
        if ( ! class_exists( 'WC_Product_Simple' ) ) return;

        $name   = $data['nome'] ?? ( $data['descrizione'] ?? __( 'Prodotto senza nome', 'woosolid' ) );
        $price  = isset( $data['prezzo_totale'] ) ? (float) $data['prezzo_totale'] : ( isset( $data['prezzo'] ) ? (float) $data['prezzo'] : 0 );
        $weight = isset( $data['peso'] ) ? (float) $data['peso'] : 1;
        $sku    = $data['codice'] ?? '';

        $product = new WC_Product_Simple();
        $product->set_name( $name );
        $product->set_regular_price( $price );
        $product->set_weight( $weight );
        if ( $sku ) {
            $product->set_sku( $sku );
        }

        if ( ! empty( $data['note'] ) ) {
            $product->set_short_description( $data['note'] );
        }

        $product_id = $product->save();

        if ( isset( $data['note'] ) ) {
            update_post_meta( $product_id, '_woosolid_note', $data['note'] );
        }

        if ( isset( $data['categoria'] ) ) {
            $term = term_exists( $data['categoria'], 'product_cat' );
            if ( ! $term ) {
                $term = wp_insert_term( $data['categoria'], 'product_cat' );
            }
            if ( ! is_wp_error( $term ) ) {
                wp_set_post_terms( $product_id, [ $term['term_id'] ], 'product_cat' );
            }
        }
    }
}

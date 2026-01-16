<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Import {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
    }

    public static function add_menu() {
        add_submenu_page(
            'woosolid',
            __( 'Listino Solidale', 'woosolid' ),
            __( 'Listino Solidale', 'woosolid' ),
            'manage_woocommerce',
            'woosolid-price-list',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Importa Listino Prodotti', 'woosolid' ); ?></h1>
            <p><?php esc_html_e( 'Usa il CSV con colonne: SKU, Nome, Descrizione, Prezzo, Peso, Categoria, Stock…', 'woosolid' ); ?></p>
            <p>
                <a class="button button-primary"
                   href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_csv_importer' ) ); ?>">
                    <?php esc_html_e( 'Vai all’importatore WooCommerce', 'woosolid' ); ?>
                </a>
            </p>
        </div>
        <?php
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Importer {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu_page' ] );
    }

    public static function add_menu_page() {
        add_submenu_page(
            'woosolid-settings',
            __( 'Listino', 'woosolid' ),
            __( 'Listino', 'woosolid' ),
            'manage_options',
            'woosolid-importer',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Importazione Listino', 'woosolid' ); ?></h1>

            <p><?php esc_html_e( 'Qui potrai importare il listino prodotti tramite file CSV.', 'woosolid' ); ?></p>

            <p><?php esc_html_e( 'Questa è una struttura base: qui inseriremo upload, anteprima e importazione.', 'woosolid' ); ?></p>
        </div>
        <?php
    }
}

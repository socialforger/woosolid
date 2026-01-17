<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Settings {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function register_menu() {

        // MENU PRINCIPALE
        add_menu_page(
            'WooSolid',
            'WooSolid',
            'manage_options',
            'woosolid-settings',
            [ __CLASS__, 'render_settings_page' ],
            'dashicons-groups',
            56
        );

        // 1) IMPOSTAZIONI
        add_submenu_page(
            'woosolid-settings',
            'Impostazioni',
            'Impostazioni',
            'manage_options',
            'woosolid-settings',
            [ __CLASS__, 'render_settings_page' ]
        );

        // 2) ENTE GESTORE
        add_submenu_page(
            'woosolid-settings',
            'Ente gestore',
            'Ente gestore',
            'manage_options',
            'woosolid-ente-gestore',
            [ __CLASS__, 'render_ente_gestore_page' ]
        );

        // 3) LISTINI
        add_submenu_page(
            'woosolid-settings',
            'Listini',
            'Listini',
            'manage_options',
            'woosolid-listino',
            [ 'WooSolid_Listino', 'render_page' ]
        );
    }

    public static function register_settings() {
        register_setting( 'woosolid_settings_group', 'woosolid_enable_shipping' );
        register_setting( 'woosolid_settings_group', 'woosolid_enable_pickup' );
    }

    /**
     * Pagina IMPOSTAZIONI
     */
    public static function render_settings_page() {

        $enable_shipping = get_option( 'woosolid_enable_shipping', 'no' );
        $enable_pickup   = get_option( 'woosolid_enable_pickup', 'no' );

        echo '<div class="wrap">';
        echo '<h1>Impostazioni WooSolid</h1>';

        echo '<form method="post" action="options.php">';
        settings_fields( 'woosolid_settings_group' );

        echo '<table class="form-table">';

        echo '<tr>';
        echo '<th scope="row">Abilita spedizione</th>';
        echo '<td><input type="checkbox" name="woosolid_enable_shipping" value="yes" ' . checked( $enable_shipping, 'yes', false ) . '> Abilita</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row">Abilita punti di ritiro</th>';
        echo '<td><input type="checkbox" name="woosolid_enable_pickup" value="yes" ' . checked( $enable_pickup, 'yes', false ) . '> Abilita</td>';
        echo '</tr>';

        echo '</table>';

        submit_button();

        echo '</form>';
        echo '</div>';
    }

    /**
     * Pagina ENTE GESTORE
     */
    public static function render_ente_gestore_page() {
        echo '<div class="wrap"><h1>Ente gestore</h1>';
        echo '<p>Qui andranno i dati dell\'ente gestore (ETS/GAS).</p>';
        echo '</div>';
    }
}

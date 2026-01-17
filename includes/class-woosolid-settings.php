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

        // 3) LISTINI (callback gestita dalla classe WooSolid_Listino)
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

        // Gruppo IMPOSTAZIONI
        register_setting( 'woosolid_settings_group', 'woosolid_enable_shipping' );
        register_setting( 'woosolid_settings_group', 'woosolid_enable_pickup' );

        // Gruppo ENTE GESTORE
        register_setting( 'woosolid_ente_settings_group', 'woosolid_ente_name' );
        register_setting( 'woosolid_ente_settings_group', 'woosolid_ente_cf' );
        register_setting( 'woosolid_ente_settings_group', 'woosolid_ente_email' );
        register_setting( 'woosolid_ente_settings_group', 'woosolid_ente_phone' );
        register_setting( 'woosolid_ente_settings_group', 'woosolid_ente_address' );
        register_setting( 'woosolid_ente_settings_group', 'woosolid_ente_iban' );
        register_setting( 'woosolid_ente_settings_group', 'woosolid_ente_notes' );
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

        $data = [
            'name'    => get_option( 'woosolid_ente_name', '' ),
            'cf'      => get_option( 'woosolid_ente_cf', '' ),
            'email'   => get_option( 'woosolid_ente_email', '' ),
            'phone'   => get_option( 'woosolid_ente_phone', '' ),
            'address' => get_option( 'woosolid_ente_address', '' ),
            'iban'    => get_option( 'woosolid_ente_iban', '' ),
            'notes'   => get_option( 'woosolid_ente_notes', '' ),
        ];

        echo '<div class="wrap">';
        echo '<h1>Ente gestore</h1>';

        echo '<form method="post" action="options.php">';
        settings_fields( 'woosolid_ente_settings_group' );

        echo '<table class="form-table">';

        echo '<tr><th scope="row">Nome ente</th><td>';
        echo '<input type="text" name="woosolid_ente_name" value="' . esc_attr( $data['name'] ) . '" class="regular-text">';
        echo '</td></tr>';

        echo '<tr><th scope="row">Codice fiscale / P.IVA</th><td>';
        echo '<input type="text" name="woosolid_ente_cf" value="' . esc_attr( $data['cf'] ) . '" class="regular-text">';
        echo '</td></tr>';

        echo '<tr><th scope="row">Email amministrativa</th><td>';
        echo '<input type="email" name="woosolid_ente_email" value="' . esc_attr( $data['email'] ) . '" class="regular-text">';
        echo '</td></tr>';

        echo '<tr><th scope="row">Telefono</th><td>';
        echo '<input type="text" name="woosolid_ente_phone" value="' . esc_attr( $data['phone'] ) . '" class="regular-text">';
        echo '</td></tr>';

        echo '<tr><th scope="row">Indirizzo</th><td>';
        echo '<input type="text" name="woosolid_ente_address" value="' . esc_attr( $data['address'] ) . '" class="regular-text">';
        echo '</td></tr>';

        echo '<tr><th scope="row">IBAN</th><td>';
        echo '<input type="text" name="woosolid_ente_iban" value="' . esc_attr( $data['iban'] ) . '" class="regular-text">';
        echo '</td></tr>';

        echo '<tr><th scope="row">Note interne</th><td>';
        echo '<textarea name="woosolid_ente_notes" rows="4" class="large-text">' . esc_textarea( $data['notes'] ) . '</textarea>';
        echo '</td></tr>';

        echo '</table>';

        submit_button();

        echo '</form>';
        echo '</div>';
    }
}

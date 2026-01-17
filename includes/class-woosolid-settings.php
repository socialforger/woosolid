<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Settings {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu_page' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function add_menu_page() {
        add_menu_page(
            __( 'WooSolid', 'woosolid' ),
            'WooSolid',
            'manage_options',
            'woosolid-settings',
            [ __CLASS__, 'render_page' ],
            'dashicons-groups',
            56
        );
    }

    public static function register_settings() {
        register_setting( 'woosolid_settings', 'woosolid_enable_shipping' );
        register_setting( 'woosolid_settings', 'woosolid_enable_pickup' );
        register_setting( 'woosolid_settings', 'woosolid_ets_email' );
        register_setting( 'woosolid_settings', 'woosolid_ets_name' );
        register_setting( 'woosolid_settings', 'woosolid_ets_cf' );

        add_settings_section(
            'woosolid_main',
            __( 'Impostazioni WooSolid', 'woosolid' ),
            '__return_false',
            'woosolid-settings'
        );

        add_settings_field(
            'woosolid_enable_shipping',
            __( 'Abilita spedizione', 'woosolid' ),
            [ __CLASS__, 'field_enable_shipping' ],
            'woosolid-settings',
            'woosolid_main'
        );

        add_settings_field(
            'woosolid_enable_pickup',
            __( 'Abilita punti di ritiro', 'woosolid' ),
            [ __CLASS__, 'field_enable_pickup' ],
            'woosolid-settings',
            'woosolid_main'
        );

        add_settings_field(
            'woosolid_ets_email',
            __( 'Email ETS per ordini e rettifiche', 'woosolid' ),
            [ __CLASS__, 'field_ets_email' ],
            'woosolid-settings',
            'woosolid_main'
        );

        add_settings_field(
            'woosolid_ets_name',
            __( 'Denominazione ETS', 'woosolid' ),
            [ __CLASS__, 'field_ets_name' ],
            'woosolid-settings',
            'woosolid_main'
        );

        add_settings_field(
            'woosolid_ets_cf',
            __( 'Codice Fiscale ETS', 'woosolid' ),
            [ __CLASS__, 'field_ets_cf' ],
            'woosolid-settings',
            'woosolid_main'
        );
    }

    public static function field_enable_shipping() {
        $value = get_option( 'woosolid_enable_shipping', 'yes' );
        ?>
        <label>
            <input type="checkbox" name="woosolid_enable_shipping" value="yes" <?php checked( $value, 'yes' ); ?> />
            <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
        </label>
        <?php
    }

    public static function field_enable_pickup() {
        $value = get_option( 'woosolid_enable_pickup', 'yes' );
        ?>
        <label>
            <input type="checkbox" name="woosolid_enable_pickup" value="yes" <?php checked( $value, 'yes' ); ?> />
            <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
        </label>
        <?php
    }

    public static function field_ets_email() {
        $value = get_option( 'woosolid_ets_email', '' );
        ?>
        <input type="email" name="woosolid_ets_email" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php
    }

    public static function field_ets_name() {
        $value = get_option( 'woosolid_ets_name', '' );
        ?>
        <input type="text" name="woosolid_ets_name" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php
    }

    public static function field_ets_cf() {
        $value = get_option( 'woosolid_ets_cf', '' );
        ?>
        <input type="text" name="woosolid_ets_cf" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Impostazioni WooSolid', 'woosolid' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'woosolid_settings' );
                do_settings_sections( 'woosolid-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

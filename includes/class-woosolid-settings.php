<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Settings {

    const OPTION_GROUP = 'woosolid_settings';
    const OPTION_NAME  = 'woosolid_settings';

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
    }

    public static function register_settings() {
        register_setting( self::OPTION_GROUP, self::OPTION_NAME );

        add_settings_section(
            'woosolid_main',
            __( 'WooSolid Settings', 'woosolid' ),
            '__return_false',
            self::OPTION_GROUP
        );

        add_settings_field(
            'solidarity_percentage',
            __( 'Percentuale fee solidale', 'woosolid' ),
            [ __CLASS__, 'field_solidarity_percentage' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_email',
            __( 'Email per ordini', 'woosolid' ),
            [ __CLASS__, 'field_association_email' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'main_project_id',
            __( 'ID progetto principale (WP Crowdfunding)', 'woosolid' ),
            [ __CLASS__, 'field_main_project_id' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_name',
            __( 'Nome Associazione', 'woosolid' ),
            [ __CLASS__, 'field_association_name' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_legal_form',
            __( 'Forma giuridica (APS, ETS, ODV…)', 'woosolid' ),
            [ __CLASS__, 'field_association_legal_form' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_cf',
            __( 'Codice Fiscale', 'woosolid' ),
            [ __CLASS__, 'field_association_cf' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_vat',
            __( 'Partita IVA', 'woosolid' ),
            [ __CLASS__, 'field_association_vat' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_pec',
            __( 'PEC', 'woosolid' ),
            [ __CLASS__, 'field_association_pec' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_sdi',
            __( 'Codice Univoco SDI', 'woosolid' ),
            [ __CLASS__, 'field_association_sdi' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_address',
            __( 'Indirizzo sede legale', 'woosolid' ),
            [ __CLASS__, 'field_association_address' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_city',
            __( 'Città', 'woosolid' ),
            [ __CLASS__, 'field_association_city' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_postcode',
            __( 'CAP', 'woosolid' ),
            [ __CLASS__, 'field_association_postcode' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_province',
            __( 'Provincia', 'woosolid' ),
            [ __CLASS__, 'field_association_province' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_phone',
            __( 'Telefono', 'woosolid' ),
            [ __CLASS__, 'field_association_phone' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );

        add_settings_field(
            'association_iban',
            __( 'IBAN', 'woosolid' ),
            [ __CLASS__, 'field_association_iban' ],
            self::OPTION_GROUP,
            'woosolid_main'
        );
    }

    public static function get_settings() {
        $defaults = [
            'solidarity_percentage'   => 10,
            'association_email'       => get_option( 'admin_email' ),
            'main_project_id'         => '',
            'association_name'        => '',
            'association_legal_form'  => '',
            'association_cf'          => '',
            'association_vat'         => '',
            'association_pec'         => '',
            'association_sdi'         => '',
            'association_address'     => '',
            'association_city'        => '',
            'association_postcode'    => '',
            'association_province'    => '',
            'association_phone'       => '',
            'association_iban'        => '',
        ];
        return wp_parse_args( get_option( self::OPTION_NAME, [] ), $defaults );
    }

    public static function add_menu() {
        add_menu_page(
            __( 'WooSolid', 'woosolid' ),
            __( 'WooSolid', 'woosolid' ),
            'manage_woocommerce',
            'woosolid',
            [ __CLASS__, 'render_page' ],
            'dashicons-heart',
            56
        );
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WooSolid Settings', 'woosolid' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::OPTION_GROUP );
                submit_button();
                ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Dati di prova', 'woosolid' ); ?></h2>
            <p><?php esc_html_e( 'Puoi cancellare tutti i dati di prova generati automaticamente da WooSolid.', 'woosolid' ); ?></p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="woosolid_delete_demo">
                <?php submit_button( 'Cancella dati di prova', 'delete' ); ?>
            </form>
        </div>
        <?php
    }

    public static function field_solidarity_percentage() {
        $settings = self::get_settings();
        ?>
        <input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[solidarity_percentage]"
               value="<?php echo esc_attr( $settings['solidarity_percentage'] ); ?>" min="0" step="0.1"> %
        <?php
    }

    public static function field_association_email() {
        $settings = self::get_settings();
        ?>
        <input type="email" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_email]"
               value="<?php echo esc_attr( $settings['association_email'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_main_project_id() {
        $settings = self::get_settings();
        ?>
        <input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[main_project_id]"
               value="<?php echo esc_attr( $settings['main_project_id'] ); ?>" min="0">
        <?php
    }

    public static function field_association_name() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_name]"
               value="<?php echo esc_attr( $settings['association_name'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_legal_form() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_legal_form]"
               value="<?php echo esc_attr( $settings['association_legal_form'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_cf() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_cf]"
               value="<?php echo esc_attr( $settings['association_cf'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_vat() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_vat]"
               value="<?php echo esc_attr( $settings['association_vat'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_pec() {
        $settings = self::get_settings();
        ?>
        <input type="email" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_pec]"
               value="<?php echo esc_attr( $settings['association_pec'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_sdi() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_sdi]"
               value="<?php echo esc_attr( $settings['association_sdi'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_address() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_address]"
               value="<?php echo esc_attr( $settings['association_address'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_city() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_city]"
               value="<?php echo esc_attr( $settings['association_city'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_postcode() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_postcode]"
               value="<?php echo esc_attr( $settings['association_postcode'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_province() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_province]"
               value="<?php echo esc_attr( $settings['association_province'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_phone() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_phone]"
               value="<?php echo esc_attr( $settings['association_phone'] ); ?>" class="regular-text">
        <?php
    }

    public static function field_association_iban() {
        $settings = self::get_settings();
        ?>
        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[association_iban]"
               value="<?php echo esc_attr( $settings['association_iban'] ); ?>" class="regular-text">
        <?php
    }
}

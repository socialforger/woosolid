<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Settings {

    const OPTION_NAME = 'woosolid_settings';

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function menu() {
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
        register_setting( 'woosolid_settings_group', self::OPTION_NAME );

        add_settings_section(
            'woosolid_main',
            __( 'Impostazioni WooSolid', 'woosolid' ),
            '__return_false',
            'woosolid-settings'
        );

        self::add_field( 'enable_shipping', __( 'Abilita spedizione', 'woosolid' ) );
        self::add_field( 'enable_pickup_points', __( 'Abilita punti di ritiro', 'woosolid' ) );
        self::add_field( 'woosolid_campaign_id', __( 'ID campagna Charitable', 'woosolid' ) );
        self::add_field( 'woosolid_ets_email', __( 'Email ETS per ordini e rettifiche', 'woosolid' ) );
    }

    private static function add_field( $id, $label ) {
        add_settings_field(
            $id,
            $label,
            [ __CLASS__, 'render_field' ],
            'woosolid-settings',
            'woosolid_main',
            [ 'id' => $id ]
        );
    }

    public static function render_field( $args ) {
        $settings = self::get_settings();
        $id = $args['id'];
        $value = isset( $settings[ $id ] ) ? $settings[ $id ] : '';

        if ( in_array( $id, [ 'enable_shipping', 'enable_pickup_points' ], true ) ) {
            ?>
            <label>
                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $id . ']' ); ?>" value="1" <?php checked( $value, 1 ); ?> />
                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
            </label>
            <?php
        } else {
            ?>
            <input type="text" class="regular-text"
                   name="<?php echo esc_attr( self::OPTION_NAME . '[' . $id . ']' ); ?>"
                   value="<?php echo esc_attr( $value ); ?>" />
            <?php
        }
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Impostazioni WooSolid', 'woosolid' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'woosolid_settings_group' );
                do_settings_sections( 'woosolid-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function get_settings() {
        $saved = get_option( self::OPTION_NAME, [] );

        return wp_parse_args( $saved, [
            'enable_shipping'      => 1,
            'enable_pickup_points' => 1,
            'woosolid_campaign_id' => '',
            'woosolid_ets_email'   => '',
        ] );
    }
}

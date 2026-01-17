<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Wizard {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_page' ] );
    }

    public static function register_page() {
        add_menu_page(
            'WooSolid Setup',
            '',
            'manage_options',
            'woosolid-wizard',
            [ __CLASS__, 'render_page' ],
            '',
            1
        );
    }

    public static function render_page() {

        // Se non ancora configurato → esegui setup
        if ( get_option( 'woosolid_setup_done' ) === 'no' ) {
            self::run_setup();
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Configurazione WooSolid', 'woosolid' ); ?></h1>

            <?php if ( get_option( 'woosolid_setup_done' ) === 'running' ) : ?>

                <p><?php esc_html_e( 'Configurazione in corso…', 'woosolid' ); ?></p>
                <p><em><?php esc_html_e( 'Non chiudere questa pagina.', 'woosolid' ); ?></em></p>

            <?php else : ?>

                <p><?php esc_html_e( 'Configurazione completata!', 'woosolid' ); ?></p>

                <script>
                    setTimeout(function(){
                        window.location.href = "<?php echo admin_url( 'admin.php?page=woosolid-settings' ); ?>";
                    }, 1500);
                </script>

            <?php endif; ?>
        </div>
        <?php
    }

    public static function run_setup() {

        update_option( 'woosolid_setup_done', 'running' );

        self::setup_woocommerce();
        self::setup_shipping();
        self::setup_payments();
        self::setup_charitable();
        self::create_required_pages();
        self::create_demo_entities();

        update_option( 'woosolid_setup_done', 'yes' );
    }

    protected static function setup_woocommerce() {

        update_option( 'woocommerce_currency', 'EUR' );
        update_option( 'woocommerce_default_country', 'IT:RM' );
        update_option( 'woocommerce_currency_pos', 'right_space' );
        update_option( 'woocommerce_price_num_decimals', '2' );
        update_option( 'woocommerce_prices_include_tax', 'no' );

        // Email mittente fake
        update_option( 'woocommerce_email_from_address', 'prova@email.it' );
        update_option( 'woocommerce_email_from_name', 'WooSolid Test' );
    }

    protected static function setup_shipping() {

        // Crea zona "Ritiro"
        $zones = WC_Shipping_Zones::get_zones();
        $exists = false;

        foreach ( $zones as $zone ) {
            if ( $zone['zone_name'] === 'Ritiro' ) {
                $exists = true;
                break;
            }
        }

        if ( ! $exists ) {
            $zone = new WC_Shipping_Zone();
            $zone->set_zone_name( 'Ritiro' );
            $zone_id = $zone->save();

            $zone->add_shipping_method( 'local_pickup' );
        }
    }

    protected static function setup_payments() {

        // Stripe test mode + fake keys
        update_option( 'woocommerce_stripe_settings', [
            'enabled'       => 'yes',
            'testmode'      => 'yes',
            'publishable_key' => 'pk_test_fake',
            'secret_key'      => 'sk_test_fake',
            'title'           => 'Carta di credito (Stripe Sandbox)',
        ] );

        // Bonifico
        update_option( 'woocommerce_bacs_settings', [
            'enabled' => 'yes',
            'title'   => 'Bonifico bancario',
        ] );
    }

    protected static function setup_charitable() {
        update_option( 'charitable_disable_forms', 'yes' );
        update_option( 'charitable_disable_gateways', 'yes' );
    }

    protected static function create_required_pages() {
        // Placeholder — aggiungeremo se necessario
    }

    protected static function create_demo_entities() {
        // Placeholder — aggiungeremo se necessario
    }
}

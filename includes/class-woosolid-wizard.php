<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Wizard {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_page' ] );
    }

    /**
     * Pagina Wizard (nascosta dal menu)
     */
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

    /**
     * Render pagina Wizard
     */
    public static function render_page() {

        if ( get_option( 'woosolid_setup_done' ) === 'no' ) {
            self::run_setup();
        }

        ?>
        <div class="wrap">
            <h1>Configurazione WooSolid</h1>

            <?php if ( get_option( 'woosolid_setup_done' ) === 'running' ) : ?>

                <p>Configurazione in corso…</p>
                <p><em>Non chiudere questa pagina.</em></p>

            <?php else : ?>

                <p>Configurazione completata!</p>

                <script>
                    setTimeout(function(){
                        window.location.href = "<?php echo admin_url( 'admin.php?page=woosolid-settings' ); ?>";
                    }, 1500);
                </script>

            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Esecuzione Wizard
     */
    public static function run_setup() {

        update_option( 'woosolid_setup_done', 'running' );

        self::setup_woocommerce();
        self::setup_payments();
        self::setup_charitable();
        self::create_required_pages();
        self::create_demo_pickup();

        update_option( 'woosolid_setup_done', 'yes' );
    }

    /**
     * Configurazione WooCommerce
     */
    protected static function setup_woocommerce() {

        update_option( 'woocommerce_currency', 'EUR' );
        update_option( 'woocommerce_default_country', 'IT:RM' );
        update_option( 'woocommerce_currency_pos', 'right_space' );
        update_option( 'woocommerce_price_num_decimals', '2' );
        update_option( 'woocommerce_prices_include_tax', 'no' );

        // Email mittente placeholder
        update_option( 'woocommerce_email_from_address', 'info@woosolid.test' );
        update_option( 'woocommerce_email_from_name', 'WooSolid' );
    }

    /**
     * Configurazione metodi di pagamento
     */
    protected static function setup_payments() {

        // Stripe (sandbox)
        update_option( 'woocommerce_stripe_settings', [
            'enabled'         => 'yes',
            'testmode'        => 'yes',
            'publishable_key' => 'pk_test_fake',
            'secret_key'      => 'sk_test_fake',
            'title'           => 'Carta di credito (Stripe Sandbox)',
        ] );

        // Bonifico
        update_option( 'woocommerce_bacs_settings', [
            'enabled' => 'yes',
            'title'   => 'Bonifico bancario',
        ] );

        // Contanti (WooSolid)
        update_option( 'woocommerce_woosolid_cash_settings', [
            'enabled'     => 'yes',
            'title'       => 'Pagamento in contanti',
            'description' => 'Paga in contanti al momento della consegna o al punto di ritiro.',
        ] );
    }

    /**
     * Disattiva Charitable (se presente)
     */
    protected static function setup_charitable() {
        update_option( 'charitable_disable_forms', 'yes' );
        update_option( 'charitable_disable_gateways', 'yes' );
    }

    /**
     * Pagine richieste (placeholder)
     */
    protected static function create_required_pages() {
        // Se in futuro servono pagine dedicate, le creiamo qui
    }

    /**
     * Crea un punto di ritiro demo
     */
    protected static function create_demo_pickup() {

        $exists = get_posts([
            'post_type'      => 'woosolid_pickup',
            'posts_per_page' => 1,
        ]);

        if ( ! empty( $exists ) ) {
            return;
        }

        $post_id = wp_insert_post([
            'post_type'   => 'woosolid_pickup',
            'post_title'  => 'Punto di ritiro demo',
            'post_content'=> 'Questo è un punto di ritiro di esempio creato automaticamente dal wizard WooSolid.',
            'post_status' => 'publish',
        ]);

        if ( $post_id ) {

            update_post_meta( $post_id, '_woosolid_pickup_indirizzo', 'Via di esempio 123' );
            update_post_meta( $post_id, '_woosolid_pickup_citta', 'Roma' );
            update_post_meta( $post_id, '_woosolid_pickup_provincia', 'RM' );
            update_post_meta( $post_id, '_woosolid_pickup_nazione', 'Italia' );
            update_post_meta( $post_id, '_woosolid_pickup_orari', "Lun–Ven: 9:00–18:00\nSab: 9:00–13:00" );
            update_post_meta( $post_id, '_woosolid_pickup_referente', 'Mario Rossi' );
            update_post_meta( $post_id, '_woosolid_pickup_telefono', '+39 333 1234567' );
        }
    }
}

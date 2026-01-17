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
        self::create_woocommerce_pages();
        self::create_registration_page();

        self::setup_charitable();
        self::create_charitable_pages();

        self::create_ente_gestore();
        self::create_demo_users();
        self::create_demo_campaign();
        self::create_demo_product();
        self::create_demo_pickup();

        update_option( 'woosolid_setup_done', 'yes' );
    }

    /**
     * Configurazione WooCommerce (base)
     */
    protected static function setup_woocommerce() {

        update_option( 'woocommerce_currency', 'EUR' );
        update_option( 'woocommerce_default_country', 'IT:RM' );
        update_option( 'woocommerce_currency_pos', 'right_space' );
        update_option( 'woocommerce_price_num_decimals', '2' );
        update_option( 'woocommerce_prices_include_tax', 'no' );

        if ( ! get_option( 'woocommerce_email_from_address' ) ) {
            update_option( 'woocommerce_email_from_address', 'info@woosolid.test' );
        }
        if ( ! get_option( 'woocommerce_email_from_name' ) ) {
            update_option( 'woocommerce_email_from_name', 'WooSolid' );
        }
    }

    /**
     * Crea le pagine WooCommerce richieste
     */
    protected static function create_woocommerce_pages() {

        // Negozio
        if ( ! get_option( 'woocommerce_shop_page_id' ) ) {
            $shop_id = wp_insert_post([
                'post_title'   => 'Negozio',
                'post_type'    => 'page',
                'post_status'  => 'publish',
            ]);
            update_option( 'woocommerce_shop_page_id', $shop_id );
        }

        // Carrello
        if ( ! get_option( 'woocommerce_cart_page_id' ) ) {
            $cart_id = wp_insert_post([
                'post_title'   => 'Carrello',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => '[woocommerce_cart]',
            ]);
            update_option( 'woocommerce_cart_page_id', $cart_id );
        }

        // Checkout
        if ( ! get_option( 'woocommerce_checkout_page_id' ) ) {
            $checkout_id = wp_insert_post([
                'post_title'   => 'Checkout',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => '[woocommerce_checkout]',
            ]);
            update_option( 'woocommerce_checkout_page_id', $checkout_id );
        }

        // Il mio account
        if ( ! get_option( 'woocommerce_myaccount_page_id' ) ) {
            $account_id = wp_insert_post([
                'post_title'   => 'Il mio account',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => '[woocommerce_my_account]',
            ]);
            update_option( 'woocommerce_myaccount_page_id', $account_id );
        }

        // Termini e condizioni
        if ( ! get_option( 'woocommerce_terms_page_id' ) ) {
            $terms_id = wp_insert_post([
                'post_title'   => 'Termini e condizioni',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => 'Questi sono i termini e le condizioni del servizio.',
            ]);
            update_option( 'woocommerce_terms_page_id', $terms_id );
        }

        // Privacy Policy
        if ( ! get_option( 'wp_page_for_privacy_policy' ) ) {
            $privacy_id = wp_insert_post([
                'post_title'   => 'Privacy Policy',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => 'Questa è la privacy policy del sito.',
            ]);
            update_option( 'wp_page_for_privacy_policy', $privacy_id );
        }
    }

    /**
     * Crea pagina Registrazione e Accesso (WooCommerce)
     */
    protected static function create_registration_page() {

        if ( get_option( 'woosolid_registration_page_id' ) ) {
            return;
        }

        $page_id = wp_insert_post([
            'post_title'   => 'Registrazione e Accesso',
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => '[woocommerce_my_account]',
        ]);

        if ( $page_id ) {
            update_option( 'woosolid_registration_page_id', $page_id );
        }
    }

    /**
     * Configurazione Charitable (valuta, gateway, sync)
     */
    protected static function setup_charitable() {

        update_option( 'charitable_currency', get_option( 'woocommerce_currency', 'EUR' ) );
        update_option( 'charitable_currency_position', get_option( 'woocommerce_currency_pos', 'left' ) );
        update_option( 'charitable_decimal_separator', get_option( 'woocommerce_price_decimal_sep', ',' ) );
        update_option( 'charitable_thousands_separator', get_option( 'woocommerce_price_thousand_sep', '.' ) );
        update_option( 'charitable_number_decimals', get_option( 'woocommerce_price_num_decimals', 2 ) );

        // Stripe Charitable sandbox
        update_option( 'charitable_active_gateways', [ 'stripe' ] );
        update_option( 'charitable_default_gateway', 'stripe' );

        update_option( 'charitable_stripe_enabled', 1 );
        update_option( 'charitable_stripe_test_mode', 1 );
        update_option( 'charitable_stripe_live_mode', 0 );

        update_option( 'charitable_stripe_test_secret_key', 'sk_test_xxxxxxxxxxxxxxxxxxxxx' );
        update_option( 'charitable_stripe_test_publishable_key', 'pk_test_xxxxxxxxxxxxxxxxxxxxx' );

        update_option( 'charitable_stripe_live_secret_key', '' );
        update_option( 'charitable_stripe_live_publishable_key', '' );

        // Sync WooCommerce → Charitable
        update_option( '_woosolid_charitable_sync', 'yes' );
    }

    /**
     * Crea le pagine richieste da Charitable
     */
    protected static function create_charitable_pages() {

        // Donazione completata
        if ( ! get_option( 'charitable_donation_success_page' ) ) {
            $success_id = wp_insert_post([
                'post_title'   => 'Donazione completata',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => '[charitable_donation_receipt]',
            ]);
            update_option( 'charitable_donation_success_page', $success_id );
        }

        // Donazione fallita
        if ( ! get_option( 'charitable_donation_failed_page' ) ) {
            $failed_id = wp_insert_post([
                'post_title'   => 'Donazione fallita',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => 'La tua donazione non è andata a buon fine.',
            ]);
            update_option( 'charitable_donation_failed_page', $failed_id );
        }

        // Archivio campagne
        if ( ! get_option( 'charitable_campaigns_page_id' ) ) {
            $archive_id = wp_insert_post([
                'post_title'   => 'Campagne Solidali',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => '[charitable_campaigns]',
            ]);
            update_option( 'charitable_campaigns_page_id', $archive_id );
        }

        // Campagna singola
        if ( ! get_option( 'charitable_single_campaign_page_id' ) ) {
            $single_id = wp_insert_post([
                'post_title'   => 'Dettaglio Campagna',
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => '[charitable_single_campaign]',
            ]);
            update_option( 'charitable_single_campaign_page_id', $single_id );
        }
    }

    /**
     * Crea ente gestore (dati base)
     */
    protected static function create_ente_gestore() {

        if ( ! get_option( '_woosolid_ente_nome' ) ) {
            update_option( '_woosolid_ente_nome', 'Ente Solidale Demo' );
        }

        if ( ! get_option( '_woosolid_ente_email' ) ) {
            update_option( '_woosolid_ente_email', 'ente.demo@example.com' );
        }

        if ( ! get_option( '_woosolid_ente_cf' ) ) {
            update_option( '_woosolid_ente_cf', 'CFDEM00000000000' );
        }

        $ente_email = get_option( '_woosolid_ente_email' );
        if ( $ente_email ) {
            update_option( 'charitable_email_from_address', $ente_email );
        }
    }

    /**
     * Crea utenti demo: persona fisica e persona giuridica
     */
    protected static function create_demo_users() {

        // Persona fisica
        if ( ! email_exists( 'mario.rossi@example.com' ) ) {

            $user_id = wp_create_user(
                'mario.rossi',
                wp_generate_password( 12, true ),
                'mario.rossi@example.com'
            );

            if ( ! is_wp_error( $user_id ) ) {
                wp_update_user([
                    'ID'           => $user_id,
                    'first_name'   => 'Mario',
                    'last_name'    => 'Rossi',
                    'display_name' => 'Mario Rossi',
                ]);

                update_user_meta( $user_id, 'billing_first_name', 'Mario' );
                update_user_meta( $user_id, 'billing_last_name', 'Rossi' );
                update_user_meta( $user_id, 'billing_email', 'mario.rossi@example.com' );
                update_user_meta( $user_id, 'billing_address_1', 'Via di esempio 1' );
                update_user_meta( $user_id, 'billing_city', 'Roma' );
                update_user_meta( $user_id, 'billing_postcode', '00100' );
                update_user_meta( $user_id, 'billing_country', 'IT' );
                update_user_meta( $user_id, '_woosolid_user_type', 'persona_fisica' );
            }
        }

        // Persona giuridica
        if ( ! email_exists( 'associazione.demo@example.com' ) ) {

            $user_id = wp_create_user(
                'associazione.demo',
                wp_generate_password( 12, true ),
                'associazione.demo@example.com'
            );

            if ( ! is_wp_error( $user_id ) ) {
                wp_update_user([
                    'ID'           => $user_id,
                    'display_name' => 'Associazione Demo',
                ]);

                update_user_meta( $user_id, 'billing_company', 'Associazione Demo' );
                update_user_meta( $user_id, 'billing_email', 'associazione.demo@example.com' );
                update_user_meta( $user_id, 'billing_address_1', 'Via delle Associazioni 10' );
                update_user_meta( $user_id, 'billing_city', 'Roma' );
                update_user_meta( $user_id, 'billing_postcode', '00100' );
                update_user_meta( $user_id, 'billing_country', 'IT' );
                update_user_meta( $user_id, '_woosolid_user_type', 'persona_giuridica' );
            }
        }
    }

    /**
     * Crea campagna Charitable predefinita
     */
    protected static function create_demo_campaign() {

        $campaign_id = get_option( '_woosolid_default_campaign_id' );

        if ( $campaign_id && get_post( $campaign_id ) ) {
            return;
        }

        $campaign_id = wp_insert_post([
            'post_title'   => 'Campagna Solidale',
            'post_type'    => 'campaign',
            'post_status'  => 'publish',
            'post_content' => 'Questa è la campagna solidale predefinita creata automaticamente dal wizard WooSolid.',
        ]);

        if ( $campaign_id ) {
            update_option( '_woosolid_default_campaign_id', $campaign_id );
        }
    }

    /**
     * Crea prodotto demo "Pomodoro"
     */
    protected static function create_demo_product() {

        $existing = get_posts([
            'post_type'      => 'product',
            'posts_per_page' => 1,
            'meta_key'       => '_woosolid_demo_product',
            'meta_value'     => 'yes',
        ]);

        if ( ! empty( $existing ) ) {
            return;
        }

        // Immagine demo
        $image_id = 0;
        $image_url = 'https://upload.wikimedia.org/wikipedia/commons/8/89/Tomato_je.jpg';

        if ( ! function_exists( 'media_sideload_image' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $image_id = media_sideload_image( $image_url, 0, 'Pomodoro', 'id' );

        // Prodotto
        $product_id = wp_insert_post([
            'post_title'   => 'Pomodoro',
            'post_content' => 'Prodotto solidale di esempio generato automaticamente dal wizard WooSolid.',
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ]);

        if ( ! $product_id ) {
            return;
        }

        update_post_meta( $product_id, '_regular_price', '10' );
        update_post_meta( $product_id, '_price', '10' );
        update_post_meta( $product_id, '_sku', 'POM-001' );
        update_post_meta( $product_id, '_manage_stock', 'no' );

        update_post_meta( $product_id, '_woosolid_is_donation', 'yes' );
        update_post_meta( $product_id, '_woosolid_is_transport', 'yes' );

        $campaign_id = get_option( '_woosolid_default_campaign_id' );
        if ( $campaign_id ) {
            update_post_meta( $product_id, '_woosolid_campaign_id', $campaign_id );
        }

        if ( $image_id && ! is_wp_error( $image_id ) ) {
            set_post_thumbnail( $product_id, $image_id );
        }

        update_post_meta( $product_id, '_woosolid_demo_product', 'yes' );
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

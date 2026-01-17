<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Wizard {

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'maybe_redirect_to_wizard' ] );
        add_action( 'admin_menu', [ __CLASS__, 'add_wizard_page' ] );
    }

    public static function maybe_redirect_to_wizard() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( 'no' === get_option( 'woosolid_setup_done', 'no' ) ) {
            if ( ! isset( $_GET['page'] ) || 'woosolid-wizard' !== $_GET['page'] ) {
                wp_safe_redirect( admin_url( 'admin.php?page=woosolid-wizard' ) );
                exit;
            }
        }
    }

    public static function add_wizard_page() {
        add_submenu_page(
            null,
            __( 'WooSolid – Configurazione iniziale', 'woosolid' ),
            __( 'WooSolid – Configurazione iniziale', 'woosolid' ),
            'manage_options',
            'woosolid-wizard',
            [ __CLASS__, 'render_wizard' ]
        );
    }

    public static function render_wizard() {
        if ( isset( $_POST['woosolid_wizard_submit'] ) && check_admin_referer( 'woosolid_wizard', 'woosolid_wizard_nonce' ) ) {
            self::handle_submit();
            return;
        }

        $shipping = get_option( 'woosolid_enable_shipping', 'yes' );
        $pickup   = get_option( 'woosolid_enable_pickup', 'yes' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WooSolid – Configurazione iniziale', 'woosolid' ); ?></h1>
            <p><?php esc_html_e( 'Questo wizard configura WooCommerce, Charitable e crea alcune entità demo per iniziare.', 'woosolid' ); ?></p>

            <form method="post">
                <?php wp_nonce_field( 'woosolid_wizard', 'woosolid_wizard_nonce' ); ?>

                <h2><?php esc_html_e( 'Logistica', 'woosolid' ); ?></h2>
                <p><?php esc_html_e( 'Definisci il modello logistico di base. Potrai modificarlo in qualsiasi momento dal pannello WooSolid → Logistica.', 'woosolid' ); ?></p>

                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Abilita spedizione', 'woosolid' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woosolid_enable_shipping" value="yes" <?php checked( $shipping, 'yes' ); ?>>
                                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Abilita punti di ritiro', 'woosolid' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woosolid_enable_pickup" value="yes" <?php checked( $pickup, 'yes' ); ?>>
                                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Pagamenti', 'woosolid' ); ?></h2>
                <p><?php esc_html_e( 'WooSolid configurerà Stripe in modalità sandbox e il bonifico bancario come seconda modalità di pagamento.', 'woosolid' ); ?></p>

                <table class="form-table">
                    <tr>
                        <th><label for="woosolid_stripe_pk"><?php esc_html_e( 'Stripe Test Publishable Key', 'woosolid' ); ?></label></th>
                        <td><input type="text" name="woosolid_stripe_pk" id="woosolid_stripe_pk" class="regular-text" value=""></td>
                    </tr>
                    <tr>
                        <th><label for="woosolid_stripe_sk"><?php esc_html_e( 'Stripe Test Secret Key', 'woosolid' ); ?></label></th>
                        <td><input type="text" name="woosolid_stripe_sk" id="woosolid_stripe_sk" class="regular-text" value=""></td>
                    </tr>
                    <tr>
                        <th><label for="woosolid_bacs_iban"><?php esc_html_e( 'IBAN per bonifico bancario', 'woosolid' ); ?></label></th>
                        <td><input type="text" name="woosolid_bacs_iban" id="woosolid_bacs_iban" class="regular-text" value=""></td>
                    </tr>
                    <tr>
                        <th><label for="woosolid_bacs_bic"><?php esc_html_e( 'BIC/SWIFT per bonifico bancario', 'woosolid' ); ?></label></th>
                        <td><input type="text" name="woosolid_bacs_bic" id="woosolid_bacs_bic" class="regular-text" value=""></td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="woosolid_wizard_submit" class="button button-primary">
                        <?php esc_html_e( 'Completa configurazione', 'woosolid' ); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }

    protected static function handle_submit() {
        $shipping  = isset( $_POST['woosolid_enable_shipping'] ) ? 'yes' : 'no';
        $pickup    = isset( $_POST['woosolid_enable_pickup'] ) ? 'yes' : 'no';

        $stripe_pk = sanitize_text_field( $_POST['woosolid_stripe_pk'] ?? '' );
        $stripe_sk = sanitize_text_field( $_POST['woosolid_stripe_sk'] ?? '' );
        $bacs_iban = sanitize_text_field( $_POST['woosolid_bacs_iban'] ?? '' );
        $bacs_bic  = sanitize_text_field( $_POST['woosolid_bacs_bic'] ?? '' );

        update_option( 'woosolid_enable_shipping', $shipping );
        update_option( 'woosolid_enable_pickup', $pickup );

        // Configurazioni automatiche
        self::configure_woocommerce_shop();
        self::configure_shipping_and_pickup( $shipping, $pickup );
        self::configure_payments( $stripe_pk, $stripe_sk, $bacs_iban, $bacs_bic );
        self::configure_charitable();
        self::create_required_pages();
        self::create_demo_entities();

        update_option( 'woosolid_setup_done', 'yes' );

        wp_safe_redirect( admin_url( 'admin.php?page=woosolid-ente' ) );
        exit;
    }

    protected static function configure_woocommerce_shop() {
        // Localizzazione Italia / Roma / Lazio
        update_option( 'woocommerce_default_country', 'IT:RM' );
        update_option( 'woocommerce_store_city', 'Roma' );
        update_option( 'woocommerce_currency', 'EUR' );
        update_option( 'woocommerce_currency_pos', 'left' );
        update_option( 'woocommerce_price_decimal_sep', ',' );
        update_option( 'woocommerce_price_thousand_sep', '.' );
        update_option( 'woocommerce_price_num_decimals', '2' );
        update_option( 'woocommerce_weight_unit', 'kg' );
        update_option( 'woocommerce_dimension_unit', 'cm' );

        // Registrazione utenti obbligatoria, no guest checkout
        update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
        update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
        update_option( 'woocommerce_enable_guest_checkout', 'no' );

        // Tasse disattivate (ETS)
        update_option( 'woocommerce_calc_taxes', 'no' );
    }

    protected static function configure_shipping_and_pickup( $shipping, $pickup ) {
        if ( 'yes' === $pickup && class_exists( 'WC_Shipping_Zones' ) ) {
            $zones          = WC_Shipping_Zones::get_zones();
            $pickup_zone_id = 0;

            foreach ( $zones as $zone ) {
                if ( isset( $zone['zone_name'] ) && 'Ritiro' === $zone['zone_name'] ) {
                    $pickup_zone_id = (int) $zone['zone_id'];
                    break;
                }
            }

            if ( ! $pickup_zone_id ) {
                $zone = new WC_Shipping_Zone();
                $zone->set_zone_name( 'Ritiro' );
                $pickup_zone_id = $zone->save();
            }

            if ( $pickup_zone_id ) {
                $zone      = new WC_Shipping_Zone( $pickup_zone_id );
                $methods   = $zone->get_shipping_methods( true );
                $has_pickup = false;

                foreach ( $methods as $method ) {
                    if ( 'woosolid_pickup' === $method->id ) {
                        $has_pickup = true;
                        break;
                    }
                }

                if ( ! $has_pickup ) {
                    $zone->add_shipping_method( 'woosolid_pickup' );
                }
            }
        }

        if ( 'no' === $shipping ) {
            update_option( 'woocommerce_flat_rate_settings', [ 'enabled' => 'no' ] );
            update_option( 'woocommerce_free_shipping_settings', [ 'enabled' => 'no' ] );
            update_option( 'woocommerce_local_pickup_settings', [ 'enabled' => 'no' ] );
        }
    }

    protected static function configure_payments( $stripe_pk, $stripe_sk, $bacs_iban, $bacs_bic ) {
        $ente_name = get_option( 'woosolid_ente_denominazione', __( 'Ente di prova', 'woosolid' ) );

        // Stripe sandbox
        $stripe_settings = [
            'enabled'              => 'yes',
            'title'                => __( 'Carta di credito (Stripe Sandbox)', 'woosolid' ),
            'description'          => __( 'Pagamento sicuro tramite Stripe in modalità test.', 'woosolid' ),
            'testmode'             => 'yes',
            'test_publishable_key' => $stripe_pk ? $stripe_pk : 'INSERISCI_LA_TUA_PK_TEST',
            'test_secret_key'      => $stripe_sk ? $stripe_sk : 'INSERISCI_LA_TUA_SK_TEST',
            'capture'              => 'yes',
            'statement_descriptor' => $ente_name ? $ente_name : 'WooSolid',
        ];
        update_option( 'woocommerce_stripe_settings', $stripe_settings );

        // Bonifico bancario (BACS)
        $bacs_settings = [
            'enabled'      => 'yes',
            'title'        => __( 'Bonifico bancario', 'woosolid' ),
            'description'  => __( 'Puoi effettuare il pagamento tramite bonifico bancario. Riceverai le coordinate dopo la conferma dell’ordine.', 'woosolid' ),
            'instructions' => __( 'Effettua il bonifico alle coordinate indicate. L’ordine sarà processato dopo la verifica del pagamento.', 'woosolid' ),
        ];
        update_option( 'woocommerce_bacs_settings', $bacs_settings );

        $bacs_accounts = [
            [
                'account_name'   => $ente_name ? $ente_name : __( 'Ente del Terzo Settore', 'woosolid' ),
                'account_number' => $bacs_iban ? $bacs_iban : 'IT00X0000000000000000000000',
                'bank_name'      => __( 'Banca Etica', 'woosolid' ),
                'sort_code'      => '',
                'iban'           => $bacs_iban ? $bacs_iban : 'IT00X0000000000000000000000',
                'bic'            => $bacs_bic ? $bacs_bic : 'ETICITMMXXX',
            ],
        ];
        update_option( 'woocommerce_bacs_accounts', $bacs_accounts );

        update_option( 'woocommerce_gateway_order', [ 'stripe', 'bacs' ] );
        update_option( 'woocommerce_cod_settings', [ 'enabled' => 'no' ] );
        update_option( 'woocommerce_cheque_settings', [ 'enabled' => 'no' ] );
    }

    protected static function configure_charitable() {
        update_option( 'charitable_settings_gateways', [
            'offline' => [ 'enabled' => 0 ],
            'paypal'  => [ 'enabled' => 0 ],
        ] );

        $charitable_settings = get_option( 'charitable_settings', [] );
        if ( ! is_array( $charitable_settings ) ) {
            $charitable_settings = [];
        }

        $charitable_settings['donation_form_display'] = 'none';
        $charitable_settings['load_stripe']          = 0;
        $charitable_settings['load_paypal']          = 0;
        $charitable_settings['load_offline']         = 0;

        update_option( 'charitable_settings', $charitable_settings );
    }

    protected static function create_required_pages() {
        if ( function_exists( 'wc_create_page' ) ) {
            if ( ! get_option( 'woocommerce_myaccount_page_id' ) ) {
                wc_create_page(
                    esc_sql( _x( 'my-account', 'page_slug', 'woocommerce' ) ),
                    'woocommerce_myaccount_page_id',
                    __( 'My account', 'woocommerce' ),
                    ''
                );
            }
        }
    }

    protected static function create_demo_entities() {
        // Ente gestore demo
        if ( ! get_option( 'woosolid_ente_denominazione' ) ) {
            update_option( 'woosolid_ente_denominazione', 'Ente di prova' );
        }

        // Campagna demo
        $campaign_id       = 0;
        $existing_campaign = get_page_by_title( 'Campagna Solidale Demo', OBJECT, 'campaign' );
        if ( $existing_campaign ) {
            $campaign_id = $existing_campaign->ID;
        } else {
            $campaign_id = wp_insert_post( [
                'post_title'  => 'Campagna Solidale Demo',
                'post_type'   => 'campaign',
                'post_status' => 'publish',
            ] );
            if ( $campaign_id ) {
                update_post_meta( $campaign_id, '_campaign_goal', 1000 );
            }
        }

        // Prodotto demo
        $existing_product = get_page_by_title( 'Cassetta Solidale', OBJECT, 'product' );
        if ( ! $existing_product ) {
            $product_id = wp_insert_post( [
                'post_title'  => 'Cassetta Solidale',
                'post_type'   => 'product',
                'post_status' => 'publish',
            ] );

            if ( $product_id ) {
                update_post_meta( $product_id, '_price', 10 );
                update_post_meta( $product_id, '_regular_price', 10 );
                update_post_meta( $product_id, '_sku', 'WS-DEMO-01' );

                if ( $campaign_id ) {
                    update_post_meta( $product_id, '_woosolid_campaign_id', $campaign_id );
                    update_post_meta( $product_id, '_woosolid_fee_mode', 'fixed' );
                    update_post_meta( $product_id, '_woosolid_fee_amount', 2 );
                }
            }
        }

        // Utente persona fisica demo
        if ( ! username_exists( 'mario.rossi' ) && ! email_exists( 'mario.rossi@example.com' ) ) {
            $user_id_pf = wp_insert_user( [
                'user_login' => 'mario.rossi',
                'user_email' => 'mario.rossi@example.com',
                'first_name' => 'Mario',
                'last_name'  => 'Rossi',
                'role'       => 'customer',
                'user_pass'  => wp_generate_password(),
            ] );

            if ( ! is_wp_error( $user_id_pf ) ) {
                update_user_meta( $user_id_pf, '_woosolid_donor_type', 'physical' );
                update_user_meta( $user_id_pf, '_woosolid_cf', 'RSSMRA80A01H501U' );
            }
        }

        // Utente persona giuridica demo
        if ( ! username_exists( 'associazione.solidale' ) && ! email_exists( 'associazione@example.com' ) ) {
            $user_id_pg = wp_insert_user( [
                'user_login' => 'associazione.solidale',
                'user_email' => 'associazione@example.com',
                'first_name' => 'Associazione',
                'last_name'  => 'Solidale',
                'role'       => 'customer',
                'user_pass'  => wp_generate_password(),
            ] );

            if ( ! is_wp_error( $user_id_pg ) ) {
                update_user_meta( $user_id_pg, '_woosolid_donor_type', 'legal' );
                update_user_meta( $user_id_pg, '_woosolid_legal_name', 'Associazione Solidale APS' );
                update_user_meta( $user_id_pg, '_woosolid_legal_cf', '12345678901' );
                update_user_meta( $user_id_pg, '_woosolid_legal_ref_name', 'Mario' );
                update_user_meta( $user_id_pg, '_woosolid_legal_ref_surname', 'Rossi' );
            }
        }
    }
}

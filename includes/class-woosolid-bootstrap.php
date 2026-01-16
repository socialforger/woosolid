<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Bootstrap {

    public static function init() {

        require_once WOOSOLID_PATH . 'includes/class-woosolid-settings.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-woocommerce.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-fees.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-crowdfunding.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-import.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-emails.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-theme.php';

        WooSolid_Settings::init();
        WooSolid_WooCommerce::init();
        WooSolid_Fees::init();
        WooSolid_Crowdfunding::init();
        WooSolid_Import::init();
        WooSolid_Emails::init();
        WooSolid_Theme::init();

        add_action( 'admin_notices', [ __CLASS__, 'admin_notices' ] );
        add_action( 'admin_post_woosolid_delete_demo', [ __CLASS__, 'delete_demo_data' ] );

        // Attivazione: auto-setup completo
        register_activation_hook( WOOSOLID_PATH . 'woosolid.php', [ __CLASS__, 'auto_setup' ] );
    }

    public static function auto_setup() {

        // 1. COMPRATORE DI PROVA (ASSOCIAZIONE)
        if ( ! username_exists( 'associazione_test' ) ) {

            $user_id = wp_insert_user([
                'user_login' => 'associazione_test',
                'user_pass'  => wp_generate_password(),
                'user_email' => 'associazione@test.it',
                'first_name' => 'Associazione',
                'last_name'  => 'Test',
                'role'       => 'customer',
            ]);

            if ( ! is_wp_error( $user_id ) ) {
                update_user_meta( $user_id, '_woosolid_buyer_role', 'legal_rep' );
                update_user_meta( $user_id, '_woosolid_group_name', 'Associazione Test' );
                update_user_meta( $user_id, '_woosolid_legal_representative', 'Mario Rossi' );
                update_user_meta( $user_id, 'billing_address_1', 'Via di Test 123' );
                update_user_meta( $user_id, 'billing_city', 'Roma' );
                update_user_meta( $user_id, 'billing_postcode', '00100' );
                update_user_meta( $user_id, 'billing_country', 'IT' );
                update_user_meta( $user_id, 'billing_phone', '3331234567' );
            }
        }

        // 2. PROGETTO SOLIDALE DI PROVA (WP CROWDFUNDING)
        $existing_project = get_page_by_title( 'Progetto Solidale di Test', OBJECT, 'product' );

        if ( ! $existing_project ) {

            $project_id = wp_insert_post([
                'post_title'   => 'Progetto Solidale di Test',
                'post_type'    => 'product',
                'post_status'  => 'publish',
            ]);

            if ( $project_id ) {
                update_post_meta( $project_id, '_crowdfunding_campaign_status', 'active' );
                update_post_meta( $project_id, '_crowdfunding_goal', '10000' );
                update_post_meta( $project_id, '_crowdfunding_start_date', date('Y-m-d') );
                update_post_meta( $project_id, '_crowdfunding_end_date', date('Y-m-d', strtotime('+1 year')) );

                $settings = WooSolid_Settings::get_settings();
                $settings['main_project_id'] = $project_id;
                update_option( WooSolid_Settings::OPTION_NAME, $settings );
            }
        }

        // 3. PRODOTTO DI PROVA (WOOCOMMERCE)
        $existing_product = get_page_by_title( 'Prodotto Solidale di Test', OBJECT, 'product' );

        if ( ! $existing_product ) {

            if ( class_exists( 'WC_Product_Simple' ) ) {
                $product = new WC_Product_Simple();
                $product->set_name( 'Prodotto Solidale di Test' );
                $product->set_regular_price( 12 );
                $product->set_sku( 'TEST-001' );
                $product->set_weight( 1 );
                $product->set_stock_quantity( 100 );
                $product->set_manage_stock( true );
                $product->save();
            }
        }

        // 4. COMPILA DATI ASSOCIAZIONE VENDITRICE (SE VUOTI)
        $settings = WooSolid_Settings::get_settings();
        $changed  = false;

        $defaults = [
            'association_name'        => 'Associazione Solidale Demo',
            'association_legal_form'  => 'APS',
            'association_cf'          => 'CFDEMO12345',
            'association_vat'         => '01234567890',
            'association_pec'         => 'demo@pec.it',
            'association_sdi'         => 'AAAAAA',
            'association_address'     => 'Via della Solidarietà 1',
            'association_city'        => 'Roma',
            'association_postcode'    => '00100',
            'association_province'    => 'RM',
            'association_phone'       => '061234567',
            'association_iban'        => 'IT60X0542811101000000123456',
        ];

        foreach ( $defaults as $key => $value ) {
            if ( empty( $settings[ $key ] ) ) {
                $settings[ $key ] = $value;
                $changed = true;
            }
        }

        if ( $changed ) {
            update_option( WooSolid_Settings::OPTION_NAME, $settings );
        }

        // 5. STRIPE IN SANDBOX (PLACEHOLDER)
        $stripe = get_option( 'woocommerce_stripe_settings', [] );
        $stripe['enabled'] = 'yes';
        $stripe['testmode'] = 'yes';
        if ( empty( $stripe['test_publishable_key'] ) ) {
            $stripe['test_publishable_key'] = 'pk_test_123456789';
        }
        if ( empty( $stripe['test_secret_key'] ) ) {
            $stripe['test_secret_key'] = 'sk_test_123456789';
        }
        update_option( 'woocommerce_stripe_settings', $stripe );

        // 6. ORDINE DI PROVA + DONAZIONE DI PROVA
        if ( ! get_option( 'woosolid_demo_order_created', false ) && function_exists( 'wc_create_order' ) ) {

            $order = wc_create_order();

            $product = get_page_by_title( 'Prodotto Solidale di Test', OBJECT, 'product' );
            if ( $product ) {
                $order->add_product( wc_get_product( $product->ID ), 2 );
            }

            $address = [
                'first_name' => 'Associazione',
                'last_name'  => 'Test',
                'email'      => 'associazione@test.it',
                'phone'      => '3331234567',
                'address_1'  => 'Via di Test 123',
                'city'       => 'Roma',
                'postcode'   => '00100',
                'country'    => 'IT',
            ];

            $order->set_address( $address, 'billing' );
            $order->set_address( $address, 'shipping' );

            $order->set_payment_method( 'stripe' );
            $order->set_payment_method_title( 'Stripe Test Mode' );

            $order->update_meta_data( '_woosolid_demo_order', 'yes' );

            $order->calculate_totals();

            $order->update_status( 'processing', 'Ordine di prova WooSolid generato automaticamente.' );

            update_option( 'woosolid_demo_order_created', true );
        }
    }

    public static function admin_notices() {

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( isset( $_GET['demo_deleted'] ) ) {
            self::notice_warning( 'I dati di prova WooSolid sono stati cancellati correttamente.' );
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            self::notice_error( 'WooSolid richiede WooCommerce per funzionare correttamente.' );
            return;
        }

        if ( ! function_exists( 'wpcf_function' ) ) {
            self::notice_error( 'WooSolid richiede WP Crowdfunding Pro per registrare le donazioni.' );
            return;
        }

        $settings = WooSolid_Settings::get_settings();
        $missing  = [];

        if ( empty( $settings['main_project_id'] ) )      $missing[] = 'ID progetto principale';
        if ( empty( $settings['association_name'] ) )     $missing[] = 'Nome associazione';
        if ( empty( $settings['association_cf'] ) )       $missing[] = 'Codice Fiscale';
        if ( empty( $settings['association_address'] ) )  $missing[] = 'Indirizzo sede legale';
        if ( empty( $settings['association_city'] ) )     $missing[] = 'Città';
        if ( empty( $settings['association_postcode'] ) ) $missing[] = 'CAP';
        if ( empty( $settings['association_iban'] ) )     $missing[] = 'IBAN';

        if ( ! empty( $missing ) ) {
            $list = implode( ', ', $missing );
            self::notice_warning(
                'WooSolid non è ancora configurato correttamente. Mancano i seguenti dati obbligatori: <strong>' . esc_html( $list ) . '</strong>.'
            );
        }
    }

    private static function notice_error( $message ) {
        echo '<div class="notice notice-error"><p>' . wp_kses_post( $message ) . '</p></div>';
    }

    private static function notice_warning( $message ) {
        echo '<div class="notice notice-warning"><p>' . wp_kses_post( $message ) . '</p></div>';
    }

    public static function delete_demo_data() {

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( 'Non autorizzato.' );
        }

        // Utente di prova
        $user = get_user_by( 'login', 'associazione_test' );
        if ( $user ) {
            wp_delete_user( $user->ID );
        }

        // Progetto solidale di prova
        $project = get_page_by_title( 'Progetto Solidale di Test', OBJECT, 'product' );
        if ( $project ) {
            wp_delete_post( $project->ID, true );
        }

        // Prodotto di prova
        $product = get_page_by_title( 'Prodotto Solidale di Test', OBJECT, 'product' );
        if ( $product ) {
            wp_delete_post( $product->ID, true );
        }

        // Ordini di prova
        if ( function_exists( 'wc_get_orders' ) ) {
            $orders = wc_get_orders([
                'limit'     => -1,
                'status'    => ['processing', 'completed', 'on-hold'],
                'meta_key'  => '_woosolid_demo_order',
                'meta_value'=> 'yes',
            ]);

            foreach ( $orders as $order ) {
                wp_delete_post( $order->get_id(), true );
            }
        }

        delete_option( 'woosolid_demo_order_created' );

        // Rimuovi main_project_id se è quello di test
        $settings = WooSolid_Settings::get_settings();
        if ( isset( $settings['main_project_id'] ) ) {
            $project_id = $settings['main_project_id'];
            $project = get_post( $project_id );
            if ( $project && $project->post_title === 'Progetto Solidale di Test' ) {
                $settings['main_project_id'] = '';
                update_option( WooSolid_Settings::OPTION_NAME, $settings );
            }
        }

        wp_redirect( admin_url( 'admin.php?page=woosolid&demo_deleted=1' ) );
        exit;
    }
}

<?php
/**
 * Plugin Name: WooSolid
 * Description: Piattaforma di ecommerce mutualistico 
 * Author: Socialforger
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Costanti base
 */
define( 'WOOSOLID_VERSION', '2.0.0' );
define( 'WOOSOLID_PLUGIN_FILE', __FILE__ );
define( 'WOOSOLID_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOOSOLID_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoload minimale dei file includes
 */
function woosolid_load_includes() {

    // Helpers
    $helpers_dir = WOOSOLID_PLUGIN_DIR . 'includes/helpers/';
    if ( is_dir( $helpers_dir ) ) {
        foreach ( glob( $helpers_dir . '*.php' ) as $helper_file ) {
            require_once $helper_file;
        }
    }

    // Core classes
    require_once WOOSOLID_PLUGIN_DIR . 'includes/class-woosolid-user-meta.php';
    require_once WOOSOLID_PLUGIN_DIR . 'includes/class-woosolid-registration.php';
    require_once WOOSOLID_PLUGIN_DIR . 'includes/class-woosolid-account-edit.php';
    require_once WOOSOLID_PLUGIN_DIR . 'includes/class-woosolid-account.php';
    require_once WOOSOLID_PLUGIN_DIR . 'includes/class-woosolid-wizard.php';
    require_once WOOSOLID_PLUGIN_DIR . 'includes/class-woosolid-woocommerce.php';
    require_once WOOSOLID_PLUGIN_DIR . 'includes/class-woosolid-ente.php';

    // Altri moduli esistenti
    $optional_files = [
        'class-woosolid-bootstrap.php',
        'class-woosolid-charitable.php',
        'class-woosolid-checkout.php',
        'class-woosolid-emails.php',
        'class-woosolid-gateway-cash.php',
        'class-woosolid-importer.php',
        'class-woosolid-listino.php',
        'class-woosolid-pickup.php',
        'class-woosolid-product-metabox.php',
        'class-woosolid-settings.php',
    ];

    foreach ( $optional_files as $file ) {
        $path = WOOSOLID_PLUGIN_DIR . 'includes/' . $file;
        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
}

/**
 * Bootstrap principale
 */
function woosolid_init() {

    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    woosolid_load_includes();

    // Inizializzazione moduli core
    new WooSolid_Registration();
    new WooSolid_Account_Edit();
    new WooSolid_Account();
    new WooSolid_Wizard();
    new WooSolid_WooCommerce();

    // Moduli opzionali se presenti
    if ( class_exists( 'WooSolid_Bootstrap' ) ) {
        new WooSolid_Bootstrap();
    }

    if ( class_exists( 'WooSolid_Charitable' ) ) {
        new WooSolid_Charitable();
    }

    if ( class_exists( 'WooSolid_Checkout' ) ) {
        new WooSolid_Checkout();
    }

    if ( class_exists( 'WooSolid_Emails' ) ) {
        new WooSolid_Emails();
    }

    if ( class_exists( 'WooSolid_Gateway_Cash' ) ) {
        new WooSolid_Gateway_Cash();
    }

    if ( class_exists( 'WooSolid_Importer' ) ) {
        new WooSolid_Importer();
    }

    if ( class_exists( 'WooSolid_Listino' ) ) {
        new WooSolid_Listino();
    }

    if ( class_exists( 'WooSolid_Pickup' ) ) {
        new WooSolid_Pickup();
    }

    if ( class_exists( 'WooSolid_Product_Metabox' ) ) {
        new WooSolid_Product_Metabox();
    }

    if ( class_exists( 'WooSolid_Settings' ) ) {
        new WooSolid_Settings();
    }
}

add_action( 'plugins_loaded', 'woosolid_init', 20 );

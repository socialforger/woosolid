<?php
/**
 * Plugin Name: WooSolid
 * Description: Plugin per Woocommerce
 * Author: Socialforge
 * Version: 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WOOSOLID_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOSOLID_URL', plugin_dir_url( __FILE__ ) );

/**
 * Helpers
 */
require_once WOOSOLID_PATH . 'includes/helpers/class-woosolid-utils.php';
require_once WOOSOLID_PATH . 'includes/helpers/class-woosolid-logger.php';

/**
 * Core classes
 */
require_once WOOSOLID_PATH . 'includes/class-woosolid-settings.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-listino.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-importer.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-gateway-cash.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-direct-donation.php';

/**
 * Bootstrap (se ti serve ancora)
 */
require_once WOOSOLID_PATH . 'includes/class-woosolid-bootstrap.php';

register_activation_hook( __FILE__, [ 'WooSolid_Bootstrap', 'activate' ] );

/**
 * Inizializzazione plugin
 */
add_action( 'plugins_loaded', function() {

    // Admin: menu, impostazioni, listini, importer
    if ( is_admin() ) {
        WooSolid_Settings::init();
        WooSolid_Listino::init();
        WooSolid_Importer::init();
    }

    // Gateway contanti (si registra da solo via filter)
    // class-woosolid-gateway-cash.php contiene già add_filter()

    // Donazione diretta (solo carta)
    WooSolid_Direct_Donation::init();

    // Bootstrap (se contiene logiche aggiuntive)
    WooSolid_Bootstrap::init();
});

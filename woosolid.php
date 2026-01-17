<?php
/**
 * Plugin Name: WooSolid
 * Description: Piattaforma di ecommerce mutualistico 
 * Author: Socialforger
 * Version: 2.0.0
 * Text Domain: woosolid
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Costanti base
 */
define( 'WOOSOLID_VERSION', '2.0.0' );
define( 'WOOSOLID_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOSOLID_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include di base necessari prima del bootstrap
 * (utils + wizard + bootstrap)
 */
require_once WOOSOLID_PATH . 'includes/class-woosolid-utils.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-wizard.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-bootstrap.php';

/**
 * Hook di attivazione
 */
register_activation_hook(
    __FILE__,
    function() {
        if ( class_exists( 'WooSolid_Bootstrap' ) ) {
            WooSolid_Bootstrap::activate();
        }
    }
);

/**
 * Inizializzazione plugin
 */
add_action(
    'plugins_loaded',
    function() {
        if ( class_exists( 'WooSolid_Bootstrap' ) ) {
            WooSolid_Bootstrap::init();
        }
    },
    20
);

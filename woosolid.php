<?php
/**
 * Plugin Name: WooSolid
 * Description: Plugin per WooCommerce.
 * Author: Socialforge
 * Version: 1.0.0
 * Text Domain: woosolid
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WOOSOLID_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOSOLID_URL',  plugin_dir_url( __FILE__ ) );
define( 'WOOSOLID_VERSION', '1.0.0' );

add_action( 'plugins_loaded', function() {

    load_plugin_textdomain(
        'woosolid',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );

    require_once WOOSOLID_PATH . 'includes/class-woosolid-bootstrap.php';
    WooSolid_Bootstrap::init();
});

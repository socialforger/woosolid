<?php
/**
 * Plugin Name: WooSolid
 * Description: Plugin di configurazione per Woocommerce e WP Crowdfunding Pro.
 * Author: Socialforge
 * Version: 1.0.0
 * Text Domain: woosolid
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WOOSOLID_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOSOLID_URL', plugin_dir_url( __FILE__ ) );

function woosolid_load_textdomain() {
    load_plugin_textdomain(
        'woosolid',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'woosolid_load_textdomain' );

require_once WOOSOLID_PATH . 'includes/class-woosolid-bootstrap.php';

add_action( 'plugins_loaded', [ 'WooSolid_Bootstrap', 'init' ] );

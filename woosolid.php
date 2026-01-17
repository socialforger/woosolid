<?php
/**
 * Plugin Name: WooSolid
 * Description: Plugin per Woocommerce
 * Author: Socialforge
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WOOSOLID_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOSOLID_URL', plugin_dir_url( __FILE__ ) );

require_once WOOSOLID_PATH . 'includes/helpers/class-woosolid-utils.php';
require_once WOOSOLID_PATH . 'includes/helpers/class-woosolid-logger.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-bootstrap.php';

register_activation_hook( __FILE__, [ 'WooSolid_Bootstrap', 'activate' ] );

add_action( 'plugins_loaded', [ 'WooSolid_Bootstrap', 'init' ] );

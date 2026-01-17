<?php
/**
 * Plugin Name: WooSolid
 * Description: Estensioni civiche per WooCommerce e Charitable.
 * Author: SocialAction
 * Version: 2.0.0
 * Text Domain: woosolid
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WOOSOLID_VERSION', '2.0.0' );
define( 'WOOSOLID_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOSOLID_URL', plugin_dir_url( __FILE__ ) );

require_once WOOSOLID_PATH . 'includes/class-woosolid-utils.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-wizard.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-bootstrap.php';

register_activation_hook(
    __FILE__,
    [ 'WooSolid_Bootstrap', 'activate' ]
);

add_action(
    'plugins_loaded',
    [ 'WooSolid_Bootstrap', 'init' ],
    20
);

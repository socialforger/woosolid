<?php
/**
 * Plugin Name: WooSolid
 * Description: Plugin di integrazione WooCommerce e Charitable
 * Author: Socialforge
 * Version: 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WOOSOLID_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOSOLID_URL', plugin_dir_url( __FILE__ ) );

/**
 * Helpers
 */
require_once WOOSOLID_PATH . 'includes/helpers/class-woosolid-utils.php';
require_once WOOSOLID_PATH . 'includes/helpers/class-woosolid-logger.php';

/**
 * Core Bootstrap
 */
require_once WOOSOLID_PATH . 'includes/class-woosolid-bootstrap.php';

/**
 * WooSolid Modules
 */
require_once WOOSOLID_PATH . 'includes/class-woosolid-wizard.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-registration.php';
require_once WOOSOLID_PATH . 'includes/class-woosolid-account.php';

/**
 * Attivazione plugin
 */
register_activation_hook( __FILE__, [ 'WooSolid_Bootstrap', 'activate' ] );

/**
 * Inizializzazione plugin
 */
add_action( 'plugins_loaded', function() {

    // Core
    WooSolid_Bootstrap::init();

    // Wizard (pagina nascosta + run setup)
    WooSolid_Wizard::init();

    // Registrazione avanzata (vecchio modello WooSolid)
    // NOTA: nel file class-woosolid-registration.php NON deve esserci un altro ::init()
    WooSolid_Registration::init();

    // Account (colonne ordini + dettagli consegna)
    WooSolid_Account::init();
});

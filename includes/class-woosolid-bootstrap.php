<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Bootstrap {

    public static function init() {
        self::includes();
        self::init_modules();
    }

    private static function includes() {
        require_once WOOSOLID_PATH . 'includes/class-woosolid-settings.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-woocommerce.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-pickup.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-importer.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-charitable.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-emails.php';
        require_once WOOSOLID_PATH . 'includes/helpers/class-woosolid-utils.php';
        require_once WOOSOLID_PATH . 'includes/helpers/class-woosolid-logger.php';
    }

    private static function init_modules() {
        WooSolid_Settings::init();
        WooSolid_WooCommerce::init();
        WooSolid_Pickup::init();
        WooSolid_Importer::init();
        WooSolid_Charitable::init();
        WooSolid_Emails::init();
    }
}

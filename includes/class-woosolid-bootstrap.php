<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Bootstrap {

    public static function activate() {
        if ( ! get_option( 'woosolid_setup_done' ) ) {
            update_option( 'woosolid_setup_done', 'no' );
        }
        flush_rewrite_rules();
    }

    public static function init() {
        if ( ! WooSolid_Utils::is_woocommerce_active() || ! WooSolid_Utils::is_charitable_active() ) {
            add_action( 'admin_notices', [ __CLASS__, 'admin_notice_missing_dependencies' ] );
            return;
        }

        self::includes();
        self::init_modules();
    }

    protected static function includes() {
        require_once WOOSOLID_PATH . 'includes/class-woosolid-settings.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-wizard.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-checkout.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-woocommerce.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-product-metabox.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-pickup.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-charitable.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-emails.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-account.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-direct-donation.php';
    }

    protected static function init_modules() {
        WooSolid_Settings::init();
        WooSolid_Wizard::init();
        WooSolid_Checkout::init();
        WooSolid_WooCommerce::init();
        WooSolid_Product_Metabox::init();
        WooSolid_Pickup::init();
        WooSolid_Charitable::init();
        WooSolid_Emails::init();
        WooSolid_Account::init();
        WooSolid_Direct_Donation::init();
    }

    public static function admin_notice_missing_dependencies() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'WooSolid richiede WooCommerce e Charitable attivi.', 'woosolid' ); ?></p>
        </div>
        <?php
    }
}

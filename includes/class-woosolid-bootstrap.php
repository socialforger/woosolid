<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Bootstrap {

    public static function activate() {
        update_option( 'woosolid_setup_done', 'no' );
        flush_rewrite_rules();
    }

    public static function init() {

        // Dipendenze obbligatorie
        if ( ! WooSolid_Utils::is_woocommerce_active() || ! WooSolid_Utils::is_charitable_active() ) {
            add_action( 'admin_notices', [ __CLASS__, 'admin_notice_missing_dependencies' ] );
            return;
        }

        self::includes();
        self::init_modules();

        add_action( 'admin_init', [ __CLASS__, 'maybe_redirect_to_wizard' ] );
    }

    public static function maybe_redirect_to_wizard() {
        if ( get_option( 'woosolid_setup_done' ) === 'no' ) {
            if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'woosolid-wizard' ) {
                wp_safe_redirect( admin_url( 'admin.php?page=woosolid-wizard' ) );
                exit;
            }
        }
    }

    protected static function includes() {

        require_once WOOSOLID_PATH . 'includes/class-woosolid-settings.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-wizard.php';

        require_once WOOSOLID_PATH . 'includes/class-woosolid-charitable.php'; // 🔥 fondamentale

        require_once WOOSOLID_PATH . 'includes/class-woosolid-listino.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-importer.php';

        require_once WOOSOLID_PATH . 'includes/class-woosolid-gateway-cash.php';

        require_once WOOSOLID_PATH . 'includes/class-woosolid-account.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-pickup.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-checkout.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-woocommerce.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-product-metabox.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-emails.php';
    }

    protected static function init_modules() {

        if ( is_admin() ) {
            WooSolid_Settings::init();
            WooSolid_Wizard::init();
            WooSolid_Listino::init();
            WooSolid_Importer::init();
        }

        WooSolid_Charitable::init(); // 🔥 sincronizzazione WooCommerce → Charitable

        WooSolid_Account::init();
        WooSolid_Pickup::init();
        WooSolid_Checkout::init();
        WooSolid_WooCommerce::init();
        WooSolid_Product_Metabox::init();
        WooSolid_Emails::init();
    }

    public static function admin_notice_missing_dependencies() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'WooSolid richiede WooCommerce e Charitable attivi.', 'woosolid' ); ?></p>
        </div>
        <?php
    }
}

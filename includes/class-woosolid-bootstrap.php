<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Bootstrap {

    /**
     * Attivazione plugin
     */
    public static function activate() {

        // Il wizard non deve più comparire come pagina
        update_option( 'woosolid_setup_done', 'yes' );

        // NON eseguiamo il wizard qui (causa fatal se le classi non sono ancora caricate)
        flush_rewrite_rules();
    }

    /**
     * Inizializzazione plugin
     */
    public static function init() {

        // Controllo dipendenze
        if ( ! WooSolid_Utils::is_woocommerce_active() || ! WooSolid_Utils::is_charitable_active() ) {
            add_action( 'admin_notices', [ __CLASS__, 'admin_notice_missing_dependencies' ] );
            return;
        }

        // Carica tutte le classi
        self::includes();

        // Inizializza i moduli
        self::init_modules();

        // Wizard automatico invisibile (ora che TUTTO è caricato)
        WooSolid_Wizard::run_auto_wizard();
    }

    /**
     * Include di tutte le classi del plugin
     */
    protected static function includes() {

        // Menu WooSolid (nuovo)
        require_once WOOSOLID_PATH . 'includes/class-woosolid-admin-menu.php';

        // Wizard aggiornato (invisibile)
        require_once WOOSOLID_PATH . 'includes/class-woosolid-wizard.php';

        // Classi originali (tutte ancora esistenti)
        require_once WOOSOLID_PATH . 'includes/class-woosolid-settings.php';
        require_once WOOSOLID_PATH . 'includes/class-woosolid-charitable.php';
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

    /**
     * Inizializzazione moduli
     */
    protected static function init_modules() {

        // Moduli admin
        if ( is_admin() ) {
            WooSolid_Admin_Menu::init();   // nuovo menu WooSolid
            WooSolid_Settings::init();
            WooSolid_Listino::init();
            WooSolid_Importer::init();
        }

        // Moduli frontend + backend
        WooSolid_Charitable::init();
        WooSolid_Account::init();
        WooSolid_Pickup::init();
        WooSolid_Checkout::init();
        WooSolid_WooCommerce::init();
        WooSolid_Product_Metabox::init();
        WooSolid_Emails::init();
    }

    /**
     * Notifica dipendenze mancanti
     */
    public static function admin_notice_missing_dependencies() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'WooSolid richiede WooCommerce e Charitable attivi.', 'woosolid' ); ?></p>
        </div>
        <?php
    }
}

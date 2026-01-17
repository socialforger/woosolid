<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Settings {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu_pages' ] );
    }

    public static function add_menu_pages() {

        // Menu principale WooSolid
        add_menu_page(
            __( 'WooSolid', 'woosolid' ),
            'WooSolid',
            'manage_options',
            'woosolid',
            [ __CLASS__, 'render_main_page' ],
            'dashicons-groups',
            56
        );

        // Sottomenu: Impostazioni (redirect a Ente gestore)
        add_submenu_page(
            'woosolid',
            __( 'Impostazioni WooSolid', 'woosolid' ),
            __( 'Impostazioni', 'woosolid' ),
            'manage_options',
            'woosolid-settings',
            [ __CLASS__, 'render_settings_redirect' ]
        );

        // Sottomenu: Ente gestore
        add_submenu_page(
            'woosolid',
            __( 'Ente gestore', 'woosolid' ),
            __( 'Ente gestore', 'woosolid' ),
            'manage_options',
            'woosolid-ente',
            [ 'WooSolid_Ente', 'render_page' ]
        );

        // Sottomenu: Logistica (spedizione + punti di ritiro)
        add_submenu_page(
            'woosolid',
            __( 'Logistica', 'woosolid' ),
            __( 'Logistica', 'woosolid' ),
            'manage_options',
            'woosolid-logistica',
            [ __CLASS__, 'render_logistica_page' ]
        );

        // Sottomenu: Punti di ritiro (redirect al CPT)
        add_submenu_page(
            'woosolid',
            __( 'Punti di ritiro', 'woosolid' ),
            __( 'Punti di ritiro', 'woosolid' ),
            'manage_options',
            'woosolid-pickup',
            [ __CLASS__, 'render_pickup_redirect' ]
        );

        // Sottomenu: Listino (redirect all’importer)
        add_submenu_page(
            'woosolid',
            __( 'Listino', 'woosolid' ),
            __( 'Listino', 'woosolid' ),
            'manage_options',
            'woosolid-listino',
            [ __CLASS__, 'render_listino_redirect' ]
        );

        // Sottomenu: Configurazione iniziale (wizard)
        add_submenu_page(
            'woosolid',
            __( 'Configurazione iniziale', 'woosolid' ),
            __( 'Configurazione iniziale', 'woosolid' ),
            'manage_options',
            'woosolid-wizard',
            [ 'WooSolid_Wizard', 'render_wizard' ]
        );
    }

    public static function render_main_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WooSolid', 'woosolid' ); ?></h1>
            <p><?php esc_html_e( 'Seleziona una sezione di configurazione:', 'woosolid' ); ?></p>
            <ul>
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=woosolid-ente' ) ); ?>">
                    <?php esc_html_e( 'Ente gestore', 'woosolid' ); ?>
                </a></li>
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=woosolid-logistica' ) ); ?>">
                    <?php esc_html_e( 'Logistica', 'woosolid' ); ?>
                </a></li>
                <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=woosolid_pickup' ) ); ?>">
                    <?php esc_html_e( 'Punti di ritiro', 'woosolid' ); ?>
                </a></li>
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=woosolid-listino' ) ); ?>">
                    <?php esc_html_e( 'Listino', 'woosolid' ); ?>
                </a></li>
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=woosolid-wizard' ) ); ?>">
                    <?php esc_html_e( 'Configurazione iniziale', 'woosolid' ); ?>
                </a></li>
            </ul>
        </div>
        <?php
    }

    public static function render_settings_redirect() {
        wp_safe_redirect( admin_url( 'admin.php?page=woosolid-ente' ) );
        exit;
    }

    public static function render_pickup_redirect() {
        wp_safe_redirect( admin_url( 'edit.php?post_type=woosolid_pickup' ) );
        exit;
    }

    public static function render_listino_redirect() {
        // Adatta lo slug se l’importer ha un nome diverso
        $page = apply_filters( 'woosolid_listino_page_slug', 'woosolid-importer' );
        wp_safe_redirect( admin_url( 'admin.php?page=' . $page ) );
        exit;
    }

    public static function render_logistica_page() {
        if ( isset( $_POST['woosolid_logistica_submit'] ) && check_admin_referer( 'woosolid_logistica', 'woosolid_logistica_nonce' ) ) {
            $shipping = isset( $_POST['woosolid_enable_shipping'] ) ? 'yes' : 'no';
            $pickup   = isset( $_POST['woosolid_enable_pickup'] ) ? 'yes' : 'no';
            update_option( 'woosolid_enable_shipping', $shipping );
            update_option( 'woosolid_enable_pickup', $pickup );
            ?>
            <div class="updated"><p><?php esc_html_e( 'Impostazioni logistica salvate.', 'woosolid' ); ?></p></div>
            <?php
        }

        $shipping = get_option( 'woosolid_enable_shipping', 'yes' );
        $pickup   = get_option( 'woosolid_enable_pickup', 'yes' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Logistica WooSolid', 'woosolid' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'woosolid_logistica', 'woosolid_logistica_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Abilita spedizione', 'woosolid' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woosolid_enable_shipping" value="yes" <?php checked( $shipping, 'yes' ); ?> />
                                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Abilita punti di ritiro', 'woosolid' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woosolid_enable_pickup" value="yes" <?php checked( $pickup, 'yes' ); ?> />
                                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button( __( 'Salva impostazioni', 'woosolid' ), 'primary', 'woosolid_logistica_submit' ); ?>
            </form>
        </div>
        <?php
    }
}

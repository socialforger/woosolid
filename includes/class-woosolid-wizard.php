<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Wizard {

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'maybe_redirect_to_wizard' ] );
        add_action( 'admin_menu', [ __CLASS__, 'add_wizard_page' ] );
    }

    public static function maybe_redirect_to_wizard() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( 'no' === get_option( 'woosolid_setup_done', 'no' ) ) {
            if ( ! isset( $_GET['page'] ) || 'woosolid-wizard' !== $_GET['page'] ) {
                wp_safe_redirect( admin_url( 'admin.php?page=woosolid-wizard' ) );
                exit;
            }
        }
    }

    public static function add_wizard_page() {
        add_submenu_page(
            null,
            __( 'WooSolid – Configurazione iniziale', 'woosolid' ),
            __( 'WooSolid – Configurazione iniziale', 'woosolid' ),
            'manage_options',
            'woosolid-wizard',
            [ __CLASS__, 'render_wizard' ]
        );
    }

    public static function render_wizard() {
        if ( isset( $_POST['woosolid_wizard_submit'] ) && check_admin_referer( 'woosolid_wizard', 'woosolid_wizard_nonce' ) ) {
            update_option( 'woosolid_ets_name', sanitize_text_field( $_POST['woosolid_ets_name'] ?? '' ) );
            update_option( 'woosolid_ets_cf', sanitize_text_field( $_POST['woosolid_ets_cf'] ?? '' ) );
            update_option( 'woosolid_ets_email', sanitize_email( $_POST['woosolid_ets_email'] ?? '' ) );
            update_option( 'woosolid_enable_shipping', isset( $_POST['woosolid_enable_shipping'] ) ? 'yes' : 'no' );
            update_option( 'woosolid_enable_pickup', isset( $_POST['woosolid_enable_pickup'] ) ? 'yes' : 'no' );
            update_option( 'woosolid_setup_done', 'yes' );

            wp_safe_redirect( admin_url( 'admin.php?page=woosolid-settings' ) );
            exit;
        }

        $ets_name   = get_option( 'woosolid_ets_name', '' );
        $ets_cf     = get_option( 'woosolid_ets_cf', '' );
        $ets_email  = get_option( 'woosolid_ets_email', '' );
        $shipping   = get_option( 'woosolid_enable_shipping', 'yes' );
        $pickup     = get_option( 'woosolid_enable_pickup', 'yes' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WooSolid – Configurazione iniziale', 'woosolid' ); ?></h1>
            <p><?php esc_html_e( 'Completa questi dati per attivare WooSolid in modalità ETS/GAS.', 'woosolid' ); ?></p>

            <form method="post">
                <?php wp_nonce_field( 'woosolid_wizard', 'woosolid_wizard_nonce' ); ?>

                <h2><?php esc_html_e( 'Dati ETS', 'woosolid' ); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><label for="woosolid_ets_name"><?php esc_html_e( 'Denominazione ETS', 'woosolid' ); ?></label></th>
                        <td><input type="text" name="woosolid_ets_name" id="woosolid_ets_name" class="regular-text" value="<?php echo esc_attr( $ets_name ); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="woosolid_ets_cf"><?php esc_html_e( 'Codice Fiscale ETS', 'woosolid' ); ?></label></th>
                        <td><input type="text" name="woosolid_ets_cf" id="woosolid_ets_cf" class="regular-text" value="<?php echo esc_attr( $ets_cf ); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="woosolid_ets_email"><?php esc_html_e( 'Email ETS per ordini e rettifiche', 'woosolid' ); ?></label></th>
                        <td><input type="email" name="woosolid_ets_email" id="woosolid_ets_email" class="regular-text" value="<?php echo esc_attr( $ets_email ); ?>"></td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Logistica', 'woosolid' ); ?></h2>

                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Abilita spedizione', 'woosolid' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woosolid_enable_shipping" value="yes" <?php checked( $shipping, 'yes' ); ?>>
                                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Abilita punti di ritiro', 'woosolid' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woosolid_enable_pickup" value="yes" <?php checked( $pickup, 'yes' ); ?>>
                                <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="woosolid_wizard_submit" class="button button-primary">
                        <?php esc_html_e( 'Completa configurazione', 'woosolid' ); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }
}

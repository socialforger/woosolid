<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Account {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'add_endpoints' ] );
        add_filter( 'query_vars', [ __CLASS__, 'add_query_vars' ] );
        add_filter( 'woocommerce_account_menu_items', [ __CLASS__, 'add_menu_items' ] );
        add_action( 'woocommerce_account_woosolid-donations_endpoint', [ __CLASS__, 'render_donations_endpoint' ] );
        add_action( 'woocommerce_account_woosolid-direct-donation_endpoint', [ __CLASS__, 'render_direct_donation_endpoint' ] );
        add_action( 'template_redirect', [ __CLASS__, 'handle_form_submission' ] );
    }

    public static function add_endpoints() {
        add_rewrite_endpoint( 'woosolid-donations', EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( 'woosolid-direct-donation', EP_ROOT | EP_PAGES );
    }

    public static function add_query_vars( $vars ) {
        $vars[] = 'woosolid-donations';
        $vars[] = 'woosolid-direct-donation';
        return $vars;
    }

    public static function add_menu_items( $items ) {
        $new = [];

        foreach ( $items as $key => $label ) {
            $new[ $key ] = $label;

            if ( 'edit-account' === $key ) {
                $new['woosolid-donations']       = __( 'Le mie donazioni', 'woosolid' );
                $new['woosolid-direct-donation'] = __( 'Fai una donazione', 'woosolid' );
            }
        }

        return $new;
    }

    public static function render_donations_endpoint() {
        if ( ! is_user_logged_in() ) {
            echo '<p>' . esc_html__( 'Devi essere autenticato per visualizzare le tue donazioni.', 'woosolid' ) . '</p>';
            return;
        }

        $current_year   = (int) date_i18n( 'Y' );
        $selected_year  = isset( $_GET['year'] ) ? (int) $_GET['year'] : $current_year;
        ?>
        <h3><?php esc_html_e( 'Le mie donazioni', 'woosolid' ); ?></h3>

        <form method="post">
            <?php wp_nonce_field( 'woosolid_donation_summary', 'woosolid_donation_summary_nonce' ); ?>

            <p>
                <label for="woosolid_donation_year">
                    <?php esc_html_e( 'Anno fiscale', 'woosolid' ); ?>
                </label>
                <select name="woosolid_donation_year" id="woosolid_donation_year">
                    <?php
                    for ( $y = $current_year; $y >= $current_year - 5; $y-- ) {
                        printf(
                            '<option value="%1$d"%2$s>%1$d</option>',
                            $y,
                            selected( $selected_year, $y, false )
                        );
                    }
                    ?>
                </select>
            </p>

            <p>
                <button type="submit" name="woosolid_donation_summary_request" class="button">
                    <?php esc_html_e( 'Invia riepilogo donazioni via email', 'woosolid' ); ?>
                </button>
            </p>
        </form>
        <?php
    }

    public static function render_direct_donation_endpoint() {
        if ( ! is_user_logged_in() ) {
            echo '<p>' . esc_html__( 'Devi essere autenticato per effettuare una donazione.', 'woosolid' ) . '</p>';
            return;
        }

        WooSolid_Direct_Donation::render_form();
    }

    public static function handle_form_submission() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( isset( $_POST['woosolid_donation_summary_request'] ) ) {
            if ( ! isset( $_POST['woosolid_donation_summary_nonce'] ) || ! wp_verify_nonce( $_POST['woosolid_donation_summary_nonce'], 'woosolid_donation_summary' ) ) {
                return;
            }

            $user_id = get_current_user_id();
            $year    = isset( $_POST['woosolid_donation_year'] ) ? (int) $_POST['woosolid_donation_year'] : (int) date_i18n( 'Y' );

            $sent = WooSolid_Charitable::send_donation_summary_email( $user_id, $year );

            if ( $sent ) {
                wc_add_notice(
                    __( 'Il riepilogo delle tue donazioni è stato inviato al tuo indirizzo email.', 'woosolid' ),
                    'success'
                );
            } else {
                wc_add_notice(
                    __( 'Si è verificato un problema durante l’invio del riepilogo donazioni.', 'woosolid' ),
                    'error'
                );
            }

            wp_safe_redirect( wc_get_account_endpoint_url( 'woosolid-donations' ) );
            exit;
        }

        if ( isset( $_POST['woosolid_direct_donation_submit'] ) ) {
            WooSolid_Direct_Donation::handle_submission();
        }
    }
}

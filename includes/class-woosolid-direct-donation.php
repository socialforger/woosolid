<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Direct_Donation {

    public static function init() {

        // Mostra il form nell'endpoint My Account
        add_action(
            'woocommerce_account_woosolid-direct-donation_endpoint',
            [ __CLASS__, 'render_form' ]
        );

        // Gestisce l'invio del form
        add_action( 'init', [ __CLASS__, 'handle_submission' ] );
    }

    /**
     * FORM DONAZIONE DIRETTA
     */
    public static function render_form() {

        $campaigns = get_posts( [
            'post_type'      => 'campaign',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        ?>
        <h3><?php esc_html_e( 'Fai una donazione diretta', 'woosolid' ); ?></h3>

        <form method="post">
            <?php wp_nonce_field( 'woosolid_direct_donation', 'woosolid_direct_donation_nonce' ); ?>

            <p>
                <label><?php esc_html_e( 'Campagna', 'woosolid' ); ?></label>
                <select name="woosolid_direct_campaign" required>
                    <option value=""><?php esc_html_e( 'Seleziona una campagna', 'woosolid' ); ?></option>
                    <?php foreach ( $campaigns as $campaign ) : ?>
                        <option value="<?php echo esc_attr( $campaign->ID ); ?>">
                            <?php echo esc_html( $campaign->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label><?php esc_html_e( 'Importo (€)', 'woosolid' ); ?></label>
                <input type="number" step="0.01" min="1" name="woosolid_direct_amount" required>
            </p>

            <p>
                <label><?php esc_html_e( 'Tipo di donazione', 'woosolid' ); ?></label>
                <select name="woosolid_direct_type" required>
                    <option value="named"><?php esc_html_e( 'Nominativa', 'woosolid' ); ?></option>
                    <option value="anonymous"><?php esc_html_e( 'Anonima', 'woosolid' ); ?></option>
                </select>
            </p>

            <p>
                <label><?php esc_html_e( 'Metodo di pagamento', 'woosolid' ); ?></label>
                <select name="woosolid_direct_payment" required>
                    <option value="stripe"><?php esc_html_e( 'Carta (Stripe)', 'woosolid' ); ?></option>
                </select>
            </p>

            <p>
                <button type="submit" name="woosolid_direct_donation_submit" class="button button-primary">
                    <?php esc_html_e( 'Conferma donazione', 'woosolid' ); ?>
                </button>
            </p>
        </form>
        <?php
    }

    /**
     * PROCESSA LA DONAZIONE DIRETTA
     */
    public static function handle_submission() {

        if ( ! isset( $_POST['woosolid_direct_donation_submit'] ) ) {
            return;
        }

        if ( ! isset( $_POST['woosolid_direct_donation_nonce'] ) ||
             ! wp_verify_nonce( $_POST['woosolid_direct_donation_nonce'], 'woosolid_direct_donation' ) ) {

            wc_add_notice( __( 'Errore di sicurezza.', 'woosolid' ), 'error' );
            return;
        }

        $user_id     = get_current_user_id();
        $campaign_id = (int) $_POST['woosolid_direct_campaign'];
        $amount      = (float) $_POST['woosolid_direct_amount'];
        $type        = sanitize_text_field( $_POST['woosolid_direct_type'] );
        $payment     = sanitize_text_field( $_POST['woosolid_direct_payment'] );

        if ( ! $campaign_id || $amount <= 0 ) {
            wc_add_notice( __( 'Dati donazione non validi.', 'woosolid' ), 'error' );
            wp_safe_redirect( wc_get_account_endpoint_url( 'woosolid-direct-donation' ) );
            exit;
        }

        $anonymous = ( $type === 'anonymous' );

        /**
         * 1) CREA ORDINE WOOCOMMERCE SENZA PRODOTTI
         */
        $order = wc_create_order();
        $order->set_total( $amount );

        // Meta WooSolid
        $order->update_meta_data( '_woosolid_donation_direct', 'yes' );
        $order->update_meta_data( '_woosolid_donation_campaign', $campaign_id );
        $order->update_meta_data( '_woosolid_donation_type', $anonymous ? 'anonymous' : 'named' );

        if ( $anonymous ) {
            $order->set_customer_id( 0 );
        } else {
            $order->set_customer_id( $user_id );
        }

        /**
         * 2) FORZA METODO DI PAGAMENTO = STRIPE
         */
        $order->set_payment_method( 'stripe' );
        $order->save();

        /**
         * 3) REDIRECT A STRIPE CHECKOUT
         */
        $redirect = $order->get_checkout_payment_url( true );

        wp_redirect( $redirect );
        exit;
    }
}

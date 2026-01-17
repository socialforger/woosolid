<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Direct_Donation {

    public static function init() {
        // Richiamata da WooSolid_Account
    }

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
                <label for="woosolid_direct_campaign">
                    <?php esc_html_e( 'Campagna', 'woosolid' ); ?>
                </label>
                <select name="woosolid_direct_campaign" id="woosolid_direct_campaign" required>
                    <option value=""><?php esc_html_e( 'Seleziona una campagna', 'woosolid' ); ?></option>
                    <?php foreach ( $campaigns as $campaign ) : ?>
                        <option value="<?php echo esc_attr( $campaign->ID ); ?>">
                            <?php echo esc_html( $campaign->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="woosolid_direct_amount">
                    <?php esc_html_e( 'Importo (€)', 'woosolid' ); ?>
                </label>
                <input type="number" step="0.01" min="1" name="woosolid_direct_amount" id="woosolid_direct_amount" required>
            </p>

            <p>
                <label for="woosolid_direct_type">
                    <?php esc_html_e( 'Tipo di donazione', 'woosolid' ); ?>
                </label>
                <select name="woosolid_direct_type" id="woosolid_direct_type" required>
                    <option value="named"><?php esc_html_e( 'Nominativa', 'woosolid' ); ?></option>
                    <option value="anonymous"><?php esc_html_e( 'Anonima', 'woosolid' ); ?></option>
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

    public static function handle_submission() {
        if ( ! isset( $_POST['woosolid_direct_donation_nonce'] ) || ! wp_verify_nonce( $_POST['woosolid_direct_donation_nonce'], 'woosolid_direct_donation' ) ) {
            return;
        }

        $user_id     = get_current_user_id();
        $campaign_id = isset( $_POST['woosolid_direct_campaign'] ) ? (int) $_POST['woosolid_direct_campaign'] : 0;
        $amount      = isset( $_POST['woosolid_direct_amount'] ) ? (float) $_POST['woosolid_direct_amount'] : 0;
        $type        = isset( $_POST['woosolid_direct_type'] ) ? sanitize_text_field( $_POST['woosolid_direct_type'] ) : 'named';

        if ( ! $campaign_id || $amount <= 0 ) {
            wc_add_notice( __( 'Dati donazione non validi.', 'woosolid' ), 'error' );
            wp_safe_redirect( wc_get_account_endpoint_url( 'woosolid-direct-donation' ) );
            exit;
        }

        $anonymous = ( 'anonymous' === $type );

        $note = $anonymous
            ? __( 'donazione diretta anonima', 'woosolid' )
            : __( 'donazione diretta', 'woosolid' );

        $donation_id = WooSolid_Charitable::create_charitable_donation(
            $campaign_id,
            $amount,
            $anonymous ? 0 : $user_id,
            $note,
            $anonymous
        );

        if ( $donation_id ) {
            wc_add_notice( __( 'La tua donazione è stata registrata correttamente.', 'woosolid' ), 'success' );
        } else {
            wc_add_notice( __( 'Si è verificato un problema durante la registrazione della donazione.', 'woosolid' ), 'error' );
        }

        wp_safe_redirect( wc_get_account_endpoint_url( 'woosolid-direct-donation' ) );
        exit;
    }
}

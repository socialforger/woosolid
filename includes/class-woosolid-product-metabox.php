<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Product_Metabox {

    public static function init() {
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_metabox' ] );
        add_action( 'save_post_product', [ __CLASS__, 'save_metabox' ] );
    }

    public static function add_metabox() {
        add_meta_box(
            'woosolid_product_box',
            __( 'Fee solidale (WooSolid)', 'woosolid' ),
            [ __CLASS__, 'render_metabox' ],
            'product',
            'side',
            'default'
        );
    }

    public static function render_metabox( $post ) {
        wp_nonce_field( 'woosolid_product_metabox', 'woosolid_product_metabox_nonce' );

        $campaign_id = get_post_meta( $post->ID, '_woosolid_campaign_id', true );
        $fee_mode    = get_post_meta( $post->ID, '_woosolid_fee_mode', true );
        $fee_amount  = get_post_meta( $post->ID, '_woosolid_fee_amount', true );

        $campaigns = get_posts( [
            'post_type'      => 'campaign',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );
        ?>

        <p>
            <label for="woosolid_campaign_id"><strong><?php esc_html_e( 'Campagna Charitable', 'woosolid' ); ?></strong></label>
            <select name="woosolid_campaign_id" id="woosolid_campaign_id" style="width:100%;">
                <option value=""><?php esc_html_e( 'Nessuna (fee disattivata)', 'woosolid' ); ?></option>
                <?php foreach ( $campaigns as $c ) : ?>
                    <option value="<?php echo esc_attr( $c->ID ); ?>" <?php selected( $campaign_id, $c->ID ); ?>>
                        <?php echo esc_html( $c->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="woosolid_fee_mode"><strong><?php esc_html_e( 'Modalità fee', 'woosolid' ); ?></strong></label>
            <select name="woosolid_fee_mode" id="woosolid_fee_mode" style="width:100%;">
                <option value="fixed" <?php selected( $fee_mode, 'fixed' ); ?>>
                    <?php esc_html_e( 'Importo fisso', 'woosolid' ); ?>
                </option>
                <option value="percent" <?php selected( $fee_mode, 'percent' ); ?>>
                    <?php esc_html_e( 'Percentuale sul prezzo', 'woosolid' ); ?>
                </option>
            </select>
        </p>

        <p>
            <label for="woosolid_fee_amount"><strong><?php esc_html_e( 'Importo fee', 'woosolid' ); ?></strong></label>
            <input type="number" step="0.01" min="0" name="woosolid_fee_amount" id="woosolid_fee_amount" value="<?php echo esc_attr( $fee_amount ); ?>" style="width:100%;">
        </p>

        <?php
    }

    public static function save_metabox( $post_id ) {
        if ( ! isset( $_POST['woosolid_product_metabox_nonce'] ) ||
             ! wp_verify_nonce( $_POST['woosolid_product_metabox_nonce'], 'woosolid_product_metabox' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_product', $post_id ) ) {
            return;
        }

        $campaign_id = isset( $_POST['woosolid_campaign_id'] ) ? (int) $_POST['woosolid_campaign_id'] : '';
        $fee_mode    = isset( $_POST['woosolid_fee_mode'] ) ? sanitize_text_field( $_POST['woosolid_fee_mode'] ) : 'fixed';
        $fee_amount  = isset( $_POST['woosolid_fee_amount'] ) ? sanitize_text_field( $_POST['woosolid_fee_amount'] ) : '';

        update_post_meta( $post_id, '_woosolid_campaign_id', $campaign_id );

        if ( $campaign_id ) {
            update_post_meta( $post_id, '_woosolid_fee_mode', $fee_mode );
            update_post_meta( $post_id, '_woosolid_fee_amount', $fee_amount );
        } else {
            delete_post_meta( $post_id, '_woosolid_fee_mode' );
            delete_post_meta( $post_id, '_woosolid_fee_amount' );
        }
    }
}

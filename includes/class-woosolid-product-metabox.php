<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Product_Metabox {

    public static function init() {
        add_action( 'woocommerce_product_options_general_product_data', [ __CLASS__, 'add_fields' ] );
        add_action( 'woocommerce_admin_process_product_object', [ __CLASS__, 'save_fields' ] );
    }

    /**
     * Aggiunge i campi nel metabox prodotto
     */
    public static function add_fields() {

        global $post;

        $is_donation = get_post_meta( $post->ID, '_woosolid_is_donation', true );
        $campaign_id = get_post_meta( $post->ID, '_woosolid_campaign_id', true );

        // Recupera tutte le campagne Charitable
        $campaigns = get_posts([
            'post_type'      => 'campaign',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        echo '<div class="options_group">';

        // Checkbox "Quota solidale"
        woocommerce_wp_checkbox([
            'id'          => '_woosolid_is_donation',
            'label'       => 'Quota solidale',
            'description' => 'Se abilitato, questo prodotto finanzia una campagna Charitable.',
            'value'       => $is_donation === 'yes' ? 'yes' : 'no',
        ]);

        // Testo dinamico
        echo '<p class="form-field" style="margin-top:-10px; margin-left:24px;">';

        if ( $campaign_id ) {
            echo '<em>La quota solidale finanzia la campagna: <strong>' . esc_html( get_the_title( $campaign_id ) ) . '</strong></em>';
        } else {
            echo '<em>La quota solidale finanzia una campagna (selezionala qui sotto)</em>';
        }

        echo '</p>';

        // Select campagne
        woocommerce_wp_select([
            'id'      => '_woosolid_campaign_id',
            'label'   => 'Campagna collegata',
            'options' => self::build_campaign_options( $campaigns ),
            'value'   => $campaign_id,
        ]);

        echo '</div>';
    }

    protected static function build_campaign_options( $campaigns ) {

        $options = [ '' => '— Seleziona una campagna —' ];

        foreach ( $campaigns as $c ) {
            $options[ $c->ID ] = $c->post_title;
        }

        return $options;
    }

    /**
     * Salva i campi
     */
    public static function save_fields( $product ) {

        $is_donation = isset( $_POST['_woosolid_is_donation'] ) ? 'yes' : 'no';
        $campaign_id = isset( $_POST['_woosolid_campaign_id'] ) ? intval( $_POST['_woosolid_campaign_id'] ) : 0;

        // Validazione: campagna obbligatoria
        if ( $is_donation === 'yes' && empty( $campaign_id ) ) {
            wc_add_notice(
                __( 'Per i prodotti con "Quota solidale" attiva devi selezionare una campagna Charitable.', 'woosolid' ),
                'error'
            );
            return;
        }

        $product->update_meta_data( '_woosolid_is_donation', $is_donation );
        $product->update_meta_data( '_woosolid_campaign_id', $campaign_id );
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Charitable {

    public static function init() {
        add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'create_donation_from_fee' ] );
    }

    public static function create_donation_from_fee( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        $fee = (float) $order->get_meta( '_woosolid_fee_solidale' );
        if ( $fee <= 0 ) return;

        $settings    = WooSolid_Settings::get_settings();
        $campaign_id = isset( $settings['woosolid_campaign_id'] ) ? (int) $settings['woosolid_campaign_id'] : 0;
        if ( ! $campaign_id ) return;

        $donor_id = $order->get_user_id();

        $order_number = $order->get_order_number();
        $order_date   = $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd/m/Y' ) : '';
        $causale      = sprintf( __( 'acquisto n. %s del %s', 'woosolid' ), $order_number, $order_date );

        $donation_id = wp_insert_post( [
            'post_title'  => sprintf( __( 'Donazione solidale – Ordine %s', 'woosolid' ), $order_number ),
            'post_type'   => 'donation',
            'post_status' => 'charitable-completed',
        ] );

        if ( ! $donation_id || is_wp_error( $donation_id ) ) {
            return;
        }

        update_post_meta( $donation_id, '_campaign_id', $campaign_id );
        update_post_meta( $donation_id, '_donation_amount', $fee );
        if ( $donor_id ) {
            update_post_meta( $donation_id, '_donor_id', $donor_id );
        }
        update_post_meta( $donation_id, '_donation_note', $causale );
        update_post_meta( $donation_id, '_woosolid_causale', $causale );
        update_post_meta( $donation_id, '_source_order_id', $order_id );

        if ( function_exists( 'charitable_get_campaign' ) ) {
            $campaign = charitable_get_campaign( $campaign_id );
            if ( $campaign ) {
                $campaign->update_donation_stats();
            }
        }

        update_post_meta( $order_id, '_woosolid_donation_id', $donation_id );
    }
}

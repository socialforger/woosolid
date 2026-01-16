<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Crowdfunding {

    const META_DONATION_RECORDED = '_woosolid_solidarity_donation_recorded';

    public static function init() {
        add_action( 'woocommerce_order_status_processing', [ __CLASS__, 'process_order' ], 20, 1 );
        add_action( 'woocommerce_order_status_completed',  [ __CLASS__, 'process_order' ], 20, 1 );
    }

    public static function process_order( $order_id ) {

        if ( ! function_exists( 'wpcf_function' ) ) return;

        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        if ( $order->get_meta( self::META_DONATION_RECORDED ) ) return;

        $settings   = WooSolid_Settings::get_settings();
        $project_id = intval( $settings['main_project_id'] );
        if ( ! $project_id ) return;

        $solidarity_total = self::get_solidarity_fee( $order );
        if ( $solidarity_total <= 0 ) return;

        $user_id = $order->get_user_id() ? $order->get_user_id() : 0;

        self::insert_pledge( $project_id, $order_id, $solidarity_total, $user_id );
        self::update_campaign_totals( $project_id );

        $order->update_meta_data( self::META_DONATION_RECORDED, 1 );
        $order->save();
    }

    private static function get_solidarity_fee( $order ) {
        $total = 0;

        foreach ( $order->get_items( 'fee' ) as $fee_item ) {
            if ( $fee_item->get_name() === __( 'Contributo Solidale', 'woosolid' ) ) {
                $total += floatval( $fee_item->get_total() );
            }
        }

        return $total;
    }

    private static function insert_pledge( $project_id, $order_id, $amount, $user_id ) {
        try {
            wpcf_function()->insert_pledge( [
                'campaign_id' => $project_id,
                'order_id'    => $order_id,
                'amount'      => $amount,
                'user_id'     => $user_id,
            ] );
        } catch ( Exception $e ) {
            error_log( 'WooSolid: Failed to insert pledge → ' . $e->getMessage() );
        }
    }

    private static function update_campaign_totals( $project_id ) {
        try {
            wpcf_function()->update_campaign_raised_amount( $project_id );
            wpcf_function()->update_campaign_backers( $project_id );
        } catch ( Exception $e ) {
            error_log( 'WooSolid: Failed to update campaign totals → ' . $e->getMessage() );
        }
    }
}

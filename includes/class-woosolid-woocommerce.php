<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_WooCommerce {

    public static function init() {
        add_action( 'woocommerce_checkout_create_order', [ __CLASS__, 'store_donations_in_order' ], 20, 2 );
    }

    public static function store_donations_in_order( $order, $data ) {
        $items = $order->get_items();

        $fee_by_campaign = [];

        foreach ( $items as $item ) {
            $product = $item->get_product();
            if ( ! $product ) {
                continue;
            }

            $product_id  = $product->get_id();
            $campaign_id = get_post_meta( $product_id, '_woosolid_campaign_id', true );

            if ( ! $campaign_id ) {
                continue;
            }

            $fee_mode   = get_post_meta( $product_id, '_woosolid_fee_mode', true );
            $fee_amount = (float) get_post_meta( $product_id, '_woosolid_fee_amount', true );

            if ( $fee_amount <= 0 ) {
                continue;
            }

            $qty        = $item->get_quantity();
            $line_total = (float) $item->get_total();

            if ( 'percent' === $fee_mode ) {
                $fee = ( $line_total * $fee_amount / 100 );
            } else {
                $fee = $fee_amount * $qty;
            }

            if ( $fee <= 0 ) {
                continue;
            }

            if ( ! isset( $fee_by_campaign[ $campaign_id ] ) ) {
                $fee_by_campaign[ $campaign_id ] = 0;
            }

            $fee_by_campaign[ $campaign_id ] += $fee;
        }

        if ( ! empty( $fee_by_campaign ) ) {
            $donations = [
                'fee' => [],
            ];

            foreach ( $fee_by_campaign as $campaign_id => $amount ) {
                $donations['fee'][] = [
                    'campaign_id' => $campaign_id,
                    'amount'      => $amount,
                ];
            }

            $order->update_meta_data( '_woosolid_donations', $donations );
        }
    }
}

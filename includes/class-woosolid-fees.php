<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Fees {

    public static function init() {
        add_action( 'woocommerce_cart_calculate_fees', [ __CLASS__, 'add_solidarity_fee' ], 20 );
        add_action( 'woocommerce_cart_calculate_fees', [ __CLASS__, 'add_transport_fee' ], 30 );
    }

    public static function add_solidarity_fee( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
        if ( $cart->is_empty() ) return;

        $settings   = WooSolid_Settings::get_settings();
        $percentage = floatval( $settings['solidarity_percentage'] );
        if ( $percentage <= 0 ) return;

        $amount = $cart->get_subtotal() * ( $percentage / 100 );
        if ( $amount <= 0 ) return;

        $cart->add_fee( __( 'Contributo Solidale', 'woosolid' ), $amount, false );
    }

    public static function add_transport_fee( $cart ) {

        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
        if ( $cart->is_empty() ) return;

        $country = WC()->customer->get_shipping_country();
        $weight  = self::get_cart_weight( $cart );
        $total   = $cart->get_subtotal();

        $rules = [

            [
                'country'     => 'IT',
                'min_weight'  => 0,
                'max_weight'  => 3,
                'fee'         => 0,
            ],
            [
                'country'     => 'IT',
                'min_weight'  => 3,
                'max_weight'  => 10,
                'fee'         => 8,
            ],
            [
                'country'     => 'IT',
                'min_weight'  => 10,
                'max_weight'  => 9999,
                'fee'         => 12,
            ],

            [
                'country' => 'EU',
                'fee'     => 20,
            ],

            [
                'country' => 'OTHER',
                'fee'     => 30,
            ],
        ];

        $fee_to_apply = self::match_fee_rule( $rules, $country, $weight, $total );

        if ( $fee_to_apply > 0 ) {
            $cart->add_fee( __( 'Costo Trasporto', 'woosolid' ), $fee_to_apply, false );
        }
    }

    private static function get_cart_weight( $cart ) {
        $weight = 0;
        foreach ( $cart->get_cart() as $item ) {
            if ( empty( $item['data'] ) ) continue;
            $product = $item['data'];
            $qty     = $item['quantity'];
            $weight += floatval( $product->get_weight() ) * $qty;
        }
        return $weight;
    }

    private static function match_fee_rule( $rules, $country, $weight, $total ) {

        foreach ( $rules as $rule ) {

            if ( isset( $rule['country'] ) ) {

                if ( $rule['country'] === 'IT' && $country !== 'IT' ) continue;

                if ( $rule['country'] === 'EU' && ! self::is_eu_country( $country ) ) continue;

                if ( $rule['country'] === 'OTHER' && ( $country === 'IT' || self::is_eu_country( $country ) ) ) continue;
            }

            if ( isset( $rule['min_weight'], $rule['max_weight'] ) ) {
                if ( $weight < $rule['min_weight'] || $weight > $rule['max_weight'] ) continue;
            }

            if ( isset( $rule['min_total'] ) && $total < $rule['min_total'] ) continue;
            if ( isset( $rule['max_total'] ) && $total > $rule['max_total'] ) continue;

            return $rule['fee'];
        }

        return 0;
    }

    private static function is_eu_country( $country ) {
        $eu = [ 'AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK' ];
        return in_array( $country, $eu, true );
    }
}

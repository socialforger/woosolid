<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_WooCommerce {

    public static function init() {
        add_filter( 'woocommerce_shipping_rate_label', [ __CLASS__, 'rename_local_pickup' ], 10, 2 );
        add_filter( 'woocommerce_package_rates', [ __CLASS__, 'filter_shipping_methods' ], 10, 1 );
    }

    public static function rename_local_pickup( $label, $rate ) {
        if ( $rate->method_id === 'local_pickup' ) {
            return __( 'Ritiro presso Punto di ritiro', 'woosolid' );
        }
        return $label;
    }

    public static function filter_shipping_methods( $rates ) {
        $settings = WooSolid_Settings::get_settings();

        $enable_shipping = ! empty( $settings['enable_shipping'] );
        $enable_pickup   = ! empty( $settings['enable_pickup_points'] );

        foreach ( $rates as $rate_id => $rate ) {
            if ( $rate->method_id === 'local_pickup' && ! $enable_pickup ) {
                unset( $rates[ $rate_id ] );
            }

            if ( $rate->method_id !== 'local_pickup' && ! $enable_shipping ) {
                unset( $rates[ $rate_id ] );
            }
        }

        return $rates;
    }
}

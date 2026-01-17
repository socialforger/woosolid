<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Utils {

    public static function is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }

    public static function is_charitable_active() {
        return class_exists( 'Charitable' );
    }

    public static function get_option_bool( $key, $default = false ) {
        $value = get_option( $key, $default ? 'yes' : 'no' );
        return 'yes' === $value;
    }
}

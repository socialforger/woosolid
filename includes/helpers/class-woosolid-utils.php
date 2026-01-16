<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Utils {

    public static function format_money( $amount ) {
        return wc_price( $amount );
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Logger {

    public static function log( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[WooSolid] ' . print_r( $message, true ) );
        }
    }
}

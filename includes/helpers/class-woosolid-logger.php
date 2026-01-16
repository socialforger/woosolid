<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Logger {

    public static function log( $message, $context = [] ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[WooSolid] ' . $message . ( $context ? ' ' . wp_json_encode( $context ) : '' ) );
        }
    }
}

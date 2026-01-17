<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_WooCommerce {

    public static function init() {

        // Aggiunge badge "Quota solidale" nella lista prodotti
        add_filter( 'woocommerce_product_get_name', [ __CLASS__, 'add_solidarity_badge' ], 10, 2 );

        // Aggiunge info nel carrello
        add_filter( 'woocommerce_cart_item_name', [ __CLASS__, 'cart_item_label' ], 10, 3 );
    }

    /**
     * Badge nella lista prodotti
     */
    public static function add_solidarity_badge( $name, $product ) {

        $is_donation = $product->get_meta( '_woosolid_is_donation' );

        if ( $is_donation === 'yes' ) {
            return $name . ' <span style="color:#c0392b; font-weight:bold;">(Quota solidale)</span>';
        }

        return $name;
    }

    /**
     * Etichetta nel carrello
     */
    public static function cart_item_label( $name, $cart_item, $cart_item_key ) {

        $product = $cart_item['data'];
        $is_donation = $product->get_meta( '_woosolid_is_donation' );

        if ( $is_donation === 'yes' ) {
            $campaign_id = $product->get_meta( '_woosolid_campaign_id' );
            $campaign = $campaign_id ? get_the_title( $campaign_id ) : 'Campagna non selezionata';

            $name .= '<br><small style="color:#27ae60;">Finanzia la campagna: ' . esc_html( $campaign ) . '</small>';
        }

        return $name;
    }
}

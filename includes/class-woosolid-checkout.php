<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Checkout {

    public static function init() {

        // Messaggio informativo nel checkout per prodotti solidali
        add_action( 'woocommerce_review_order_before_payment', [ __CLASS__, 'checkout_notice' ] );
    }

    /**
     * Messaggio nel checkout se ci sono prodotti solidali
     */
    public static function checkout_notice() {

        $has_donation = false;

        foreach ( WC()->cart->get_cart() as $item ) {
            $product = $item['data'];
            if ( $product->get_meta( '_woosolid_is_donation' ) === 'yes' ) {
                $has_donation = true;
                break;
            }
        }

        if ( $has_donation ) {
            echo '<div class="woocommerce-info" style="border-left-color:#27ae60;">
                    Stai sostenendo una o più campagne solidali con il tuo acquisto.
                  </div>';
        }
    }
}

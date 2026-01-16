<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Emails {

    public static function init() {
        add_action( 'woocommerce_order_status_processing', [ __CLASS__, 'send_association_email' ], 30, 1 );
    }

    public static function send_association_email( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        $settings = WooSolid_Settings::get_settings();
        $to       = $settings['association_email'];

        $subject = sprintf( __( 'Nuovo ordine solidale #%d', 'woosolid' ), $order_id );

        $buyer_role  = $order->get_meta( '_woosolid_buyer_role' );
        $group_name  = $order->get_meta( '_woosolid_group_name' );
        $legal       = $order->get_meta( '_woosolid_legal_representative' );
        $ship_phone  = $order->get_meta( '_woosolid_shipping_phone' );
        $shipping_method = $order->get_shipping_method();

        ob_start();
        ?>
Nuovo ordine solidale #<?php echo esc_html( $order_id ); ?>


<?php esc_html_e( 'Cliente:', 'woosolid' ); ?>
<?php echo esc_html( $order->get_formatted_billing_full_name() ); ?>


Email: <?php echo esc_html( $order->get_billing_email() ); ?>


Ruolo acquirente: <?php echo esc_html( $buyer_role ); ?>

<?php if ( $group_name ) : ?>
Nome gruppo/associazione: <?php echo esc_html( $group_name ); ?>

<?php endif; ?>

<?php if ( $legal ) : ?>
Rappresentante legale: <?php echo esc_html( $legal ); ?>


<?php endif; ?>

Metodo di consegna: <?php echo esc_html( $shipping_method ); ?>


Telefono DDT: <?php echo esc_html( $ship_phone ); ?>


<?php esc_html_e( 'Prodotti:', 'woosolid' ); ?>

<?php foreach ( $order->get_items() as $item ) : ?>
- <?php echo esc_html( $item->get_name() ); ?> x <?php echo esc_html( $item->get_quantity() ); ?> (<?php echo wp_kses_post( wc_price( $item->get_total() ) ); ?>)
<?php endforeach; ?>


<?php esc_html_e( 'Fee:', 'woosolid' ); ?>

<?php foreach ( $order->get_items( 'fee' ) as $fee ) : ?>
- <?php echo esc_html( $fee->get_name() ); ?>: <?php echo wp_kses_post( wc_price( $fee->get_total() ) ); ?>

<?php endforeach; ?>


<?php esc_html_e( 'Totale:', 'woosolid' ); ?> <?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?>


--- DATI ASSOCIAZIONE VENDITRICE ---

Associazione: <?php echo esc_html( $settings['association_name'] ); ?> (<?php echo esc_html( $settings['association_legal_form'] ); ?>)
CF: <?php echo esc_html( $settings['association_cf'] ); ?> - P.IVA: <?php echo esc_html( $settings['association_vat'] ); ?>


PEC: <?php echo esc_html( $settings['association_pec'] ); ?> - SDI: <?php echo esc_html( $settings['association_sdi'] ); ?>


Sede legale: <?php echo esc_html( $settings['association_address'] ); ?>, <?php echo esc_html( $settings['association_postcode'] ); ?> <?php echo esc_html( $settings['association_city'] ); ?> (<?php echo esc_html( $settings['association_province'] ); ?>)

Telefono: <?php echo esc_html( $settings['association_phone'] ); ?>


IBAN: <?php echo esc_html( $settings['association_iban'] ); ?>


        <?php
        $message = ob_get_clean();

        wp_mail( $to, $subject, $message );
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Account {

    public static function init() {
        add_filter( 'woocommerce_my_account_my_orders_columns', [ __CLASS__, 'add_consegna_column' ] );
        add_action( 'woocommerce_my_account_my_orders_column_woosolid_consegna', [ __CLASS__, 'render_consegna_column' ] );
        add_action( 'woocommerce_view_order', [ __CLASS__, 'view_order_delivery_box' ], 20 );
    }

    public static function add_consegna_column( $columns ) {
        $new = [];
        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;
            if ( 'order-total' === $key ) {
                $new['woosolid_consegna'] = 'Consegna';
            }
        }
        return $new;
    }

    public static function render_consegna_column( $order ) {

        if ( ! $order instanceof WC_Order ) {
            return;
        }

        $pickup_id = $order->get_meta( '_woosolid_pickup_id' );
        $delivery  = $order->get_meta( '_woosolid_delivery_address' );

        if ( $pickup_id ) {
            echo 'Punto di ritiro';
        } elseif ( ! empty( $delivery ) ) {
            echo 'Consegna a domicilio';
        } else {
            echo '-';
        }
    }

    public static function view_order_delivery_box( $order_id ) {

        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        $pickup_id = $order->get_meta( '_woosolid_pickup_id' );
        $delivery  = $order->get_meta( '_woosolid_delivery_address' );

        echo '<section class="woocommerce-order-details woosolid-order-delivery">';
        echo '<h2 class="woocommerce-order-details__title">Dettagli consegna</h2>';

        if ( $pickup_id ) {

            $post = get_post( $pickup_id );
            if ( $post && $post->post_type === 'woosolid_pickup' ) {

                $indirizzo = get_post_meta( $pickup_id, '_woosolid_pickup_indirizzo', true );
                $citta     = get_post_meta( $pickup_id, '_woosolid_pickup_citta', true );
                $provincia = get_post_meta( $pickup_id, '_woosolid_pickup_provincia', true );
                $nazione   = get_post_meta( $pickup_id, '_woosolid_pickup_nazione', true );
                $orari     = get_post_meta( $pickup_id, '_woosolid_pickup_orari', true );
                $referente = get_post_meta( $pickup_id, '_woosolid_pickup_referente', true );
                $telefono  = get_post_meta( $pickup_id, '_woosolid_pickup_telefono', true );

                echo '<p><strong>Punto di ritiro:</strong><br>';
                echo esc_html( get_the_title( $pickup_id ) ) . '<br>';
                if ( $indirizzo ) echo esc_html( $indirizzo ) . '<br>';
                if ( $citta || $provincia ) echo esc_html( trim( $citta . ' ' . $provincia ) ) . '<br>';
                if ( $nazione ) echo esc_html( $nazione ) . '<br>';
                if ( $orari ) echo '<br><strong>Orari:</strong> ' . nl2br( esc_html( $orari ) ) . '<br>';
                if ( $referente ) echo '<strong>Referente:</strong> ' . esc_html( $referente ) . '<br>';
                if ( $telefono ) echo '<strong>Telefono:</strong> ' . esc_html( $telefono ) . '<br>';
                echo '</p>';
            }

        } elseif ( ! empty( $delivery ) ) {

            echo '<p><strong>Consegna a domicilio:</strong><br>';
            echo nl2br( esc_html( $delivery ) );
            echo '</p>';

        } else {

            echo '<p><strong>Indirizzo di consegna:</strong><br>';
            echo wp_kses_post( $order->get_formatted_shipping_address() );
            echo '</p>';
        }

        echo '</section>';
    }
}

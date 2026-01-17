<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Emails {

    public static function init() {
        add_action( 'woocommerce_email_after_order_table', [ __CLASS__, 'email_payment_and_delivery' ], 5, 4 );
    }

    public static function email_payment_and_delivery( $order, $sent_to_admin, $plain_text, $email ) {

        if ( ! $order instanceof WC_Order ) {
            return;
        }

        $payment_method = $order->get_payment_method_title();
        $status         = $order->get_status();

        // Stato pagamento leggibile
        $payment_status = 'Da pagare';
        if ( in_array( $status, [ 'processing', 'completed' ], true ) ) {
            $payment_status = 'Pagato';
        }

        // Dati consegna / ritiro (adatta alle tue meta key WooSolid)
        $pickup_id = $order->get_meta( '_woosolid_pickup_id' );
        $delivery  = $order->get_meta( '_woosolid_delivery_address' );

        echo '<h2>Pagamento</h2>';
        echo '<p>';
        echo '<strong>Metodo di pagamento:</strong> ' . esc_html( $payment_method ) . '<br>';
        echo '<strong>Stato pagamento:</strong> ' . esc_html( $payment_status );
        echo '</p>';

        echo '<h2>Dettagli consegna</h2>';

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

            // fallback: indirizzo di spedizione WooCommerce
            echo '<p><strong>Indirizzo di consegna:</strong><br>';
            echo wp_kses_post( $order->get_formatted_shipping_address() );
            echo '</p>';
        }
    }
}

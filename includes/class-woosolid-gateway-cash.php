<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Gateway_Cash extends WC_Payment_Gateway {

    public function __construct() {

        $this->id                 = 'woosolid_cash';
        $this->method_title       = 'Pagamento in contanti';
        $this->method_description = 'Pagamento in contanti alla consegna o al punto di ritiro.';
        $this->has_fields         = false;

        $this->title = 'Pagamento in contanti';

        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
        add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'set_on_hold_if_needed' ], 10, 1 );
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => 'Abilita/Disabilita',
                'type'    => 'checkbox',
                'label'   => 'Abilita pagamento in contanti',
                'default' => 'yes',
            ],
            'title' => [
                'title'       => 'Titolo',
                'type'        => 'text',
                'description' => 'Titolo mostrato al checkout.',
                'default'     => 'Pagamento in contanti',
            ],
            'description' => [
                'title'       => 'Descrizione',
                'type'        => 'textarea',
                'description' => 'Testo mostrato sotto il metodo di pagamento.',
                'default'     => 'Paga in contanti al momento della consegna o al punto di ritiro.',
            ],
        ];
    }

    /**
     * Disponibile solo se il metodo di spedizione è ritiro o consegna WooSolid
     */
    public function is_available() {

        if ( 'yes' !== $this->get_option( 'enabled' ) ) {
            return false;
        }

        if ( ! is_checkout() ) {
            return false;
        }

        $chosen_shipping = WC()->session ? WC()->session->get( 'chosen_shipping_methods' ) : [];

        if ( empty( $chosen_shipping ) || ! is_array( $chosen_shipping ) ) {
            return false;
        }

        $method = $chosen_shipping[0];

        // Adatta questi ID ai tuoi shipping methods WooSolid
        if (
            strpos( $method, 'woosolid_pickup' ) !== false ||
            strpos( $method, 'woosolid_delivery' ) !== false
        ) {
            return true;
        }

        return false;
    }

    public function process_payment( $order_id ) {

        $order = wc_get_order( $order_id );

        // Ordine da pagare alla consegna → on-hold
        $order->update_status( 'on-hold', 'Pagamento in contanti alla consegna o al punto di ritiro.' );

        WC()->cart->empty_cart();

        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        ];
    }

    /**
     * Safety: se serve forzare on-hold
     */
    public function set_on_hold_if_needed( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( $order && $order->get_status() === 'pending' ) {
            $order->update_status( 'on-hold', 'Pagamento in contanti alla consegna o al punto di ritiro.' );
        }
    }
}

/**
 * Registrazione gateway
 */
add_filter( 'woocommerce_payment_gateways', function( $gateways ) {
    $gateways[] = 'WooSolid_Gateway_Cash';
    return $gateways;
} );

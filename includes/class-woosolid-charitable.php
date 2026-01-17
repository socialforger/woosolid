<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Charitable {

    /**
     * Hook di inizializzazione
     */
    public static function init() {

        // Quando un ordine WooCommerce viene completato → crea donazioni Charitable
        add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'sync_order_to_charitable' ], 10, 1 );
    }

    /**
     * Sincronizza un ordine WooCommerce con Charitable
     *
     * - Riconosce i prodotti-donazione
     * - Crea una donazione Charitable per ciascuno
     * - Collega utente WooCommerce ↔ donatore Charitable
     */
    public static function sync_order_to_charitable( $order_id ) {

        if ( ! function_exists( 'charitable_get_campaign' ) ) {
            // Charitable non disponibile: esci silenziosamente
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Evita doppia sincronizzazione
        if ( $order->get_meta( '_woosolid_charitable_synced' ) ) {
            return;
        }

        $items = $order->get_items();
        if ( empty( $items ) ) {
            return;
        }

        $user_id    = $order->get_user_id();
        $donor_data = self::get_donor_data_from_order( $order );

        foreach ( $items as $item_id => $item ) {

            $product_id = $item->get_product_id();
            $qty        = $item->get_quantity();
            $line_total = $item->get_total();

            // Verifica se il prodotto è marcato come "donazione"
            $is_donation = get_post_meta( $product_id, '_woosolid_is_donation', true );
            $campaign_id = get_post_meta( $product_id, '_woosolid_campaign_id', true );

            if ( $is_donation !== 'yes' || ! $campaign_id ) {
                continue;
            }

            // Verifica che la campagna esista in Charitable
            $campaign = charitable_get_campaign( $campaign_id );
            if ( ! $campaign ) {
                continue;
            }

            // Importo donazione = totale riga (o per qty, se vuoi gestirlo diversamente)
            $donation_amount = $line_total;

            self::create_charitable_donation(
                $campaign_id,
                $donation_amount,
                $order_id,
                $user_id,
                $donor_data
            );
        }

        // Marca l'ordine come sincronizzato
        $order->update_meta_data( '_woosolid_charitable_synced', 1 );
        $order->save();
    }

    /**
     * Estrae i dati del donatore dall'ordine WooCommerce
     */
    protected static function get_donor_data_from_order( WC_Order $order ) {

        return [
            'first_name' => $order->get_billing_first_name(),
            'last_name'  => $order->get_billing_last_name(),
            'email'      => $order->get_billing_email(),
            'address'    => [
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city'      => $order->get_billing_city(),
                'postcode'  => $order->get_billing_postcode(),
                'country'   => $order->get_billing_country(),
            ],
        ];
    }

    /**
     * Crea una donazione in Charitable a partire da un ordine WooCommerce
     */
    protected static function create_charitable_donation( $campaign_id, $amount, $order_id, $user_id, $donor_data ) {

        if ( ! function_exists( 'charitable_get_donation' ) || ! class_exists( 'Charitable_Donation' ) ) {
            return;
        }

        $donation_data = [
            'campaign_id' => $campaign_id,
            'amount'      => $amount,
            'gateway'     => 'woocommerce', // etichetta logica, non un vero gateway Charitable
            'status'      => 'charitable-completed',
            'user_id'     => $user_id,
            'donor'       => [
                'first_name' => $donor_data['first_name'],
                'last_name'  => $donor_data['last_name'],
                'email'      => $donor_data['email'],
                'address'    => $donor_data['address'],
            ],
        ];

        /**
         * Qui usiamo l'API di Charitable per creare una donazione.
         * A seconda della versione di Charitable, può essere:
         *
         * - charitable_get_donation( charitable_create_donation( $donation_data ) );
         * - new Charitable_Donation( charitable_create_donation( $donation_data ) );
         *
         * Ti lascio il placeholder, da adattare alla tua versione.
         */

        if ( function_exists( 'charitable_create_donation' ) ) {

            $donation_id = charitable_create_donation( $donation_data );

            if ( $donation_id ) {
                // Collega l'ordine WooCommerce alla donazione Charitable
                update_post_meta( $donation_id, '_woosolid_source_order_id', $order_id );
            }
        }
    }
}

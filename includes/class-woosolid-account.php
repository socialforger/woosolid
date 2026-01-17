<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Account {

    public function __construct() {

        // Aggiunge una sezione personalizzata in "Il mio account"
        add_action( 'woocommerce_account_dashboard', [ $this, 'render_account_summary' ] );

        // Aggiunge colonna personalizzata nella lista ordini
        add_filter( 'woocommerce_my_account_my_orders_columns', [ $this, 'add_orders_column' ] );

        // Contenuto della colonna personalizzata
        add_action( 'woocommerce_my_account_my_orders_column_consegna', [ $this, 'render_orders_column' ] );

        // Aggiunge box "Dettagli consegna" nella pagina ordine
        add_action( 'woocommerce_order_details_after_order_table', [ $this, 'render_order_delivery_details' ] );
    }

    /**
     * Sezione personalizzata nella dashboard "Il mio account"
     */
    public function render_account_summary() {

        $user_id = get_current_user_id();
        $meta = WooSolid_User_Meta::get( $user_id );

        echo '<h3 class="woosolid-section-title">Profilo WooSolid</h3>';

        echo '<p><strong>Tipo utente:</strong> ' . esc_html( $meta['user_type'] ) . '</p>';

        if ( $meta['is_org_representative'] === 'yes' ) {

            echo '<h4>Dati Organizzazione</h4>';

            echo '<p><strong>Ragione Sociale:</strong> ' . esc_html( $meta['company_name'] ) . '</p>';
            echo '<p><strong>P.IVA:</strong> ' . esc_html( $meta['company_piva'] ) . '</p>';
            echo '<p><strong>CF Ente:</strong> ' . esc_html( $meta['company_cf'] ) . '</p>';
            echo '<p><strong>PEC:</strong> ' . esc_html( $meta['company_pec'] ) . '</p>';
            echo '<p><strong>SDI:</strong> ' . esc_html( $meta['company_sdi'] ) . '</p>';

            echo '<p><strong>Sede Legale:</strong><br>' .
                esc_html( $meta['company_address'] ) . ', ' .
                esc_html( $meta['company_postcode'] ) . ' ' .
                esc_html( $meta['company_city'] ) . ' (' .
                esc_html( $meta['company_state'] ) . ')</p>';

            if ( $meta['is_ente_gestore'] === 'yes' ) {
                echo '<p><strong>Ente Gestore:</strong> Sì</p>';
            }
        }
    }

    /**
     * Aggiunge colonna "Consegna" nella lista ordini
     */
    public function add_orders_column( $columns ) {
        $new_columns = [];

        foreach ( $columns as $key => $label ) {
            $new_columns[$key] = $label;

            if ( $key === 'order-total' ) {
                $new_columns['consegna'] = 'Consegna';
            }
        }

        return $new_columns;
    }

    /**
     * Contenuto della colonna "Consegna"
     */
    public function render_orders_column( $order ) {

        $pickup = $order->get_meta( '_woosolid_pickup_point' );

        if ( ! empty( $pickup ) ) {
            echo esc_html( $pickup );
        } else {
            echo '<em>Nessuna</em>';
        }
    }

    /**
     * Box "Dettagli consegna" nella pagina ordine
     */
    public function render_order_delivery_details( $order ) {

        $pickup = $order->get_meta( '_woosolid_pickup_point' );

        if ( empty( $pickup ) ) {
            return;
        }

        echo '<section class="woocommerce-order-details woosolid-delivery-box">';
        echo '<h2 class="woocommerce-order-details__title">Dettagli Consegna</h2>';

        echo '<p><strong>Punto di ritiro:</strong><br>' . esc_html( $pickup ) . '</p>';

        echo '</section>';
    }
}

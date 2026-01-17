<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_WooCommerce {

    public function __construct() {

        // Aggiunge/gestisce i campi nel checkout
        add_filter( 'woocommerce_checkout_fields', [ $this, 'checkout_fields' ] );

        // Precompila i campi dal profilo utente
        add_filter( 'woocommerce_checkout_get_value', [ $this, 'prefill_checkout_fields' ], 10, 2 );

        // Salva i meta utente al completamento ordine
        add_action( 'woocommerce_checkout_update_user_meta', [ $this, 'save_user_meta_from_checkout' ], 10, 2 );
    }

    /**
     * Aggiunge/riorganizza i campi del checkout
     */
    public function checkout_fields( $fields ) {

        // Assicuriamoci che Nome/Cognome siano chiaramente "Rappresentante legale"
        $fields['billing']['billing_first_name']['label'] = 'Nome rappresentante legale';
        $fields['billing']['billing_last_name']['label']  = 'Cognome rappresentante legale';

        // Aggiungiamo la Ragione Sociale come campo dedicato
        $fields['billing']['company_name'] = [
            'type'        => 'text',
            'label'       => 'Ragione Sociale',
            'required'    => false,
            'class'       => ['form-row-wide'],
            'priority'    => 60,
        ];

        return $fields;
    }

    /**
     * Precompila i campi del checkout dai meta utente
     */
    public function prefill_checkout_fields( $value, $input ) {

        if ( ! is_user_logged_in() ) {
            return $value;
        }

        $user_id = get_current_user_id();
        $meta    = WooSolid_User_Meta::get( $user_id );

        switch ( $input ) {

            case 'billing_first_name':
                return $value ?: $meta['first_name'];

            case 'billing_last_name':
                return $value ?: $meta['last_name'];

            case 'billing_phone':
                return $value ?: $meta['phone'];

            case 'billing_address_1':
                return $value ?: $meta['pf_address'];

            case 'billing_city':
                return $value ?: $meta['pf_city'];

            case 'billing_postcode':
                return $value ?: $meta['pf_postcode'];

            case 'billing_state':
                return $value ?: $meta['pf_state'];

            case 'billing_country':
                return $value ?: $meta['pf_country'];

            case 'company_name':
                return $value ?: $meta['company_name'];
        }

        return $value;
    }

    /**
     * Salva i meta utente quando l’ordine viene completato
     */
    public function save_user_meta_from_checkout( $customer_id, $posted ) {

        if ( ! $customer_id ) {
            return;
        }

        $meta = WooSolid_User_Meta::get( $customer_id );
        $data = [];

        // Persona fisica (rappresentante legale)
        $data['first_name']     = $posted['billing_first_name'] ?? $meta['first_name'];
        $data['last_name']      = $posted['billing_last_name'] ?? $meta['last_name'];
        $data['phone']          = $posted['billing_phone'] ?? $meta['phone'];
        $data['pf_address']     = $posted['billing_address_1'] ?? $meta['pf_address'];
        $data['pf_city']        = $posted['billing_city'] ?? $meta['pf_city'];
        $data['pf_postcode']    = $posted['billing_postcode'] ?? $meta['pf_postcode'];
        $data['pf_state']       = $posted['billing_state'] ?? $meta['pf_state'];
        $data['pf_country']     = $posted['billing_country'] ?? $meta['pf_country'];

        // Ragione sociale (se presente)
        if ( ! empty( $posted['company_name'] ) ) {

            $data['company_name']          = $posted['company_name'];
            $data['is_org_representative'] = 'yes';

            // Se prima era PF, ora diventa PG
            if ( $meta['user_type'] !== 'persona_giuridica' ) {
                $data['user_type'] = 'persona_giuridica';
            }
        } else {
            // Se non c'è ragione sociale, non forziamo nulla: resta il tipo attuale
            $data['user_type']             = $meta['user_type'] ?: 'persona_fisica';
            $data['is_org_representative'] = $meta['is_org_representative'];
        }

        // Manteniamo il flag ente gestore se già presente
        $data['is_ente_gestore'] = $meta['is_ente_gestore'];

        WooSolid_User_Meta::save( $customer_id, $data );
    }
}

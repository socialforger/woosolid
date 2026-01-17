<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WooSolid_User_Meta
 *
 * Gestione centralizzata dei meta utente:
 * - Persona fisica
 * - Persona giuridica
 * - Ente gestore
 * - Rappresentante legale
 */
class WooSolid_User_Meta {

    /**
     * Restituisce tutti i meta utente WooSolid in forma normalizzata
     */
    public static function get( $user_id ) {

        $meta = [];

        // Persona fisica
        $meta['first_name']       = get_user_meta( $user_id, 'billing_first_name', true );
        $meta['last_name']        = get_user_meta( $user_id, 'billing_last_name', true );
        $meta['codice_fiscale']   = get_user_meta( $user_id, 'codice_fiscale', true );
        $meta['phone']            = get_user_meta( $user_id, 'billing_phone', true );

        $meta['pf_address']       = get_user_meta( $user_id, 'billing_address_1', true );
        $meta['pf_city']          = get_user_meta( $user_id, 'billing_city', true );
        $meta['pf_postcode']      = get_user_meta( $user_id, 'billing_postcode', true );
        $meta['pf_state']         = get_user_meta( $user_id, 'billing_state', true );
        $meta['pf_country']       = get_user_meta( $user_id, 'billing_country', true );

        // Persona giuridica
        $meta['company_name']     = get_user_meta( $user_id, 'company_name', true );
        $meta['company_type']     = get_user_meta( $user_id, 'company_type', true );
        $meta['company_piva']     = get_user_meta( $user_id, 'company_piva', true );
        $meta['company_cf']       = get_user_meta( $user_id, 'company_cf', true );
        $meta['company_pec']      = get_user_meta( $user_id, 'company_pec', true );
        $meta['company_sdi']      = get_user_meta( $user_id, 'company_sdi', true );

        $meta['company_address']  = get_user_meta( $user_id, 'company_address', true );
        $meta['company_city']     = get_user_meta( $user_id, 'company_city', true );
        $meta['company_postcode'] = get_user_meta( $user_id, 'company_postcode', true );
        $meta['company_state']    = get_user_meta( $user_id, 'company_state', true );
        $meta['company_country']  = get_user_meta( $user_id, 'company_country', true );

        // Flag WooSolid
        $meta['user_type']                = get_user_meta( $user_id, '_woosolid_user_type', true );
        $meta['is_org_representative']    = get_user_meta( $user_id, '_woosolid_is_org_representative', true );
        $meta['is_ente_gestore']          = get_user_meta( $user_id, '_woosolid_is_ente_gestore', true );

        return $meta;
    }

    /**
     * Salva i meta utente WooSolid
     */
    public static function save( $user_id, $data ) {

        // Persona fisica
        update_user_meta( $user_id, 'billing_first_name', sanitize_text_field( $data['first_name'] ?? '' ) );
        update_user_meta( $user_id, 'billing_last_name', sanitize_text_field( $data['last_name'] ?? '' ) );
        update_user_meta( $user_id, 'codice_fiscale', sanitize_text_field( $data['codice_fiscale'] ?? '' ) );
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $data['phone'] ?? '' ) );

        update_user_meta( $user_id, 'billing_address_1', sanitize_text_field( $data['pf_address'] ?? '' ) );
        update_user_meta( $user_id, 'billing_city', sanitize_text_field( $data['pf_city'] ?? '' ) );
        update_user_meta( $user_id, 'billing_postcode', sanitize_text_field( $data['pf_postcode'] ?? '' ) );
        update_user_meta( $user_id, 'billing_state', sanitize_text_field( $data['pf_state'] ?? '' ) );
        update_user_meta( $user_id, 'billing_country', sanitize_text_field( $data['pf_country'] ?? 'IT' ) );

        // Persona giuridica
        update_user_meta( $user_id, 'company_name', sanitize_text_field( $data['company_name'] ?? '' ) );
        update_user_meta( $user_id, 'company_type', sanitize_text_field( $data['company_type'] ?? '' ) );
        update_user_meta( $user_id, 'company_piva', sanitize_text_field( $data['company_piva'] ?? '' ) );
        update_user_meta( $user_id, 'company_cf', sanitize_text_field( $data['company_cf'] ?? '' ) );
        update_user_meta( $user_id, 'company_pec', sanitize_email( $data['company_pec'] ?? '' ) );
        update_user_meta( $user_id, 'company_sdi', sanitize_text_field( $data['company_sdi'] ?? '' ) );

        update_user_meta( $user_id, 'company_address', sanitize_text_field( $data['company_address'] ?? '' ) );
        update_user_meta( $user_id, 'company_city', sanitize_text_field( $data['company_city'] ?? '' ) );
        update_user_meta( $user_id, 'company_postcode', sanitize_text_field( $data['company_postcode'] ?? '' ) );
        update_user_meta( $user_id, 'company_state', sanitize_text_field( $data['company_state'] ?? '' ) );
        update_user_meta( $user_id, 'company_country', sanitize_text_field( $data['company_country'] ?? 'IT' ) );

        // Flag WooSolid
        update_user_meta( $user_id, '_woosolid_user_type', sanitize_text_field( $data['user_type'] ?? 'persona_fisica' ) );
        update_user_meta( $user_id, '_woosolid_is_org_representative', sanitize_text_field( $data['is_org_representative'] ?? 'no' ) );
        update_user_meta( $user_id, '_woosolid_is_ente_gestore', sanitize_text_field( $data['is_ente_gestore'] ?? 'no' ) );
    }

    /**
     * Imposta un utente come ente gestore
     */
    public static function set_ente_gestore( $user_id, $company_data = [] ) {

        $data = array_merge( $company_data, [
            'user_type'             => 'persona_giuridica',
            'is_org_representative' => 'yes',
            'is_ente_gestore'       => 'yes',
        ]);

        self::save( $user_id, $data );
    }

    /**
     * Verifica se un utente è ente gestore
     */
    public static function is_ente_gestore( $user_id ) {
        return get_user_meta( $user_id, '_woosolid_is_ente_gestore', true ) === 'yes';
    }

    /**
     * Verifica se un utente è rappresentante legale
     */
    public static function is_rappresentante( $user_id ) {
        return get_user_meta( $user_id, '_woosolid_is_org_representative', true ) === 'yes';
    }
}

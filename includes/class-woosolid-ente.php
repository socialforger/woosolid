<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WooSolid_Ente
 *
 * Wrapper logico per la gestione dell’Ente Gestore.
 * Non salva meta (delegato a WooSolid_User_Meta).
 * Non crea utenti (delegato al Wizard).
 * Fornisce solo metodi di lettura e utilità.
 */
class WooSolid_Ente {

    /**
     * Verifica se un utente è ente gestore
     */
    public static function is_ente_gestore( $user_id ) {
        return WooSolid_User_Meta::is_ente_gestore( $user_id );
    }

    /**
     * Restituisce i dati completi dell’ente gestore
     */
    public static function get_ente_data( $user_id ) {

        if ( ! self::is_ente_gestore( $user_id ) ) {
            return null;
        }

        $meta = WooSolid_User_Meta::get( $user_id );

        return [
            'company_name'     => $meta['company_name'],
            'company_type'     => $meta['company_type'],
            'company_piva'     => $meta['company_piva'],
            'company_cf'       => $meta['company_cf'],
            'company_pec'      => $meta['company_pec'],
            'company_sdi'      => $meta['company_sdi'],

            'company_address'  => $meta['company_address'],
            'company_city'     => $meta['company_city'],
            'company_postcode' => $meta['company_postcode'],
            'company_state'    => $meta['company_state'],
            'company_country'  => $meta['company_country'],

            'representante' => [
                'first_name'     => $meta['first_name'],
                'last_name'      => $meta['last_name'],
                'codice_fiscale' => $meta['codice_fiscale'],
                'phone'          => $meta['phone'],
                'address'        => $meta['pf_address'],
                'city'           => $meta['pf_city'],
                'postcode'       => $meta['pf_postcode'],
                'state'          => $meta['pf_state'],
                'country'        => $meta['pf_country'],
            ]
        ];
    }

    /**
     * Restituisce il nome dell’ente gestore
     */
    public static function get_nome_ente( $user_id ) {

        if ( ! self::is_ente_gestore( $user_id ) ) {
            return null;
        }

        $meta = WooSolid_User_Meta::get( $user_id );
        return $meta['company_name'];
    }

    /**
     * Restituisce i dati del rappresentante legale
     */
    public static function get_rappresentante( $user_id ) {

        $meta = WooSolid_User_Meta::get( $user_id );

        if ( $meta['is_org_representative'] !== 'yes' ) {
            return null;
        }

        return [
            'first_name'     => $meta['first_name'],
            'last_name'      => $meta['last_name'],
            'codice_fiscale' => $meta['codice_fiscale'],
            'phone'          => $meta['phone'],
            'address'        => $meta['pf_address'],
            'city'           => $meta['pf_city'],
            'postcode'       => $meta['pf_postcode'],
            'state'          => $meta['pf_state'],
            'country'        => $meta['pf_country'],
        ];
    }

    /**
     * Restituisce un array pronto per API/JSON
     */
    public static function to_array( $user_id ) {
        return self::get_ente_data( $user_id );
    }
}

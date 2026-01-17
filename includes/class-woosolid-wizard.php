<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WooSolid_Wizard
 *
 * Wizard automatico e invisibile.
 * - Nessun menu
 * - Nessuna pagina
 * - Nessun form
 * - Nessuna interazione manuale
 *
 * Crea automaticamente l’Ente Gestore se non esiste.
 */
class WooSolid_Wizard {

    /**
     * Avvia il wizard automatico
     */
    public static function run_auto_wizard() {

        // Se esiste già un ente gestore → non fare nulla
        $existing = get_users([
            'meta_key'   => '_woosolid_is_ente_gestore',
            'meta_value' => 'yes',
            'number'     => 1,
            'fields'     => 'ID'
        ]);

        if ( ! empty( $existing ) ) {
            return; // Ente già presente → stop
        }

        // Crea automaticamente un utente rappresentante legale
        $email = 'ente-gestore@localhost';
        $password = wp_generate_password( 12, true );

        $user_id = wp_create_user( $email, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            return; // Non possiamo creare l’utente → stop
        }

        // Imposta ruolo amministratore (come nel WooSolid originale)
        $user = new WP_User( $user_id );
        $user->set_role( 'administrator' );

        // Dati minimi dell’ente gestore
        $data = [
            // Persona fisica (rappresentante)
            'first_name'     => 'Rappresentante',
            'last_name'      => 'Legale',
            'codice_fiscale' => 'AAAAAAAAAAAAAA',
            'phone'          => '',
            'pf_address'     => '',
            'pf_city'        => '',
            'pf_postcode'    => '',
            'pf_state'       => '',
            'pf_country'     => 'IT',

            // Persona giuridica (ente)
            'company_name'     => 'Ente Gestore',
            'company_type'     => 'Associazione',
            'company_piva'     => '',
            'company_cf'       => '',
            'company_pec'      => '',
            'company_sdi'      => '',
            'company_address'  => '',
            'company_city'     => '',
            'company_postcode' => '',
            'company_state'    => '',
            'company_country'  => 'IT',

            // Flag WooSolid
            'user_type'             => 'persona_giuridica',
            'is_org_representative' => 'yes',
            'is_ente_gestore'       => 'yes',
        ];

        // Salva i meta tramite il modello unico
        WooSolid_User_Meta::save( $user_id, $data );
    }
}

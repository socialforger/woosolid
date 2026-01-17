<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Account_Edit {

    public function __construct() {

        // Aggiunge campi extra nella pagina "Modifica account"
        add_action( 'woocommerce_edit_account_form', [ $this, 'render_account_fields' ] );

        // Salva i campi extra
        add_action( 'woocommerce_save_account_details', [ $this, 'save_account_fields' ] );

        // Aggiunge campi extra nella pagina "Indirizzi"
        add_filter( 'woocommerce_billing_fields', [ $this, 'billing_fields' ] );
    }

    /**
     * Aggiunge i campi WooSolid nella pagina "Modifica account"
     */
    public function render_account_fields() {

        $user_id = get_current_user_id();
        $meta = WooSolid_User_Meta::get( $user_id );

        ?>
        <h3 class="woosolid-section-title">Dati personali</h3>

        <p class="form-row form-row-first">
            <label for="billing_first_name">Nome</label>
            <input type="text" name="billing_first_name" id="billing_first_name"
                   value="<?php echo esc_attr( $meta['first_name'] ); ?>" />
        </p>

        <p class="form-row form-row-last">
            <label for="billing_last_name">Cognome</label>
            <input type="text" name="billing_last_name" id="billing_last_name"
                   value="<?php echo esc_attr( $meta['last_name'] ); ?>" />
        </p>

        <p class="form-row form-row-wide">
            <label for="codice_fiscale">Codice Fiscale</label>
            <input type="text" name="codice_fiscale" id="codice_fiscale"
                   value="<?php echo esc_attr( $meta['codice_fiscale'] ); ?>" />
        </p>

        <p class="form-row form-row-wide">
            <label for="billing_phone">Telefono</label>
            <input type="text" name="billing_phone" id="billing_phone"
                   value="<?php echo esc_attr( $meta['phone'] ); ?>" />
        </p>

        <h3 class="woosolid-section-title">Indirizzo personale</h3>

        <p class="form-row form-row-wide">
            <label for="billing_address_1">Indirizzo</label>
            <input type="text" name="billing_address_1" id="billing_address_1"
                   value="<?php echo esc_attr( $meta['pf_address'] ); ?>" />
        </p>

        <p class="form-row form-row-first">
            <label for="billing_city">Città</label>
            <input type="text" name="billing_city" id="billing_city"
                   value="<?php echo esc_attr( $meta['pf_city'] ); ?>" />
        </p>

        <p class="form-row form-row-last">
            <label for="billing_postcode">CAP</label>
            <input type="text" name="billing_postcode" id="billing_postcode"
                   value="<?php echo esc_attr( $meta['pf_postcode'] ); ?>" />
        </p>

        <p class="form-row form-row-first">
            <label for="billing_state">Provincia</label>
            <input type="text" name="billing_state" id="billing_state"
                   value="<?php echo esc_attr( $meta['pf_state'] ); ?>" />
        </p>

        <p class="form-row form-row-last">
            <label for="billing_country">Nazione</label>
            <input type="text" name="billing_country" id="billing_country"
                   value="<?php echo esc_attr( $meta['pf_country'] ); ?>" />
        </p>

        <?php if ( $meta['is_org_representative'] === 'yes' ) : ?>

            <h3 class="woosolid-section-title">Dati organizzazione</h3>

            <p class="form-row form-row-wide">
                <label for="company_name">Ragione Sociale</label>
                <input type="text" name="company_name" id="company_name"
                       value="<?php echo esc_attr( $meta['company_name'] ); ?>" />
            </p>

            <p class="form-row form-row-first">
                <label for="company_type">Tipo Ente</label>
                <input type="text" name="company_type" id="company_type"
                       value="<?php echo esc_attr( $meta['company_type'] ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="company_piva">Partita IVA</label>
                <input type="text" name="company_piva" id="company_piva"
                       value="<?php echo esc_attr( $meta['company_piva'] ); ?>" />
            </p>

            <p class="form-row form-row-wide">
                <label for="company_cf">Codice Fiscale Ente</label>
                <input type="text" name="company_cf" id="company_cf"
                       value="<?php echo esc_attr( $meta['company_cf'] ); ?>" />
            </p>

            <p class="form-row form-row-first">
                <label for="company_pec">PEC</label>
                <input type="email" name="company_pec" id="company_pec"
                       value="<?php echo esc_attr( $meta['company_pec'] ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="company_sdi">SDI</label>
                <input type="text" name="company_sdi" id="company_sdi"
                       value="<?php echo esc_attr( $meta['company_sdi'] ); ?>" />
            </p>

            <h3 class="woosolid-section-title">Sede legale</h3>

            <p class="form-row form-row-wide">
                <label for="company_address">Indirizzo</label>
                <input type="text" name="company_address" id="company_address"
                       value="<?php echo esc_attr( $meta['company_address'] ); ?>" />
            </p>

            <p class="form-row form-row-first">
                <label for="company_city">Città</label>
                <input type="text" name="company_city" id="company_city"
                       value="<?php echo esc_attr( $meta['company_city'] ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="company_postcode">CAP</label>
                <input type="text" name="company_postcode" id="company_postcode"
                       value="<?php echo esc_attr( $meta['company_postcode'] ); ?>" />
            </p>

            <p class="form-row form-row-first">
                <label for="company_state">Provincia</label>
                <input type="text" name="company_state" id="company_state"
                       value="<?php echo esc_attr( $meta['company_state'] ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="company_country">Nazione</label>
                <input type="text" name="company_country" id="company_country"
                       value="<?php echo esc_attr( $meta['company_country'] ); ?>" />
            </p>

        <?php endif;
    }

    /**
     * Salvataggio dei campi nella pagina "Modifica account"
     */
    public function save_account_fields( $user_id ) {

        $meta = WooSolid_User_Meta::get( $user_id );
        $data = [];

        // Persona fisica
        $data['first_name']     = $_POST['billing_first_name'] ?? $meta['first_name'];
        $data['last_name']      = $_POST['billing_last_name'] ?? $meta['last_name'];
        $data['codice_fiscale'] = $_POST['codice_fiscale'] ?? $meta['codice_fiscale'];
        $data['phone']          = $_POST['billing_phone'] ?? $meta['phone'];

        $data['pf_address']     = $_POST['billing_address_1'] ?? $meta['pf_address'];
        $data['pf_city']        = $_POST['billing_city'] ?? $meta['pf_city'];
        $data['pf_postcode']    = $_POST['billing_postcode'] ?? $meta['pf_postcode'];
        $data['pf_state']       = $_POST['billing_state'] ?? $meta['pf_state'];
        $data['pf_country']     = $_POST['billing_country'] ?? $meta['pf_country'];

        // Se rappresentante legale → salva i dati dell’ente
        if ( $meta['is_org_representative'] === 'yes' ) {

            $data['company_name']     = $_POST['company_name'] ?? $meta['company_name'];
            $data['company_type']     = $_POST['company_type'] ?? $meta['company_type'];
            $data['company_piva']     = $_POST['company_piva'] ?? $meta['company_piva'];
            $data['company_cf']       = $_POST['company_cf'] ?? $meta['company_cf'];
            $data['company_pec']      = $_POST['company_pec'] ?? $meta['company_pec'];
            $data['company_sdi']      = $_POST['company_sdi'] ?? $meta['company_sdi'];

            $data['company_address']  = $_POST['company_address'] ?? $meta['company_address'];
            $data['company_city']     = $_POST['company_city'] ?? $meta['company_city'];
            $data['company_postcode'] = $_POST['company_postcode'] ?? $meta['company_postcode'];
            $data['company_state']    = $_POST['company_state'] ?? $meta['company_state'];
            $data['company_country']  = $_POST['company_country'] ?? $meta['company_country'];
        }

        WooSolid_User_Meta::save( $user_id, $data );
    }

    /**
     * Aggiunge i campi WooSolid nella pagina "Indirizzi"
     */
    public function billing_fields( $fields ) {

        $user_id = get_current_user_id();
        $meta = WooSolid_User_Meta::get( $user_id );

        // Aggiunge il CF persona fisica
        $fields['codice_fiscale'] = [
            'label'       => 'Codice Fiscale',
            'required'    => true,
            'class'       => ['form-row-wide'],
            'priority'    => 22,
            'default'     => $meta['codice_fiscale'],
        ];

        return $fields;
    }
}

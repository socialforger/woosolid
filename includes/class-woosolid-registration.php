<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Registration {

    public function __construct() {
        // Aggiunge i campi al form di registrazione WooCommerce
        add_action( 'woocommerce_register_form', [ $this, 'render_registration_fields' ] );

        // Valida i campi
        add_filter( 'woocommerce_registration_errors', [ $this, 'validate_registration_fields' ], 10, 3 );

        // Salva i meta utente
        add_action( 'woocommerce_created_customer', [ $this, 'save_registration_fields' ] );
    }

    /**
     * Render del form unico PF/PG
     */
    public function render_registration_fields() {

        ?>
        <h3 class="woosolid-section-title">Dati personali</h3>

        <p class="form-row form-row-first">
            <label for="billing_first_name">Nome <span class="required">*</span></label>
            <input type="text" name="billing_first_name" id="billing_first_name" value="<?php echo esc_attr( $_POST['billing_first_name'] ?? '' ); ?>" required />
        </p>

        <p class="form-row form-row-last">
            <label for="billing_last_name">Cognome <span class="required">*</span></label>
            <input type="text" name="billing_last_name" id="billing_last_name" value="<?php echo esc_attr( $_POST['billing_last_name'] ?? '' ); ?>" required />
        </p>

        <p class="form-row form-row-wide">
            <label for="codice_fiscale">Codice Fiscale <span class="required">*</span></label>
            <input type="text" name="codice_fiscale" id="codice_fiscale" value="<?php echo esc_attr( $_POST['codice_fiscale'] ?? '' ); ?>" required />
        </p>

        <p class="form-row form-row-wide">
            <label for="billing_phone">Telefono <span class="required">*</span></label>
            <input type="text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $_POST['billing_phone'] ?? '' ); ?>" required />
        </p>

        <h3 class="woosolid-section-title">Indirizzo personale</h3>

        <p class="form-row form-row-wide">
            <label for="billing_address_1">Indirizzo</label>
            <input type="text" name="billing_address_1" id="billing_address_1" value="<?php echo esc_attr( $_POST['billing_address_1'] ?? '' ); ?>" />
        </p>

        <p class="form-row form-row-first">
            <label for="billing_city">Città</label>
            <input type="text" name="billing_city" id="billing_city" value="<?php echo esc_attr( $_POST['billing_city'] ?? '' ); ?>" />
        </p>

        <p class="form-row form-row-last">
            <label for="billing_postcode">CAP</label>
            <input type="text" name="billing_postcode" id="billing_postcode" value="<?php echo esc_attr( $_POST['billing_postcode'] ?? '' ); ?>" />
        </p>

        <p class="form-row form-row-first">
            <label for="billing_state">Provincia</label>
            <input type="text" name="billing_state" id="billing_state" value="<?php echo esc_attr( $_POST['billing_state'] ?? '' ); ?>" />
        </p>

        <p class="form-row form-row-last">
            <label for="billing_country">Nazione</label>
            <input type="text" name="billing_country" id="billing_country" value="<?php echo esc_attr( $_POST['billing_country'] ?? 'IT' ); ?>" />
        </p>

        <h3 class="woosolid-section-title">Rappresentanza</h3>

        <p class="form-row form-row-wide">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox">
                <input type="checkbox" name="is_org_representative" id="is_org_representative" value="yes"
                    <?php checked( $_POST['is_org_representative'] ?? '', 'yes' ); ?> />
                <span>Sei il rappresentante legale di una organizzazione?</span>
            </label>
        </p>

        <div id="woosolid-company-fields" style="display: <?php echo ( isset($_POST['is_org_representative']) && $_POST['is_org_representative'] === 'yes' ) ? 'block' : 'none'; ?>;">

            <h3 class="woosolid-section-title">Dati organizzazione</h3>

            <p class="form-row form-row-wide">
                <label for="company_name">Ragione Sociale <span class="required">*</span></label>
                <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr( $_POST['company_name'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-first">
                <label for="company_type">Tipo Ente</label>
                <input type="text" name="company_type" id="company_type" value="<?php echo esc_attr( $_POST['company_type'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="company_piva">Partita IVA</label>
                <input type="text" name="company_piva" id="company_piva" value="<?php echo esc_attr( $_POST['company_piva'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-wide">
                <label for="company_cf">Codice Fiscale Ente</label>
                <input type="text" name="company_cf" id="company_cf" value="<?php echo esc_attr( $_POST['company_cf'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-first">
                <label for="company_pec">PEC</label>
                <input type="email" name="company_pec" id="company_pec" value="<?php echo esc_attr( $_POST['company_pec'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="company_sdi">SDI</label>
                <input type="text" name="company_sdi" id="company_sdi" value="<?php echo esc_attr( $_POST['company_sdi'] ?? '' ); ?>" />
            </p>

            <h3 class="woosolid-section-title">Sede legale</h3>

            <p class="form-row form-row-wide">
                <label for="company_address">Indirizzo</label>
                <input type="text" name="company_address" id="company_address" value="<?php echo esc_attr( $_POST['company_address'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-first">
                <label for="company_city">Città</label>
                <input type="text" name="company_city" id="company_city" value="<?php echo esc_attr( $_POST['company_city'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="company_postcode">CAP</label>
                <input type="text" name="company_postcode" id="company_postcode" value="<?php echo esc_attr( $_POST['company_postcode'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-first">
                <label for="company_state">Provincia</label>
                <input type="text" name="company_state" id="company_state" value="<?php echo esc_attr( $_POST['company_state'] ?? '' ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="company_country">Nazione</label>
                <input type="text" name="company_country" id="company_country" value="<?php echo esc_attr( $_POST['company_country'] ?? 'IT' ); ?>" />
            </p>

        </div>

        <script>
            jQuery(function($){
                $('#is_org_representative').on('change', function(){
                    $('#woosolid-company-fields').toggle( this.checked );
                });
            });
        </script>

        <?php
    }

    /**
     * Validazione
     */
    public function validate_registration_fields( $errors, $username, $email ) {

        if ( empty( $_POST['billing_first_name'] ) )
            $errors->add( 'billing_first_name_error', 'Inserisci il nome.' );

        if ( empty( $_POST['billing_last_name'] ) )
            $errors->add( 'billing_last_name_error', 'Inserisci il cognome.' );

        if ( empty( $_POST['codice_fiscale'] ) )
            $errors->add( 'codice_fiscale_error', 'Inserisci il codice fiscale.' );

        if ( empty( $_POST['billing_phone'] ) )
            $errors->add( 'billing_phone_error', 'Inserisci il telefono.' );

        // Se rappresentante legale → richiedi i dati dell’ente
        if ( isset($_POST['is_org_representative']) && $_POST['is_org_representative'] === 'yes' ) {

            if ( empty( $_POST['company_name'] ) )
                $errors->add( 'company_name_error', 'Inserisci la ragione sociale dell’organizzazione.' );
        }

        return $errors;
    }

    /**
     * Salvataggio meta
     */
    public function save_registration_fields( $user_id ) {

        $data = [];

        // Persona fisica
        $data['first_name']     = $_POST['billing_first_name'] ?? '';
        $data['last_name']      = $_POST['billing_last_name'] ?? '';
        $data['codice_fiscale'] = $_POST['codice_fiscale'] ?? '';
        $data['phone']          = $_POST['billing_phone'] ?? '';

        $data['pf_address']     = $_POST['billing_address_1'] ?? '';
        $data['pf_city']        = $_POST['billing_city'] ?? '';
        $data['pf_postcode']    = $_POST['billing_postcode'] ?? '';
        $data['pf_state']       = $_POST['billing_state'] ?? '';
        $data['pf_country']     = $_POST['billing_country'] ?? 'IT';

        // Flag rappresentanza
        $is_rep = isset($_POST['is_org_representative']) && $_POST['is_org_representative'] === 'yes';
        $data['is_org_representative'] = $is_rep ? 'yes' : 'no';

        // Tipo utente
        $data['user_type'] = $is_rep ? 'persona_giuridica' : 'persona_fisica';

        // Dati organizzazione (solo se rappresentante)
        if ( $is_rep ) {

            $data['company_name']     = $_POST['company_name'] ?? '';
            $data['company_type']     = $_POST['company_type'] ?? '';
            $data['company_piva']     = $_POST['company_piva'] ?? '';
            $data['company_cf']       = $_POST['company_cf'] ?? '';
            $data['company_pec']      = $_POST['company_pec'] ?? '';
            $data['company_sdi']      = $_POST['company_sdi'] ?? '';

            $data['company_address']  = $_POST['company_address'] ?? '';
            $data['company_city']     = $_POST['company_city'] ?? '';
            $data['company_postcode'] = $_POST['company_postcode'] ?? '';
            $data['company_state']    = $_POST['company_state'] ?? '';
            $data['company_country']  = $_POST['company_country'] ?? 'IT';
        }

        // Salva tutto tramite UserMeta
        WooSolid_User_Meta::save( $user_id, $data );
    }
}

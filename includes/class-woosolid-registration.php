<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Registration {

    public static function init() {

        // Aggiunge i campi al form WooCommerce
        add_action( 'woocommerce_register_form', [ __CLASS__, 'render_fields' ] );

        // Validazione
        add_filter( 'woocommerce_registration_errors', [ __CLASS__, 'validate_fields' ], 10, 3 );

        // Salvataggio meta
        add_action( 'woocommerce_created_customer', [ __CLASS__, 'save_fields' ] );
    }

    /**
     * Render campi registrazione (vecchio modello WooSolid)
     */
    public static function render_fields() {
        ?>

        <h3>Informazioni personali</h3>

        <p class="form-row form-row-first">
            <label for="reg_first_name">Nome <span class="required">*</span></label>
            <input type="text" name="first_name" id="reg_first_name" value="<?php echo esc_attr( $_POST['first_name'] ?? '' ); ?>">
        </p>

        <p class="form-row form-row-last">
            <label for="reg_last_name">Cognome <span class="required">*</span></label>
            <input type="text" name="last_name" id="reg_last_name" value="<?php echo esc_attr( $_POST['last_name'] ?? '' ); ?>">
        </p>

        <p class="form-row form-row-wide">
            <label for="reg_codice_fiscale">Codice Fiscale <span class="required">*</span></label>
            <input type="text" name="codice_fiscale" id="reg_codice_fiscale" value="<?php echo esc_attr( $_POST['codice_fiscale'] ?? '' ); ?>">
        </p>

        <p class="form-row form-row-wide">
            <label for="reg_address">Indirizzo <span class="required">*</span></label>
            <input type="text" name="billing_address_1" id="reg_address" value="<?php echo esc_attr( $_POST['billing_address_1'] ?? '' ); ?>">
        </p>

        <p class="form-row form-row-first">
            <label for="reg_city">Città <span class="required">*</span></label>
            <input type="text" name="billing_city" id="reg_city" value="<?php echo esc_attr( $_POST['billing_city'] ?? '' ); ?>">
        </p>

        <p class="form-row form-row-last">
            <label for="reg_postcode">CAP <span class="required">*</span></label>
            <input type="text" name="billing_postcode" id="reg_postcode" value="<?php echo esc_attr( $_POST['billing_postcode'] ?? '' ); ?>">
        </p>

        <p class="form-row form-row-first">
            <label for="reg_state">Provincia <span class="required">*</span></label>
            <input type="text" name="billing_state" id="reg_state" value="<?php echo esc_attr( $_POST['billing_state'] ?? '' ); ?>">
        </p>

        <p class="form-row form-row-last">
            <label for="reg_phone">Telefono <span class="required">*</span></label>
            <input type="text" name="billing_phone" id="reg_phone" value="<?php echo esc_attr( $_POST['billing_phone'] ?? '' ); ?>">
        </p>

        <h3>Rappresentanza</h3>

        <p class="form-row form-row-wide">
            <label>Sei il rappresentante di una organizzazione?</label><br>
            <label><input type="radio" name="is_org_rep" value="no" checked> No</label>
            <label><input type="radio" name="is_org_rep" value="yes"> Sì</label>
        </p>

        <div id="woosolid_org_fields" style="display:none; margin-top:20px;">

            <h3>Dati organizzazione</h3>

            <p class="form-row form-row-wide">
                <label for="company_name">Ragione sociale</label>
                <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr( $_POST['company_name'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-first">
                <label for="company_type">Tipo ente</label>
                <input type="text" name="company_type" id="company_type" value="<?php echo esc_attr( $_POST['company_type'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-last">
                <label for="company_piva">Partita IVA</label>
                <input type="text" name="company_piva" id="company_piva" value="<?php echo esc_attr( $_POST['company_piva'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-wide">
                <label for="company_cf">Codice Fiscale Ente</label>
                <input type="text" name="company_cf" id="company_cf" value="<?php echo esc_attr( $_POST['company_cf'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-first">
                <label for="company_pec">PEC</label>
                <input type="email" name="company_pec" id="company_pec" value="<?php echo esc_attr( $_POST['company_pec'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-last">
                <label for="company_sdi">Codice SDI</label>
                <input type="text" name="company_sdi" id="company_sdi" value="<?php echo esc_attr( $_POST['company_sdi'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-wide">
                <label for="company_address">Indirizzo sede legale</label>
                <input type="text" name="company_address" id="company_address" value="<?php echo esc_attr( $_POST['company_address'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-first">
                <label for="company_city">Città</label>
                <input type="text" name="company_city" id="company_city" value="<?php echo esc_attr( $_POST['company_city'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-last">
                <label for="company_postcode">CAP</label>
                <input type="text" name="company_postcode" id="company_postcode" value="<?php echo esc_attr( $_POST['company_postcode'] ?? '' ); ?>">
            </p>

            <p class="form-row form-row-wide">
                <label for="company_state">Provincia</label>
                <input type="text" name="company_state" id="company_state" value="<?php echo esc_attr( $_POST['company_state'] ?? '' ); ?>">
            </p>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function(){
                const radios = document.querySelectorAll('input[name="is_org_rep"]');
                const orgFields = document.getElementById('woosolid_org_fields');

                function toggleOrgFields() {
                    orgFields.style.display = document.querySelector('input[name="is_org_rep"]:checked').value === 'yes'
                        ? 'block'
                        : 'none';
                }

                radios.forEach(r => r.addEventListener('change', toggleOrgFields));
                toggleOrgFields();
            });
        </script>

        <?php
    }

    /**
     * Validazione
     */
    public static function validate_fields( $errors, $username, $email ) {

        $required = [
            'first_name'        => 'Nome',
            'last_name'         => 'Cognome',
            'codice_fiscale'    => 'Codice Fiscale',
            'billing_address_1' => 'Indirizzo',
            'billing_city'      => 'Città',
            'billing_postcode'  => 'CAP',
            'billing_state'     => 'Provincia',
            'billing_phone'     => 'Telefono',
        ];

        foreach ( $required as $field => $label ) {
            if ( empty( $_POST[$field] ) ) {
                $errors->add( 'required_'.$field, "Il campo $label è obbligatorio." );
            }
        }

        if ( isset($_POST['is_org_rep']) && $_POST['is_org_rep'] === 'yes' ) {
            if ( empty($_POST['company_piva']) ) {
                $errors->add( 'required_piva', 'La Partita IVA è obbligatoria per le organizzazioni.' );
            }
        }

        return $errors;
    }

    /**
     * Salvataggio meta utente
     */
    public static function save_fields( $user_id ) {

        // Persona fisica
        update_user_meta( $user_id, 'billing_first_name', sanitize_text_field($_POST['first_name']) );
        update_user_meta( $user_id, 'billing_last_name', sanitize_text_field($_POST['last_name']) );
        update_user_meta( $user_id, 'codice_fiscale', sanitize_text_field($_POST['codice_fiscale']) );
        update_user_meta( $user_id, 'billing_address_1', sanitize_text_field($_POST['billing_address_1']) );
        update_user_meta( $user_id, 'billing_city', sanitize_text_field($_POST['billing_city']) );
        update_user_meta( $user_id, 'billing_postcode', sanitize_text_field($_POST['billing_postcode']) );
        update_user_meta( $user_id, 'billing_state', sanitize_text_field($_POST['billing_state']) );
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']) );

        // Rappresentanza
        $is_rep = $_POST['is_org_rep'] ?? 'no';
        update_user_meta( $user_id, '_woosolid_is_org_representative', $is_rep );

        if ( $is_rep === 'yes' ) {

            update_user_meta( $user_id, '_woosolid_user_type', 'persona_giuridica' );

            update_user_meta( $user_id, 'company_name', sanitize_text_field($_POST['company_name']) );
            update_user_meta( $user_id, 'company_type', sanitize_text_field($_POST['company_type']) );
            update_user_meta( $user_id, 'company_piva', sanitize_text_field($_POST['company_piva']) );
            update_user_meta( $user_id, 'company_cf', sanitize_text_field($_POST['company_cf']) );
            update_user_meta( $user_id, 'company_pec', sanitize_text_field($_POST['company_pec']) );
            update_user_meta( $user_id, 'company_sdi', sanitize_text_field($_POST['company_sdi']) );
            update_user_meta( $user_id, 'company_address', sanitize_text_field($_POST['company_address']) );
            update_user_meta( $user_id, 'company_city', sanitize_text_field($_POST['company_city']) );
            update_user_meta( $user_id, 'company_postcode', sanitize_text_field($_POST['company_postcode']) );
            update_user_meta( $user_id, 'company_state', sanitize_text_field($_POST['company_state']) );

        } else {
            update_user_meta( $user_id, '_woosolid_user_type', 'persona_fisica' );
        }
    }
}

WooSolid_Registration::init();

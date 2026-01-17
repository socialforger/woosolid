<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Wizard {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_wizard_page' ] );
        add_action( 'admin_post_woosolid_run_wizard', [ $this, 'run_wizard' ] );
    }

    /**
     * Aggiunge la pagina del wizard nel backend
     */
    public function add_wizard_page() {
        add_menu_page(
            'WooSolid Wizard',
            'WooSolid Wizard',
            'manage_options',
            'woosolid-wizard',
            [ $this, 'wizard_page_html' ],
            'dashicons-admin-tools',
            56
        );
    }

    /**
     * HTML della pagina wizard
     */
    public function wizard_page_html() {
        ?>
        <div class="wrap">
            <h1>WooSolid – Wizard di inizializzazione</h1>
            <p>Questo wizard crea automaticamente l’ente gestore, le pagine necessarie e i dati demo.</p>

            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="woosolid_run_wizard">

                <h2>Dati Ente Gestore</h2>

                <table class="form-table">
                    <tr>
                        <th><label for="company_name">Ragione Sociale</label></th>
                        <td><input type="text" name="company_name" id="company_name" class="regular-text" required></td>
                    </tr>

                    <tr>
                        <th><label for="company_piva">Partita IVA</label></th>
                        <td><input type="text" name="company_piva" id="company_piva" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="company_cf">Codice Fiscale Ente</label></th>
                        <td><input type="text" name="company_cf" id="company_cf" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="company_pec">PEC</label></th>
                        <td><input type="email" name="company_pec" id="company_pec" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="company_sdi">SDI</label></th>
                        <td><input type="text" name="company_sdi" id="company_sdi" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="company_address">Indirizzo sede legale</label></th>
                        <td><input type="text" name="company_address" id="company_address" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="company_city">Città</label></th>
                        <td><input type="text" name="company_city" id="company_city" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="company_postcode">CAP</label></th>
                        <td><input type="text" name="company_postcode" id="company_postcode" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="company_state">Provincia</label></th>
                        <td><input type="text" name="company_state" id="company_state" class="regular-text"></td>
                    </tr>
                </table>

                <h2>Dati Rappresentante Legale</h2>

                <table class="form-table">
                    <tr>
                        <th><label for="first_name">Nome</label></th>
                        <td><input type="text" name="first_name" id="first_name" class="regular-text" required></td>
                    </tr>

                    <tr>
                        <th><label for="last_name">Cognome</label></th>
                        <td><input type="text" name="last_name" id="last_name" class="regular-text" required></td>
                    </tr>

                    <tr>
                        <th><label for="codice_fiscale">Codice Fiscale</label></th>
                        <td><input type="text" name="codice_fiscale" id="codice_fiscale" class="regular-text" required></td>
                    </tr>

                    <tr>
                        <th><label for="email">Email</label></th>
                        <td><input type="email" name="email" id="email" class="regular-text" required></td>
                    </tr>

                    <tr>
                        <th><label for="phone">Telefono</label></th>
                        <td><input type="text" name="phone" id="phone" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="pf_address">Indirizzo personale</label></th>
                        <td><input type="text" name="pf_address" id="pf_address" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="pf_city">Città</label></th>
                        <td><input type="text" name="pf_city" id="pf_city" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="pf_postcode">CAP</label></th>
                        <td><input type="text" name="pf_postcode" id="pf_postcode" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="pf_state">Provincia</label></th>
                        <td><input type="text" name="pf_state" id="pf_state" class="regular-text"></td>
                    </tr>
                </table>

                <p><button type="submit" class="button button-primary">Esegui Wizard</button></p>
            </form>
        </div>
        <?php
    }

    /**
     * Esecuzione del wizard
     */
    public function run_wizard() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Non autorizzato.' );
        }

        // Crea l'utente rappresentante legale
        $email = sanitize_email( $_POST['email'] );
        $password = wp_generate_password( 12, true );

        $user_id = wp_create_user(
            $email,
            $password,
            $email
        );

        if ( is_wp_error( $user_id ) ) {
            wp_die( 'Errore nella creazione dell’utente rappresentante.' );
        }

        // Prepara i dati PF + PG
        $data = [
            // PF
            'first_name'     => $_POST['first_name'],
            'last_name'      => $_POST['last_name'],
            'codice_fiscale' => $_POST['codice_fiscale'],
            'phone'          => $_POST['phone'] ?? '',
            'pf_address'     => $_POST['pf_address'] ?? '',
            'pf_city'        => $_POST['pf_city'] ?? '',
            'pf_postcode'    => $_POST['pf_postcode'] ?? '',
            'pf_state'       => $_POST['pf_state'] ?? '',
            'pf_country'     => 'IT',

            // PG
            'company_name'     => $_POST['company_name'],
            'company_type'     => $_POST['company_type'] ?? '',
            'company_piva'     => $_POST['company_piva'] ?? '',
            'company_cf'       => $_POST['company_cf'] ?? '',
            'company_pec'      => $_POST['company_pec'] ?? '',
            'company_sdi'      => $_POST['company_sdi'] ?? '',
            'company_address'  => $_POST['company_address'] ?? '',
            'company_city'     => $_POST['company_city'] ?? '',
            'company_postcode' => $_POST['company_postcode'] ?? '',
            'company_state'    => $_POST['company_state'] ?? '',
            'company_country'  => 'IT',

            // Flag
            'user_type'             => 'persona_giuridica',
            'is_org_representative' => 'yes',
            'is_ente_gestore'       => 'yes',
        ];

        // Salva tutto tramite UserMeta
        WooSolid_User_Meta::save( $user_id, $data );

        // Imposta ruolo customer
        $user = new WP_User( $user_id );
        $user->set_role( 'customer' );

        // Redirect
        wp_redirect( admin_url( 'admin.php?page=woosolid-wizard&success=1' ) );
        exit;
    }
}

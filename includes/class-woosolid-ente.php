<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Ente {

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function register_settings() {

        $group = 'woosolid_ente_settings';
        $page  = 'woosolid-ente';

        $options = [
            // 1. Identità
            'woosolid_ente_denominazione',
            'woosolid_ente_cf',
            'woosolid_ente_piva',
            'woosolid_ente_tipologia',
            'woosolid_ente_rappresentante',
            'woosolid_ente_email',
            'woosolid_ente_pec',
            'woosolid_ente_cuu',
            'woosolid_ente_telefono',
            'woosolid_ente_sito',
            // 2. Sede legale
            'woosolid_ente_indirizzo',
            'woosolid_ente_cap',
            'woosolid_ente_citta',
            'woosolid_ente_provincia',
            'woosolid_ente_nazione',
            // 3. Logo e immagine
            'woosolid_ente_logo_id',
            'woosolid_ente_firma_id',
            'woosolid_ente_colore_primario',
            // 4. Ricevute
            'woosolid_ente_prefisso_ricevute',
            'woosolid_ente_numero_progressivo',
            'woosolid_ente_testo_legale',
            'woosolid_ente_testo_privacy',
            'woosolid_ente_testo_ringraziamento',
            // 5. Comunicazioni
            'woosolid_ente_email_mittente',
            'woosolid_ente_nome_mittente',
            'woosolid_ente_email_risposta',
            'woosolid_ente_msg_conf_donazione',
            'woosolid_ente_msg_conf_ordine',
            'woosolid_ente_msg_riepilogo_fiscale',
            // 6. Operative
            'woosolid_ente_modalita',
            'woosolid_ente_donazioni_anonime',
            'woosolid_ente_importo_min_donazione',
            'woosolid_ente_importo_max_donazione',
            'woosolid_ente_ricevuta_automatica',
            'woosolid_ente_riepilogo_annuale',
        ];

        foreach ( $options as $opt ) {
            register_setting( $group, $opt, [ 'sanitize_callback' => [ __CLASS__, 'sanitize_field' ] ] );
        }

        // Sezioni
        add_settings_section( 'woosolid_ente_identita', __( 'Identità dell’Ente', 'woosolid' ), '__return_false', $page );
        add_settings_section( 'woosolid_ente_sede', __( 'Sede legale', 'woosolid' ), '__return_false', $page );
        add_settings_section( 'woosolid_ente_logo', __( 'Logo e immagine coordinata', 'woosolid' ), '__return_false', $page );
        add_settings_section( 'woosolid_ente_ricevute', __( 'Impostazioni ricevute', 'woosolid' ), '__return_false', $page );
        add_settings_section( 'woosolid_ente_comunicazioni', __( 'Impostazioni comunicazioni', 'woosolid' ), '__return_false', $page );
        add_settings_section( 'woosolid_ente_operative', __( 'Impostazioni operative Ente', 'woosolid' ), '__return_false', $page );

        // 1. Identità
        self::add_text_field( 'woosolid_ente_denominazione', __( 'Denominazione Ente', 'woosolid' ), 'woosolid_ente_identita', $page );
        self::add_text_field( 'woosolid_ente_cf', __( 'Codice Fiscale Ente', 'woosolid' ), 'woosolid_ente_identita', $page );
        self::add_text_field( 'woosolid_ente_piva', __( 'Partita IVA Ente', 'woosolid' ), 'woosolid_ente_identita', $page );
        add_settings_field(
            'woosolid_ente_tipologia',
            __( 'Tipologia Ente', 'woosolid' ),
            [ __CLASS__, 'field_tipologia' ],
            $page,
            'woosolid_ente_identita'
        );
        self::add_text_field( 'woosolid_ente_rappresentante', __( 'Rappresentante legale', 'woosolid' ), 'woosolid_ente_identita', $page );
        self::add_email_field( 'woosolid_ente_email', __( 'Email Ente', 'woosolid' ), 'woosolid_ente_identita', $page );
        self::add_email_field( 'woosolid_ente_pec', __( 'PEC Ente', 'woosolid' ), 'woosolid_ente_identita', $page );
        self::add_text_field( 'woosolid_ente_cuu', __( 'CUU Ente', 'woosolid' ), 'woosolid_ente_identita', $page );
        self::add_text_field( 'woosolid_ente_telefono', __( 'Telefono', 'woosolid' ), 'woosolid_ente_identita', $page );
        self::add_text_field( 'woosolid_ente_sito', __( 'Sito web', 'woosolid' ), 'woosolid_ente_identita', $page );

        // 2. Sede legale
        self::add_text_field( 'woosolid_ente_indirizzo', __( 'Indirizzo', 'woosolid' ), 'woosolid_ente_sede', $page );
        self::add_text_field( 'woosolid_ente_cap', __( 'CAP', 'woosolid' ), 'woosolid_ente_sede', $page );
        self::add_text_field( 'woosolid_ente_citta', __( 'Città', 'woosolid' ), 'woosolid_ente_sede', $page );
        self::add_text_field( 'woosolid_ente_provincia', __( 'Provincia', 'woosolid' ), 'woosolid_ente_sede', $page );
        self::add_text_field( 'woosolid_ente_nazione', __( 'Nazione', 'woosolid' ), 'woosolid_ente_sede', $page );

        // 3. Logo e immagine
        self::add_text_field( 'woosolid_ente_logo_id', __( 'Logo Ente (ID media)', 'woosolid' ), 'woosolid_ente_logo', $page );
        self::add_text_field( 'woosolid_ente_firma_id', __( 'Firma digitale (ID media)', 'woosolid' ), 'woosolid_ente_logo', $page );
        self::add_text_field( 'woosolid_ente_colore_primario', __( 'Colore primario (es. #008000)', 'woosolid' ), 'woosolid_ente_logo', $page );

        // 4. Ricevute
        self::add_text_field( 'woosolid_ente_prefisso_ricevute', __( 'Prefisso ricevute', 'woosolid' ), 'woosolid_ente_ricevute', $page );
        self::add_number_field( 'woosolid_ente_numero_progressivo', __( 'Numero progressivo (auto-increment)', 'woosolid' ), 'woosolid_ente_ricevute', $page );
        self::add_textarea_field( 'woosolid_ente_testo_legale', __( 'Testo legale in calce', 'woosolid' ), 'woosolid_ente_ricevute', $page );
        self::add_textarea_field( 'woosolid_ente_testo_privacy', __( 'Testo privacy', 'woosolid' ), 'woosolid_ente_ricevute', $page );
        self::add_textarea_field( 'woosolid_ente_testo_ringraziamento', __( 'Testo ringraziamento', 'woosolid' ), 'woosolid_ente_ricevute', $page );

        // 5. Comunicazioni
        self::add_email_field( 'woosolid_ente_email_mittente', __( 'Email mittente', 'woosolid' ), 'woosolid_ente_comunicazioni', $page );
        self::add_text_field( 'woosolid_ente_nome_mittente', __( 'Nome mittente', 'woosolid' ), 'woosolid_ente_comunicazioni', $page );
        self::add_email_field( 'woosolid_ente_email_risposta', __( 'Email di risposta', 'woosolid' ), 'woosolid_ente_comunicazioni', $page );
        self::add_textarea_field( 'woosolid_ente_msg_conf_donazione', __( 'Messaggio conferma donazione', 'woosolid' ), 'woosolid_ente_comunicazioni', $page );
        self::add_textarea_field( 'woosolid_ente_msg_conf_ordine', __( 'Messaggio conferma ordine solidale', 'woosolid' ), 'woosolid_ente_comunicazioni', $page );
        self::add_textarea_field( 'woosolid_ente_msg_riepilogo_fiscale', __( 'Messaggio riepilogo fiscale mensile', 'woosolid' ), 'woosolid_ente_comunicazioni', $page );

        // 6. Operative
        add_settings_field(
            'woosolid_ente_modalita',
            __( 'Modalità Ente', 'woosolid' ),
            [ __CLASS__, 'field_modalita' ],
            $page,
            'woosolid_ente_operative'
        );
        self::add_checkbox_field( 'woosolid_ente_donazioni_anonime', __( 'Abilita donazioni anonime', 'woosolid' ), 'woosolid_ente_operative', $page );
        self::add_number_field( 'woosolid_ente_importo_min_donazione', __( 'Importo minimo donazione', 'woosolid' ), 'woosolid_ente_operative', $page );
        self::add_number_field( 'woosolid_ente_importo_max_donazione', __( 'Importo massimo donazione', 'woosolid' ), 'woosolid_ente_operative', $page );
        self::add_checkbox_field( 'woosolid_ente_ricevuta_automatica', __( 'Abilita ricevuta automatica', 'woosolid' ), 'woosolid_ente_operative', $page );
        self::add_checkbox_field( 'woosolid_ente_riepilogo_annuale', __( 'Abilita riepilogo annuale', 'woosolid' ), 'woosolid_ente_operative', $page );
    }

    public static function sanitize_field( $value ) {
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }
        return is_string( $value ) ? sanitize_text_field( $value ) : $value;
    }

    protected static function add_text_field( $option, $label, $section, $page ) {
        add_settings_field(
            $option,
            $label,
            [ __CLASS__, 'field_text' ],
            $page,
            $section,
            [ 'option' => $option ]
        );
    }

    protected static function add_email_field( $option, $label, $section, $page ) {
        add_settings_field(
            $option,
            $label,
            [ __CLASS__, 'field_email' ],
            $page,
            $section,
            [ 'option' => $option ]
        );
    }

    protected static function add_number_field( $option, $label, $section, $page ) {
        add_settings_field(
            $option,
            $label,
            [ __CLASS__, 'field_number' ],
            $page,
            $section,
            [ 'option' => $option ]
        );
    }

    protected static function add_textarea_field( $option, $label, $section, $page ) {
        add_settings_field(
            $option,
            $label,
            [ __CLASS__, 'field_textarea' ],
            $page,
            $section,
            [ 'option' => $option ]
        );
    }

    protected static function add_checkbox_field( $option, $label, $section, $page ) {
        add_settings_field(
            $option,
            $label,
            [ __CLASS__, 'field_checkbox' ],
            $page,
            $section,
            [ 'option' => $option ]
        );
    }

    public static function field_text( $args ) {
        $option = $args['option'];
        $value  = get_option( $option, '' );
        ?>
        <input type="text" name="<?php echo esc_attr( $option ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php
    }

    public static function field_email( $args ) {
        $option = $args['option'];
        $value  = get_option( $option, '' );
        ?>
        <input type="email" name="<?php echo esc_attr( $option ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php
    }

    public static function field_number( $args ) {
        $option = $args['option'];
        $value  = get_option( $option, '' );
        ?>
        <input type="number" name="<?php echo esc_attr( $option ); ?>" value="<?php echo esc_attr( $value ); ?>" class="small-text" />
        <?php
    }

    public static function field_textarea( $args ) {
        $option = $args['option'];
        $value  = get_option( $option, '' );
        ?>
        <textarea name="<?php echo esc_attr( $option ); ?>" rows="4" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
        <?php
    }

    public static function field_checkbox( $args ) {
        $option = $args['option'];
        $value  = get_option( $option, '' );
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( $option ); ?>" value="yes" <?php checked( $value, 'yes' ); ?> />
            <?php esc_html_e( 'Abilita', 'woosolid' ); ?>
        </label>
        <?php
    }

    public static function field_tipologia() {
        $option  = 'woosolid_ente_tipologia';
        $value   = get_option( $option, '' );
        $choices = [
            ''              => __( 'Seleziona…', 'woosolid' ),
            'aps'           => 'APS',
            'odv'           => 'ODV',
            'ets'           => 'ETS',
            'gas'           => 'GAS',
            'coop'          => __( 'Cooperativa', 'woosolid' ),
            'fondazione'    => __( 'Fondazione', 'woosolid' ),
            'associazione'  => __( 'Associazione', 'woosolid' ),
        ];
        ?>
        <select name="<?php echo esc_attr( $option ); ?>">
            <?php foreach ( $choices as $k => $label ) : ?>
                <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $value, $k ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public static function field_modalita() {
        $option  = 'woosolid_ente_modalita';
        $value   = get_option( $option, '' );
        $choices = [
            ''      => __( 'Seleziona…', 'woosolid' ),
            'aps'   => 'APS',
            'odv'   => 'ODV',
            'gas'   => 'GAS',
            'coop'  => __( 'Cooperativa', 'woosolid' ),
            'altro' => __( 'Altro', 'woosolid' ),
        ];
        ?>
        <select name="<?php echo esc_attr( $option ); ?>">
            <?php foreach ( $choices as $k => $label ) : ?>
                <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $value, $k ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Ente gestore', 'woosolid' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'woosolid_ente_settings' );
                do_settings_sections( 'woosolid-ente' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

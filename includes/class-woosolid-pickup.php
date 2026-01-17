<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Pickup {

    const CPT = 'woosolid_pickup';

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_cpt' ] );
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_metaboxes' ] );
        add_action( 'save_post_' . self::CPT, [ __CLASS__, 'save_meta' ], 10, 2 );
    }

    /**
     * CPT Punti di ritiro
     */
    public static function register_cpt() {

        $labels = [
            'name'               => 'Punti di ritiro',
            'singular_name'      => 'Punto di ritiro',
            'add_new'            => 'Aggiungi punto di ritiro',
            'add_new_item'       => 'Aggiungi nuovo punto di ritiro',
            'edit_item'          => 'Modifica punto di ritiro',
            'new_item'           => 'Nuovo punto di ritiro',
            'view_item'          => 'Visualizza punto di ritiro',
            'search_items'       => 'Cerca punti di ritiro',
            'not_found'          => 'Nessun punto di ritiro trovato',
            'not_found_in_trash' => 'Nessun punto di ritiro nel cestino',
            'menu_name'          => 'Punti di ritiro',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'woosolid-settings',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => [ 'title', 'editor' ], // niente featured image
            'has_archive'        => false,
            'menu_position'      => null,
        ];

        register_post_type( self::CPT, $args );
    }

    /**
     * Metabox
     */
    public static function add_metaboxes() {
        add_meta_box(
            'woosolid_pickup_details',
            'Dettagli punto di ritiro',
            [ __CLASS__, 'render_metabox' ],
            self::CPT,
            'normal',
            'high'
        );
    }

    public static function render_metabox( $post ) {

        wp_nonce_field( 'woosolid_pickup_save', 'woosolid_pickup_nonce' );

        $indirizzo = get_post_meta( $post->ID, '_woosolid_pickup_indirizzo', true );
        $citta     = get_post_meta( $post->ID, '_woosolid_pickup_citta', true );
        $provincia = get_post_meta( $post->ID, '_woosolid_pickup_provincia', true );
        $nazione   = get_post_meta( $post->ID, '_woosolid_pickup_nazione', true );
        $orari     = get_post_meta( $post->ID, '_woosolid_pickup_orari', true );
        $referente = get_post_meta( $post->ID, '_woosolid_pickup_referente', true );
        $telefono  = get_post_meta( $post->ID, '_woosolid_pickup_telefono', true );

        echo '<table class="form-table">';

        echo '<tr><th><label for="woosolid_pickup_indirizzo">Indirizzo</label></th><td>';
        echo '<input type="text" class="regular-text" id="woosolid_pickup_indirizzo" name="woosolid_pickup_indirizzo" value="' . esc_attr( $indirizzo ) . '">';
        echo '</td></tr>';

        echo '<tr><th><label for="woosolid_pickup_citta">Città</label></th><td>';
        echo '<input type="text" class="regular-text" id="woosolid_pickup_citta" name="woosolid_pickup_citta" value="' . esc_attr( $citta ) . '">';
        echo '</td></tr>';

        echo '<tr><th><label for="woosolid_pickup_provincia">Provincia</label></th><td>';
        echo '<input type="text" class="regular-text" id="woosolid_pickup_provincia" name="woosolid_pickup_provincia" value="' . esc_attr( $provincia ) . '">';
        echo '</td></tr>';

        echo '<tr><th><label for="woosolid_pickup_nazione">Nazione</label></th><td>';
        echo '<input type="text" class="regular-text" id="woosolid_pickup_nazione" name="woosolid_pickup_nazione" value="' . esc_attr( $nazione ) . '">';
        echo '</td></tr>';

        echo '<tr><th><label for="woosolid_pickup_orari">Orari</label></th><td>';
        echo '<textarea class="large-text" rows="3" id="woosolid_pickup_orari" name="woosolid_pickup_orari">' . esc_textarea( $orari ) . '</textarea>';
        echo '</td></tr>';

        echo '<tr><th><label for="woosolid_pickup_referente">Referente</label></th><td>';
        echo '<input type="text" class="regular-text" id="woosolid_pickup_referente" name="woosolid_pickup_referente" value="' . esc_attr( $referente ) . '">';
        echo '</td></tr>';

        echo '<tr><th><label for="woosolid_pickup_telefono">Telefono</label></th><td>';
        echo '<input type="text" class="regular-text" id="woosolid_pickup_telefono" name="woosolid_pickup_telefono" value="' . esc_attr( $telefono ) . '">';
        echo '</td></tr>';

        echo '</table>';
    }

    public static function save_meta( $post_id, $post ) {

        if ( ! isset( $_POST['woosolid_pickup_nonce'] ) || ! wp_verify_nonce( $_POST['woosolid_pickup_nonce'], 'woosolid_pickup_save' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = [
            'indirizzo' => '_woosolid_pickup_indirizzo',
            'citta'     => '_woosolid_pickup_citta',
            'provincia' => '_woosolid_pickup_provincia',
            'nazione'   => '_woosolid_pickup_nazione',
            'orari'     => '_woosolid_pickup_orari',
            'referente' => '_woosolid_pickup_referente',
            'telefono'  => '_woosolid_pickup_telefono',
        ];

        foreach ( $fields as $field => $meta_key ) {
            if ( isset( $_POST[ 'woosolid_pickup_' . $field ] ) ) {
                update_post_meta(
                    $post_id,
                    $meta_key,
                    sanitize_text_field( wp_unslash( $_POST[ 'woosolid_pickup_' . $field ] ) )
                );
            }
        }
    }
}

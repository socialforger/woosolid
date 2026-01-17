<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Pickup {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_post_type' ] );
        add_action( 'add_meta_boxes', [ __CLASS__, 'register_metaboxes' ] );
        add_action( 'save_post_woosolid_pickup', [ __CLASS__, 'save_metabox' ] );
    }

    public static function register_post_type() {

        $labels = [
            'name'               => __( 'Punti di ritiro', 'woosolid' ),
            'singular_name'      => __( 'Punto di ritiro', 'woosolid' ),
            'menu_name'          => __( 'Punti di ritiro', 'woosolid' ),
            'add_new'            => __( 'Aggiungi nuovo', 'woosolid' ),
            'add_new_item'       => __( 'Aggiungi punto di ritiro', 'woosolid' ),
            'edit_item'          => __( 'Modifica punto di ritiro', 'woosolid' ),
            'new_item'           => __( 'Nuovo punto di ritiro', 'woosolid' ),
            'view_item'          => __( 'Visualizza punto di ritiro', 'woosolid' ),
            'search_items'       => __( 'Cerca punti di ritiro', 'woosolid' ),
            'not_found'          => __( 'Nessun punto di ritiro trovato', 'woosolid' ),
            'not_found_in_trash' => __( 'Nessun punto di ritiro nel cestino', 'woosolid' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => false, // Gestito da Logistica
            'supports'           => [ 'title', 'editor' ],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'rewrite'            => false,
        ];

        register_post_type( 'woosolid_pickup', $args );
    }

    public static function register_metaboxes() {
        add_meta_box(
            'woosolid_pickup_details',
            __( 'Dettagli punto di ritiro', 'woosolid' ),
            [ __CLASS__, 'render_metabox' ],
            'woosolid_pickup',
            'normal',
            'default'
        );
    }

    public static function render_metabox( $post ) {

        wp_nonce_field( 'woosolid_pickup_save', 'woosolid_pickup_nonce' );

        $indirizzo = get_post_meta( $post->ID, '_woosolid_pickup_address', true );
        $orari     = get_post_meta( $post->ID, '_woosolid_pickup_hours', true );
        $contatto  = get_post_meta( $post->ID, '_woosolid_pickup_contact', true );
        ?>

        <p>
            <label><strong><?php esc_html_e( 'Indirizzo', 'woosolid' ); ?></strong></label><br>
            <input type="text" class="widefat" name="woosolid_pickup_address" value="<?php echo esc_attr( $indirizzo ); ?>">
        </p>

        <p>
            <label><strong><?php esc_html_e( 'Orari di ritiro', 'woosolid' ); ?></strong></label><br>
            <textarea class="widefat" name="woosolid_pickup_hours" rows="3"><?php echo esc_textarea( $orari ); ?></textarea>
        </p>

        <p>
            <label><strong><?php esc_html_e( 'Contatto', 'woosolid' ); ?></strong></label><br>
            <input type="text" class="widefat" name="woosolid_pickup_contact" value="<?php echo esc_attr( $contatto ); ?>">
        </p>

        <?php
    }

    public static function save_metabox( $post_id ) {

        if ( ! isset( $_POST['woosolid_pickup_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['woosolid_pickup_nonce'], 'woosolid_pickup_save' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        update_post_meta( $post_id, '_woosolid_pickup_address', sanitize_text_field( $_POST['woosolid_pickup_address'] ?? '' ) );
        update_post_meta( $post_id, '_woosolid_pickup_hours', sanitize_textarea_field( $_POST['woosolid_pickup_hours'] ?? '' ) );
        update_post_meta( $post_id, '_woosolid_pickup_contact', sanitize_text_field( $_POST['woosolid_pickup_contact'] ?? '' ) );
    }
}

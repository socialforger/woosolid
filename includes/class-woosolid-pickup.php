<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Pickup {

    public static function init() {
        add_filter( 'woocommerce_checkout_fields', [ __CLASS__, 'add_fields' ] );
        add_action( 'woocommerce_checkout_process', [ __CLASS__, 'validate' ] );
        add_action( 'woocommerce_checkout_update_order_meta', [ __CLASS__, 'save_meta' ] );
        add_action( 'woocommerce_admin_order_data_after_shipping_address', [ __CLASS__, 'admin_view' ] );
        add_action( 'wp_footer', [ __CLASS__, 'checkout_js' ] );
    }

    public static function add_fields( $fields ) {
        $settings = WooSolid_Settings::get_settings();
        if ( empty( $settings['enable_pickup_points'] ) ) return $fields;

        $fields['billing']['woosolid_pickup_point'] = [
            'type'     => 'text',
            'label'    => __( 'Punto di ritiro', 'woosolid' ),
            'required' => false,
            'class'    => [ 'form-row-wide', 'woosolid-pickup-field' ],
            'priority' => 120,
        ];

        $fields['billing']['woosolid_pickup_time'] = [
            'type'     => 'text',
            'label'    => __( 'Orario di ritiro', 'woosolid' ),
            'required' => false,
            'class'    => [ 'form-row-wide', 'woosolid-pickup-field' ],
            'priority' => 121,
        ];

        return $fields;
    }

    public static function validate() {
        if ( ! WC()->cart ) return;

        $chosen_method = WC()->session->get( 'chosen_shipping_methods' );
        $chosen_method = is_array( $chosen_method ) ? reset( $chosen_method ) : '';

        if ( strpos( $chosen_method, 'local_pickup' ) !== false ) {
            if ( empty( $_POST['woosolid_pickup_point'] ) ) {
                wc_add_notice( __( 'Seleziona un punto di ritiro.', 'woosolid' ), 'error' );
            }
            if ( empty( $_POST['woosolid_pickup_time'] ) ) {
                wc_add_notice( __( 'Seleziona un orario di ritiro.', 'woosolid' ), 'error' );
            }
        }
    }

    public static function save_meta( $order_id ) {
        if ( isset( $_POST['woosolid_pickup_point'] ) ) {
            update_post_meta( $order_id, '_woosolid_pickup_point', sanitize_text_field( $_POST['woosolid_pickup_point'] ) );
        }
        if ( isset( $_POST['woosolid_pickup_time'] ) ) {
            update_post_meta( $order_id, '_woosolid_pickup_time', sanitize_text_field( $_POST['woosolid_pickup_time'] ) );
        }
    }

    public static function admin_view( $order ) {
        $point = get_post_meta( $order->get_id(), '_woosolid_pickup_point', true );
        $time  = get_post_meta( $order->get_id(), '_woosolid_pickup_time', true );

        if ( ! $point && ! $time ) return;

        echo '<p><strong>' . esc_html__( 'Ritiro WooSolid', 'woosolid' ) . '</strong><br />';
        if ( $point ) {
            echo esc_html__( 'Punto di ritiro: ', 'woosolid' ) . esc_html( $point ) . '<br />';
        }
        if ( $time ) {
            echo esc_html__( 'Orario di ritiro: ', 'woosolid' ) . esc_html( $time ) . '<br />';
        }
        echo '</p>';
    }

    public static function checkout_js() {
        if ( ! is_checkout() ) return;
        ?>
        <script>
        (function($){
            function togglePickupFields() {
                var method = $('input[name^="shipping_method"]:checked').val() || '';
                var isPickup = method.indexOf('local_pickup') !== -1;
                $('.woosolid-pickup-field').closest('.form-row').toggle(isPickup);
            }
            $(document.body).on('change', 'input[name^="shipping_method"]', togglePickupFields);
            $(document).ready(togglePickupFields);
        })(jQuery);
        </script>
        <?php
    }
}

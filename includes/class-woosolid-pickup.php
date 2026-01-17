<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Pickup {

    public static function init() {
        add_filter( 'woocommerce_shipping_methods', [ __CLASS__, 'register_shipping_method' ] );
        add_action( 'woocommerce_after_order_notes', [ __CLASS__, 'add_pickup_fields' ] );
        add_action( 'woocommerce_checkout_update_order_meta', [ __CLASS__, 'save_pickup_fields' ] );
        add_action( 'woocommerce_admin_order_data_after_billing_address', [ __CLASS__, 'display_pickup_in_admin' ], 10, 1 );
    }

    public static function register_shipping_method( $methods ) {
        if ( WooSolid_Utils::get_option_bool( 'woosolid_enable_pickup', true ) ) {
            $methods['woosolid_pickup'] = 'WooSolid_Shipping_Pickup';
        }
        return $methods;
    }

}

if ( ! class_exists( 'WooSolid_Shipping_Pickup' ) ) {

    class WooSolid_Shipping_Pickup extends WC_Shipping_Method {

        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'woosolid_pickup';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __( 'Ritiro presso punto di ritiro', 'woosolid' );
            $this->method_description = __( 'Metodo di ritiro locale gestito da WooSolid.', 'woosolid' );
            $this->supports           = [
                'shipping-zones',
                'instance-settings',
            ];

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();

            $this->enabled = $this->get_option( 'enabled', 'yes' );
            $this->title   = $this->get_option( 'title', __( 'Ritiro presso punto di ritiro', 'woosolid' ) );

            add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
        }

        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __( 'Abilita', 'woosolid' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Abilita ritiro presso punto di ritiro', 'woosolid' ),
                    'default' => 'yes',
                ],
                'title'   => [
                    'title'       => __( 'Titolo', 'woosolid' ),
                    'type'        => 'text',
                    'description' => __( 'Titolo mostrato al checkout.', 'woosolid' ),
                    'default'     => __( 'Ritiro presso punto di ritiro', 'woosolid' ),
                ],
            ];
        }

        public function calculate_shipping( $package = [] ) {
            $rate = [
                'id'    => $this->id . ':' . $this->instance_id,
                'label' => $this->title,
                'cost'  => 0,
            ];

            $this->add_rate( $rate );
        }
    }
}

if ( ! class_exists( 'WooSolid_Pickup_Fields' ) ) {

    class WooSolid_Pickup_Fields {

        public static function add_pickup_fields( $checkout ) {
            if ( ! WooSolid_Utils::get_option_bool( 'woosolid_enable_pickup', true ) ) {
                return;
            }

            echo '<div id="woosolid_pickup_fields"><h3>' . esc_html__( 'Dettagli ritiro', 'woosolid' ) . '</h3>';

            woocommerce_form_field(
                'woosolid_pickup_location',
                [
                    'type'        => 'text',
                    'class'       => [ 'form-row-wide' ],
                    'label'       => __( 'Punto di ritiro', 'woosolid' ),
                    'placeholder' => __( 'Es. Emporio solidale, sede GAS…', 'woosolid' ),
                    'required'    => false,
                ],
                $checkout->get_value( 'woosolid_pickup_location' )
            );

            woocommerce_form_field(
                'woosolid_pickup_date',
                [
                    'type'        => 'date',
                    'class'       => [ 'form-row-first' ],
                    'label'       => __( 'Data ritiro', 'woosolid' ),
                    'required'    => false,
                ],
                $checkout->get_value( 'woosolid_pickup_date' )
            );

            woocommerce_form_field(
                'woosolid_pickup_time',
                [
                    'type'        => 'time',
                    'class'       => [ 'form-row-last' ],
                    'label'       => __( 'Orario ritiro', 'woosolid' ),
                    'required'    => false,
                ],
                $checkout->get_value( 'woosolid_pickup_time' )
            );

            echo '<div class="clear"></div></div>';
        }

        public static function save_pickup_fields( $order_id ) {
            if ( isset( $_POST['woosolid_pickup_location'] ) ) {
                update_post_meta( $order_id, '_woosolid_pickup_location', sanitize_text_field( $_POST['woosolid_pickup_location'] ) );
            }
            if ( isset( $_POST['woosolid_pickup_date'] ) ) {
                update_post_meta( $order_id, '_woosolid_pickup_date', sanitize_text_field( $_POST['woosolid_pickup_date'] ) );
            }
            if ( isset( $_POST['woosolid_pickup_time'] ) ) {
                update_post_meta( $order_id, '_woosolid_pickup_time', sanitize_text_field( $_POST['woosolid_pickup_time'] ) );
            }
        }

        public static function display_pickup_in_admin( $order ) {
            $location = $order->get_meta( '_woosolid_pickup_location' );
            $date     = $order->get_meta( '_woosolid_pickup_date' );
            $time     = $order->get_meta( '_woosolid_pickup_time' );

            if ( ! $location && ! $date && ! $time ) {
                return;
            }

            echo '<p><strong>' . esc_html__( 'Dettagli ritiro (WooSolid)', 'woosolid' ) . '</strong><br/>';

            if ( $location ) {
                echo esc_html__( 'Punto di ritiro: ', 'woosolid' ) . esc_html( $location ) . '<br/>';
            }
            if ( $date ) {
                echo esc_html__( 'Data: ', 'woosolid' ) . esc_html( $date ) . '<br/>';
            }
            if ( $time ) {
                echo esc_html__( 'Orario: ', 'woosolid' ) . esc_html( $time ) . '<br/>';
            }

            echo '</p>';
        }
    }

    add_action( 'woocommerce_after_order_notes', [ 'WooSolid_Pickup_Fields', 'add_pickup_fields' ] );
    add_action( 'woocommerce_checkout_update_order_meta', [ 'WooSolid_Pickup_Fields', 'save_pickup_fields' ] );
    add_action( 'woocommerce_admin_order_data_after_billing_address', [ 'WooSolid_Pickup_Fields', 'display_pickup_in_admin' ], 10, 1 );
}

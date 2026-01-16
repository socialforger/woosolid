<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_WooCommerce {

    public static function init() {
        add_filter( 'woocommerce_checkout_fields', [ __CLASS__, 'add_custom_checkout_fields' ] );
        add_action( 'woocommerce_checkout_process', [ __CLASS__, 'validate_custom_checkout_fields' ] );
        add_action( 'woocommerce_checkout_update_order_meta', [ __CLASS__, 'save_custom_checkout_fields' ] );
        add_action( 'woocommerce_admin_order_data_after_billing_address', [ __CLASS__, 'admin_show_custom_fields' ] );
        add_action( 'wp_footer', [ __CLASS__, 'checkout_dynamic_js' ] );
    }

    public static function add_custom_checkout_fields( $fields ) {

        $fields['billing']['woosolid_buyer_role'] = [
            'type'        => 'select',
            'label'       => __( 'In quale veste stai effettuando l’acquisto?', 'woosolid' ),
            'required'    => true,
            'options'     => [
                ''           => __( 'Seleziona…', 'woosolid' ),
                'individual' => __( 'Persona fisica (acquisto individuale)', 'woosolid' ),
                'legal_rep'  => __( 'Rappresentante legale di associazione / ente', 'woosolid' ),
                'gas'        => __( 'Referente GAS / Gruppo di acquisto', 'woosolid' ),
                'company'    => __( 'Azienda / Cooperativa', 'woosolid' ),
                'other'      => __( 'Altro (specificare)', 'woosolid' ),
            ],
            'priority' => 3,
            'class'    => ['form-row-wide'],
        ];

        $fields['billing']['woosolid_group_name'] = [
            'type'        => 'text',
            'label'       => __( 'Nome Gruppo o Associazione', 'woosolid' ),
            'required'    => false,
            'priority'    => 4,
            'class'       => ['form-row-wide'],
        ];

        $fields['billing']['woosolid_legal_representative'] = [
            'type'        => 'text',
            'label'       => __( 'Nome e cognome rappresentante legale', 'woosolid' ),
            'required'    => false,
            'priority'    => 5,
            'class'       => ['form-row-wide'],
        ];

        $fields['shipping']['woosolid_shipping_phone'] = [
            'type'        => 'text',
            'label'       => __( 'Telefono per il corriere (DDT)', 'woosolid' ),
            'required'    => true,
            'priority'    => 25,
            'class'       => ['form-row-wide'],
        ];

        return $fields;
    }

    public static function validate_custom_checkout_fields() {

        $role  = isset( $_POST['woosolid_buyer_role'] ) ? sanitize_text_field( $_POST['woosolid_buyer_role'] ) : '';
        $legal = isset( $_POST['woosolid_legal_representative'] ) ? trim( sanitize_text_field( $_POST['woosolid_legal_representative'] ) ) : '';

        if ( in_array( $role, [ 'legal_rep', 'company', 'gas' ], true ) ) {
            if ( empty( $legal ) ) {
                wc_add_notice(
                    __( 'Inserisci il nome e cognome del rappresentante legale.', 'woosolid' ),
                    'error'
                );
            }
        }

        if ( empty( $_POST['woosolid_shipping_phone'] ) ) {
            wc_add_notice(
                __( 'Inserisci il telefono per il corriere (DDT).', 'woosolid' ),
                'error'
            );
        }
    }

    public static function save_custom_checkout_fields( $order_id ) {

        $map = [
            'woosolid_buyer_role'           => '_woosolid_buyer_role',
            'woosolid_group_name'           => '_woosolid_group_name',
            'woosolid_legal_representative' => '_woosolid_legal_representative',
            'woosolid_shipping_phone'       => '_woosolid_shipping_phone',
        ];

        foreach ( $map as $post_key => $meta_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                update_post_meta(
                    $order_id,
                    $meta_key,
                    sanitize_text_field( $_POST[ $post_key ] )
                );
            }
        }
    }

    public static function admin_show_custom_fields( $order ) {

        $role   = $order->get_meta( '_woosolid_buyer_role' );
        $group  = $order->get_meta( '_woosolid_group_name' );
        $legal  = $order->get_meta( '_woosolid_legal_representative' );
        $phone  = $order->get_meta( '_woosolid_shipping_phone' );

        echo '<div class="woosolid-meta">';
        if ( $role ) {
            echo '<p><strong>' . __( 'Ruolo acquirente:', 'woosolid' ) . '</strong> ' . esc_html( $role ) . '</p>';
        }
        if ( $group ) {
            echo '<p><strong>' . __( 'Nome gruppo/associazione:', 'woosolid' ) . '</strong> ' . esc_html( $group ) . '</p>';
        }
        if ( $legal ) {
            echo '<p><strong>' . __( 'Rappresentante legale:', 'woosolid' ) . '</strong> ' . esc_html( $legal ) . '</p>';
        }
        if ( $phone ) {
            echo '<p><strong>' . __( 'Telefono DDT:', 'woosolid' ) . '</strong> ' . esc_html( $phone ) . '</p>';
        }
        echo '</div>';
    }

    public static function checkout_dynamic_js() {
        if ( ! is_checkout() ) return;
        ?>
        <script>
        jQuery(function($){

            function toggleLegalRepresentative(){
                const role = $('#woosolid_buyer_role').val();
                const field = $('#woosolid_legal_representative_field');
                const groupField = $('#woosolid_group_name_field');

                if (role === 'legal_rep' || role === 'company' || role === 'gas') {
                    field.show();
                    groupField.show();
                    $('#woosolid_legal_representative').prop('required', true);
                } else {
                    field.hide();
                    groupField.hide();
                    $('#woosolid_legal_representative').prop('required', false);
                }
            }

            toggleLegalRepresentative();
            $(document).on('change', '#woosolid_buyer_role', toggleLegalRepresentative);

        });
        </script>
        <?php
    }
}

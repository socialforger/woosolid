<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Checkout {

    public static function init() {
        add_filter( 'woocommerce_checkout_fields', [ __CLASS__, 'add_donor_fields' ] );
        add_action( 'woocommerce_checkout_update_order_meta', [ __CLASS__, 'save_donor_fields' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
    }

    public static function add_donor_fields( $fields ) {
        $fields['billing']['woosolid_cf'] = [
            'type'        => 'text',
            'label'       => __( 'Codice fiscale (persona fisica)', 'woosolid' ),
            'required'    => true,
            'priority'    => 51,
            'class'       => [ 'form-row-wide' ],
        ];

        $fields['billing']['woosolid_is_legal_rep'] = [
            'type'        => 'select',
            'label'       => __( 'Sei il rappresentante legale di una organizzazione?', 'woosolid' ),
            'required'    => true,
            'options'     => [
                ''    => __( 'Seleziona…', 'woosolid' ),
                'no'  => __( 'No', 'woosolid' ),
                'yes' => __( 'Sì', 'woosolid' ),
            ],
            'priority'    => 52,
            'class'       => [ 'form-row-wide' ],
        ];

        $fields['billing']['woosolid_legal_name'] = [
            'type'        => 'text',
            'label'       => __( 'Ragione sociale (organizzazione)', 'woosolid' ),
            'required'    => false,
            'priority'    => 53,
            'class'       => [ 'form-row-wide', 'woosolid-legal-fields' ],
        ];

        $fields['billing']['woosolid_legal_cf'] = [
            'type'        => 'text',
            'label'       => __( 'Codice fiscale / P.IVA (organizzazione)', 'woosolid' ),
            'required'    => false,
            'priority'    => 54,
            'class'       => [ 'form-row-wide', 'woosolid-legal-fields' ],
        ];

        $fields['billing']['woosolid_legal_pec'] = [
            'type'        => 'email',
            'label'       => __( 'PEC (organizzazione, opzionale)', 'woosolid' ),
            'required'    => false,
            'priority'    => 55,
            'class'       => [ 'form-row-wide', 'woosolid-legal-fields' ],
        ];

        $fields['billing']['woosolid_legal_sdi'] = [
            'type'        => 'text',
            'label'       => __( 'Codice SDI (organizzazione, opzionale)', 'woosolid' ),
            'required'    => false,
            'priority'    => 56,
            'class'       => [ 'form-row-wide', 'woosolid-legal-fields' ],
        ];

        $fields['billing']['woosolid_legal_ref_name'] = [
            'type'        => 'text',
            'label'       => __( 'Nome rappresentante legale', 'woosolid' ),
            'required'    => false,
            'priority'    => 57,
            'class'       => [ 'form-row-first', 'woosolid-legal-fields' ],
        ];

        $fields['billing']['woosolid_legal_ref_surname'] = [
            'type'        => 'text',
            'label'       => __( 'Cognome rappresentante legale', 'woosolid' ),
            'required'    => false,
            'priority'    => 58,
            'class'       => [ 'form-row-last', 'woosolid-legal-fields' ],
        ];

        return $fields;
    }

    public static function enqueue_scripts() {
        if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
            return;
        }

        wp_add_inline_script(
            'wc-checkout',
            "
            jQuery(function($){
                function toggleLegalFields() {
                    var val = $('#woosolid_is_legal_rep').val();
                    if (val === 'yes') {
                        $('.woosolid-legal-fields').closest('.form-row').show();

                        var firstName = $('#billing_first_name').val();
                        var lastName  = $('#billing_last_name').val();

                        if (!$('#woosolid_legal_ref_name').val()) {
                            $('#woosolid_legal_ref_name').val(firstName);
                        }
                        if (!$('#woosolid_legal_ref_surname').val()) {
                            $('#woosolid_legal_ref_surname').val(lastName);
                        }
                    } else {
                        $('.woosolid-legal-fields').closest('.form-row').hide();
                    }
                }

                toggleLegalFields();

                $(document.body).on('change', '#woosolid_is_legal_rep', toggleLegalFields);
            });
            "
        );
    }

    public static function save_donor_fields( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $cf             = isset( $_POST['woosolid_cf'] ) ? sanitize_text_field( $_POST['woosolid_cf'] ) : '';
        $is_legal_rep   = isset( $_POST['woosolid_is_legal_rep'] ) ? sanitize_text_field( $_POST['woosolid_is_legal_rep'] ) : 'no';
        $legal_name     = isset( $_POST['woosolid_legal_name'] ) ? sanitize_text_field( $_POST['woosolid_legal_name'] ) : '';
        $legal_cf       = isset( $_POST['woosolid_legal_cf'] ) ? sanitize_text_field( $_POST['woosolid_legal_cf'] ) : '';
        $legal_pec      = isset( $_POST['woosolid_legal_pec'] ) ? sanitize_email( $_POST['woosolid_legal_pec'] ) : '';
        $legal_sdi      = isset( $_POST['woosolid_legal_sdi'] ) ? sanitize_text_field( $_POST['woosolid_legal_sdi'] ) : '';
        $legal_ref_name = isset( $_POST['woosolid_legal_ref_name'] ) ? sanitize_text_field( $_POST['woosolid_legal_ref_name'] ) : '';
        $legal_ref_surn = isset( $_POST['woosolid_legal_ref_surname'] ) ? sanitize_text_field( $_POST['woosolid_legal_ref_surname'] ) : '';

        $order->update_meta_data( '_woosolid_cf', $cf );
        $order->update_meta_data( '_woosolid_is_legal_rep', $is_legal_rep );

        if ( 'yes' === $is_legal_rep ) {
            $order->update_meta_data( '_woosolid_donor_type', 'legal' );
            $order->update_meta_data( '_woosolid_legal_name', $legal_name );
            $order->update_meta_data( '_woosolid_legal_cf', $legal_cf );
            $order->update_meta_data( '_woosolid_legal_pec', $legal_pec );
            $order->update_meta_data( '_woosolid_legal_sdi', $legal_sdi );
            $order->update_meta_data( '_woosolid_legal_ref_name', $legal_ref_name );
            $order->update_meta_data( '_woosolid_legal_ref_surname', $legal_ref_surn );
        } else {
            $order->update_meta_data( '_woosolid_donor_type', 'physical' );
        }
    }
}

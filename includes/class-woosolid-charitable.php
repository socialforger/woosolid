<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Charitable {

    public static function init() {
        add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'create_donations_from_order' ], 20, 1 );
    }

    public static function create_donations_from_order( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $donations = $order->get_meta( '_woosolid_donations', true );
        if ( empty( $donations ) || ! is_array( $donations ) ) {
            return;
        }

        $user_id      = $order->get_user_id();
        $order_number = $order->get_order_number();
        $order_date   = $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd/m/Y' ) : '';

        $causale_fee = sprintf(
            'ordine n. %s del %s',
            $order_number,
            $order_date
        );

        if ( ! empty( $donations['fee'] ) && $user_id ) {
            foreach ( $donations['fee'] as $fee ) {
                self::create_charitable_donation(
                    $fee['campaign_id'],
                    $fee['amount'],
                    $user_id,
                    $causale_fee,
                    false
                );
            }
        }
    }

    public static function create_charitable_donation( $campaign_id, $amount, $user_id, $note, $anonymous = false ) {
        if ( ! $campaign_id || $amount <= 0 ) {
            return false;
        }

        $donation_post = [
            'post_type'   => 'donation',
            'post_status' => 'publish',
            'post_title'  => sprintf( __( 'Donazione per %s', 'woosolid' ), get_the_title( $campaign_id ) ),
        ];

        $donation_id = wp_insert_post( $donation_post );
        if ( ! $donation_id ) {
            return false;
        }

        update_post_meta( $donation_id, '_campaign_id', $campaign_id );
        update_post_meta( $donation_id, '_donation_amount', $amount );
        update_post_meta( $donation_id, '_donation_note', $note );

        if ( ! $anonymous && $user_id ) {
            update_post_meta( $donation_id, '_donor_id', $user_id );
        }

        return $donation_id;
    }

    public static function get_user_donations_for_year( $user_id, $year ) {
        if ( ! $user_id || ! $year ) {
            return [];
        }

        $args = [
            'post_type'      => 'donation',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'   => '_donor_id',
                    'value' => $user_id,
                ],
            ],
            'date_query'     => [
                [
                    'year' => (int) $year,
                ],
            ],
            'orderby'        => 'date',
            'order'          => 'ASC',
        ];

        $query      = new WP_Query( $args );
        $donations  = [];

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $donation_id   = $post->ID;
                $campaign_id   = get_post_meta( $donation_id, '_campaign_id', true );
                $amount        = get_post_meta( $donation_id, '_donation_amount', true );
                $note          = get_post_meta( $donation_id, '_donation_note', true );
                $date          = get_post_time( 'Y-m-d', false, $donation_id );
                $campaign_name = $campaign_id ? get_the_title( $campaign_id ) : '';

                $donations[] = [
                    'id'            => $donation_id,
                    'date'          => $date,
                    'amount'        => (float) $amount,
                    'campaign_id'   => $campaign_id,
                    'campaign_name' => $campaign_name,
                    'note'          => $note,
                ];
            }
        }

        wp_reset_postdata();

        return $donations;
    }

    public static function send_donation_summary_email( $user_id, $year ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return false;
        }

        $donations = self::get_user_donations_for_year( $user_id, $year );
        return WooSolid_Emails::send_donation_summary_email( $user, $donations, $year );
    }
}

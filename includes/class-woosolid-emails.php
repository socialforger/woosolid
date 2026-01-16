<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Emails {

    public static function init() {
        add_action( 'woocommerce_order_status_processing', [ __CLASS__, 'send_initial_order' ], 20, 1 );
        add_action( 'woocommerce_update_order', [ __CLASS__, 'send_rectification' ], 20, 1 );
    }

    public static function send_initial_order( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        $settings = WooSolid_Settings::get_settings();
        $to       = $settings['woosolid_ets_email'];
        if ( ! $to ) return;

        $subject = sprintf(
            __( 'Ordine %s del %s', 'woosolid' ),
            $order->get_order_number(),
            $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd/m/Y' ) : ''
        );

        $body = self::build_order_email_body( $order );

        wp_mail( $to, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
    }

    public static function send_rectification( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        $settings = WooSolid_Settings::get_settings();
        $to       = $settings['woosolid_ets_email'];
        if ( ! $to ) return;

        $original_date = $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd/m/Y' ) : '';

        $subject = sprintf(
            __( 'rettifica ordine %s del %s – sostituisce il precedente invio', 'woosolid' ),
            $order->get_order_number(),
            $original_date
        );

        $body = self::build_order_email_body( $order );

        wp_mail( $to, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
    }

    private static function build_order_email_body( $order ) {
        ob_start();
        ?>
        <h2><?php printf( esc_html__( 'Ordine %s', 'woosolid' ), esc_html( $order->get_order_number() ) ); ?></h2>
        <p>
            <?php esc_html_e( 'Data:', 'woosolid' ); ?>
            <?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd/m/Y H:i' ) : '' ); ?>
        </p>
        <h3><?php esc_html_e( 'Dettaglio prodotti', 'woosolid' ); ?></h3>
        <table cellspacing="0" cellpadding="6" border="1" style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Prodotto', 'woosolid' ); ?></th>
                    <th><?php esc_html_e( 'Quantità', 'woosolid' ); ?></th>
                    <th><?php esc_html_e( 'Totale', 'woosolid' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $order->get_items() as $item ) : ?>
                <tr>
                    <td><?php echo esc_html( $item->get_name() ); ?></td>
                    <td><?php echo esc_html( $item->get_quantity() ); ?></td>
                    <td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <?php esc_html_e( 'Totale ordine:', 'woosolid' ); ?>
            <?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
        </p>
        <?php
        $fee = (float) $order->get_meta( '_woosolid_fee_solidale' );
        if ( $fee > 0 ) : ?>
            <p>
                <?php esc_html_e( 'Fee solidale:', 'woosolid' ); ?>
                <?php echo esc_html( wc_price( $fee ) ); ?>
            </p>
        <?php endif;

        $pickup_point = get_post_meta( $order->get_id(), '_woosolid_pickup_point', true );
        $pickup_time  = get_post_meta( $order->get_id(), '_woosolid_pickup_time', true );
        if ( $pickup_point || $pickup_time ) : ?>
            <h3><?php esc_html_e( 'Ritiro', 'woosolid' ); ?></h3>
            <?php if ( $pickup_point ) : ?>
                <p><?php esc_html_e( 'Punto di ritiro:', 'woosolid' ); ?> <?php echo esc_html( $pickup_point ); ?></p>
            <?php endif; ?>
            <?php if ( $pickup_time ) : ?>
                <p><?php esc_html_e( 'Orario di ritiro:', 'woosolid' ); ?> <?php echo esc_html( $pickup_time ); ?></p>
            <?php endif; ?>
        <?php endif;

        return ob_get_clean();
    }
}

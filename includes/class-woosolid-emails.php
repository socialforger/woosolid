<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooSolid_Emails {

    public static function init() {
        // Estendibile per email ETS aggiuntive
    }

    public static function send_donation_summary_email( $user, $donations, $year ) {
        $to = $user->user_email;

        if ( ! $to ) {
            return false;
        }

        $subject = sprintf(
            __( 'Riepilogo donazioni anno %d', 'woosolid' ),
            $year
        );

        $name = trim( $user->first_name . ' ' . $user->last_name );
        if ( '' === $name ) {
            $name = $user->display_name;
        }

        $body  = '';
        $body .= sprintf( __( 'Gentile %s,', 'woosolid' ), $name ) . "\n\n";
        $body .= sprintf(
            __( 'di seguito trova il riepilogo delle donazioni effettuate nell’anno %d.', 'woosolid' ),
            $year
        ) . "\n\n";

        if ( empty( $donations ) ) {
            $body .= __( 'Non risultano donazioni registrate per l’anno selezionato.', 'woosolid' ) . "\n\n";
        } else {
            $body .= __( 'DONAZIONI NOMINATIVE', 'woosolid' ) . "\n";
            $body .= "----------------------------------------\n";

            $total = 0;

            foreach ( $donations as $donation ) {
                $date          = date_i18n( 'd/m/Y', strtotime( $donation['date'] ) );
                $amount        = number_format_i18n( $donation['amount'], 2 );
                $campaign_name = $donation['campaign_name'] ?: __( 'Campagna sconosciuta', 'woosolid' );
                $note          = $donation['note'];

                $body .= sprintf( __( 'Data: %s', 'woosolid' ), $date ) . "\n";
                $body .= sprintf( __( 'Campagna: %s', 'woosolid' ), $campaign_name ) . "\n";
                $body .= sprintf( __( 'Importo: € %s', 'woosolid' ), $amount ) . "\n";
                if ( $note ) {
                    $body .= sprintf( __( 'Causale: %s', 'woosolid' ), $note ) . "\n";
                }
                $body .= "\n";

                $total += (float) $donation['amount'];
            }

            $body .= "----------------------------------------\n";
            $body .= sprintf(
                __( 'Totale donazioni anno %d: € %s', 'woosolid' ),
                $year,
                number_format_i18n( $total, 2 )
            ) . "\n\n";
        }

        $body .= __( 'Questa comunicazione è valida ai fini fiscali secondo la normativa vigente sulle erogazioni liberali.', 'woosolid' ) . "\n\n";

        $ets_name = get_option( 'woosolid_ets_name' );
        $ets_cf   = get_option( 'woosolid_ets_cf' );

        if ( $ets_name ) {
            $body .= $ets_name . "\n";
        }
        if ( $ets_cf ) {
            $body .= sprintf( __( 'Codice Fiscale: %s', 'woosolid' ), $ets_cf ) . "\n";
        }

        $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

        return wp_mail( $to, $subject, $body, $headers );
    }
}

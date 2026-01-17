<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Listino {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu_entry' ] );
    }

    public static function add_menu_entry() {
        add_submenu_page(
            'woosolid-settings',
            'Listini',
            'Listini',
            'manage_options',
            'woosolid-listino',
            [ __CLASS__, 'render_page' ]
        );
    }

    protected static function get_listini() {
        $listini = get_option( 'woosolid_listini', [] );
        return is_array( $listini ) ? $listini : [];
    }

    public static function render_page() {

        $listini = self::get_listini();

        echo '<div class="wrap">';
        echo '<h1>Listini</h1>';

        if ( isset( $_GET['msg'] ) ) {
            $messages = [
                'nofile'       => 'Nessun file selezionato.',
                'uploaderror'  => 'Errore durante il caricamento del file.',
                'invalidformat'=> 'Formato non valido. Caricare un file CSV, XLS o XLSX.',
                'empty'        => 'Il file è vuoto.',
                'badheader'    => 'Intestazione non valida. Campi richiesti: <code>sku, nome, descrizione, prezzo, categoria, quantita, note</code>.',
            ];

            if ( isset( $messages[ $_GET['msg'] ] ) ) {
                echo '<div class="notice notice-error"><p>' . $messages[ $_GET['msg'] ] . '</p></div>';
            }
        }

        echo '<h2>Elenco listini caricati</h2>';

        if ( empty( $listini ) ) {
            echo '<p>Nessun listino caricato.</p>';
        } else {
            echo '<table class="widefat striped">';
            echo '<thead><tr>';
            echo '<th>Nome file</th>';
            echo '<th>Data caricamento</th>';
            echo '<th>Prodotti importati</th>';
            echo '<th>Stato</th>';
            echo '</tr></thead><tbody>';

            foreach ( $listini as $l ) {
                echo '<tr>';
                echo '<td>' . esc_html( $l['filename'] ) . '</td>';
                echo '<td>' . esc_html( $l['uploaded'] ) . '</td>';
                echo '<td>' . esc_html( $l['products'] ) . '</td>';
                echo '<td>' . ( ! empty( $l['active'] ) ? 'Attivo' : 'Non attivo' ) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        }

        echo '<hr>';

        echo '<h2>Carica nuovo listino</h2>';
        echo '<p>Formato richiesto: CSV/XLS/XLSX con colonne: <code>sku;nome;descrizione;prezzo;categoria;quantita;note</code></p>';

        echo '<form method="post" enctype="multipart/form-data" action="' . admin_url( 'admin-post.php' ) . '">';
        echo '<input type="hidden" name="action" value="woosolid_upload_listino">';
        echo '<input type="file" name="woosolid_listino" accept=".csv,.xls,.xlsx" required>';
        echo '<p><button class="button button-primary">Carica e importa listino</button></p>';
        echo '</form>';

        echo '</div>';
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Importer {

    public static function init() {
        add_action( 'admin_post_woosolid_upload_listino', [ __CLASS__, 'handle_upload' ] );
    }

    /**
     * Gestione upload + importazione prodotti
     */
    public static function handle_upload() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Non autorizzato' );
        }

        if ( ! isset( $_FILES['woosolid_listino'] ) ) {
            wp_redirect( add_query_arg( 'msg', 'nofile', wp_get_referer() ) );
            exit;
        }

        $file = $_FILES['woosolid_listino'];

        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            wp_redirect( add_query_arg( 'msg', 'uploaderror', wp_get_referer() ) );
            exit;
        }

        // Estensione
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, [ 'csv', 'xls', 'xlsx' ] ) ) {
            wp_redirect( add_query_arg( 'msg', 'invalidformat', wp_get_referer() ) );
            exit;
        }

        // Cartella upload
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/woosolid/listini/';

        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        // Nome file CSV finale
        $filename = 'listino_' . date( 'Ymd_His' ) . '.csv';
        $filepath = $dir . $filename;

        // Se è già CSV → lo copiamo direttamente
        if ( $ext === 'csv' ) {

            move_uploaded_file( $file['tmp_name'], $filepath );

        } else {

            // XLS o XLSX → conversione in CSV
            if ( ! class_exists( 'SimpleXLSX' ) ) {
                require_once plugin_dir_path( __FILE__ ) . 'lib/simplexlsx.class.php';
            }
            if ( ! class_exists( 'SimpleXLS' ) ) {
                require_once plugin_dir_path( __FILE__ ) . 'lib/simplexls.class.php';
            }

            if ( $ext === 'xlsx' ) {
                $xlsx = SimpleXLSX::parse( $file['tmp_name'] );
                $rows = $xlsx ? $xlsx->rows() : [];
            } else {
                $xls  = SimpleXLS::parse( $file['tmp_name'] );
                $rows = $xls ? $xls->rows() : [];
            }

            $fp = fopen( $filepath, 'w' );
            foreach ( $rows as $r ) {
                fputcsv( $fp, $r, ';' );
            }
            fclose( $fp );
        }

        // Lettura CSV
        $rows = array_map( function( $line ) {
            return str_getcsv( $line, ';' );
        }, file( $filepath ) );

        if ( empty( $rows ) ) {
            wp_redirect( add_query_arg( 'msg', 'empty', wp_get_referer() ) );
            exit;
        }

        // Header
        $header = array_map( 'trim', $rows[0] );

        $expected = [ 'sku', 'nome', 'descrizione', 'prezzo', 'categoria', 'quantita', 'note' ];
        $missing  = array_diff( $expected, $header );

        if ( ! empty( $missing ) ) {
            wp_redirect( add_query_arg( 'msg', 'badheader', wp_get_referer() ) );
            exit;
        }

        $indexes = array_flip( $header );

        // IMPORTAZIONE PRODOTTI
        $imported = 0;

        foreach ( $rows as $index => $row ) {

            if ( $index === 0 ) continue;
            if ( empty( array_filter( $row ) ) ) continue;

            $sku         = trim( $row[ $indexes['sku'] ] ?? '' );
            $nome        = trim( $row[ $indexes['nome'] ] ?? '' );
            $descrizione = trim( $row[ $indexes['descrizione'] ] ?? '' );
            $prezzo      = trim( $row[ $indexes['prezzo'] ] ?? '' );
            $categoria   = trim( $row[ $indexes['categoria'] ] ?? '' );
            $quantita    = trim( $row[ $indexes['quantita'] ] ?? '' );
            $note        = trim( $row[ $indexes['note'] ] ?? '' );

            if ( $sku === '' || $nome === '' ) continue;

            // Cerca prodotto esistente tramite SKU
            $existing_id = wc_get_product_id_by_sku( $sku );

            if ( $existing_id ) {
                $product = wc_get_product( $existing_id );
            } else {
                $product = new WC_Product_Simple();
                $product->set_sku( $sku );
            }

            // Dati base
            $product->set_name( $nome );
            $product->set_description( $descrizione );

            if ( is_numeric( str_replace( ',', '.', $prezzo ) ) ) {
                $product->set_regular_price( str_replace( ',', '.', $prezzo ) );
            }

            // Categoria
            if ( $categoria ) {
                $term = term_exists( $categoria, 'product_cat' );
                if ( ! $term ) {
                    $term = wp_insert_term( $categoria, 'product_cat' );
                }
                if ( ! is_wp_error( $term ) ) {
                    $product->set_category_ids( [ $term['term_id'] ] );
                }
            }

            // Quantità → stock
            if ( $quantita !== '' && is_numeric( $quantita ) ) {
                $product->set_manage_stock( true );
                $product->set_stock_quantity( intval( $quantita ) );
            }

            // Note → descrizione breve
            if ( $note ) {
                $product->set_short_description( $note );
            }

            $product->save();
            $imported++;
        }

        // Salva metadati listino
        $listini   = get_option( 'woosolid_listini', [] );
        $listini[] = [
            'id'        => time(),
            'filename'  => $filename,
            'uploaded'  => current_time( 'mysql' ),
            'products'  => $imported,
            'active'    => true,
        ];
        update_option( 'woosolid_listini', $listini );

        // Redirect alla pagina prodotti WooCommerce
        wp_redirect( admin_url( 'edit.php?post_type=product&woosolid_import=ok' ) );
        exit;
    }
}

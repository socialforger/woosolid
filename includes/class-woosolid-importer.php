<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WooSolid_Importer {

    public static function init() {
        add_action( 'admin_post_woosolid_upload_listino', [ __CLASS__, 'handle_upload' ] );
    }

    public static function handle_upload() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Non autorizzato' );
        }

        if ( ! isset( $_FILES['woosolid_listino'] ) ) {
            wp_redirect( admin_url( 'admin.php?page=woosolid-listino&msg=nofile' ) );
            exit;
        }

        $file = $_FILES['woosolid_listino'];

        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            wp_redirect( admin_url( 'admin.php?page=woosolid-listino&msg=uploaderror' ) );
            exit;
        }

        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, [ 'csv', 'xls', 'xlsx' ], true ) ) {
            wp_redirect( admin_url( 'admin.php?page=woosolid-listino&msg=invalidformat' ) );
            exit;
        }

        $upload_dir = wp_upload_dir();
        $dir        = trailingslashit( $upload_dir['basedir'] ) . 'woosolid/listini/';

        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        $filename = 'listino_' . date( 'Ymd_His' ) . '.csv';
        $filepath = $dir . $filename;

        // CSV diretto
        if ( $ext === 'csv' ) {

            if ( ! move_uploaded_file( $file['tmp_name'], $filepath ) ) {
                wp_redirect( admin_url( 'admin.php?page=woosolid-listino&msg=uploaderror' ) );
                exit;
            }

        } else {

            // XLS/XLSX → CSV
            if ( $ext === 'xlsx' ) {
                if ( ! class_exists( 'SimpleXLSX' ) ) {
                    require_once plugin_dir_path( __FILE__ ) . 'lib/simplexlsx.class.php';
                }
                $xlsx = SimpleXLSX::parse( $file['tmp_name'] );
                $rows = $xlsx ? $xlsx->rows() : [];
            } else {
                if ( ! class_exists( 'SimpleXLS' ) ) {
                    require_once plugin_dir_path( __FILE__ ) . 'lib/simplexls.class.php';
                }
                $xls  = SimpleXLS::parse( $file['tmp_name'] );
                $rows = $xls ? $xls->rows() : [];
            }

            if ( empty( $rows ) ) {
                wp_redirect( admin_url( 'admin.php?page=woosolid-listino&msg=empty' ) );
                exit;
            }

            $fp = fopen( $filepath, 'w' );
            foreach ( $rows as $r ) {
                fputcsv( $fp, $r, ';' );
            }
            fclose( $fp );
        }

        // Lettura CSV finale
        $lines = @file( $filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        if ( ! $lines ) {
            wp_redirect( admin_url( 'admin.php?page=woosolid-listino&msg=empty' ) );
            exit;
        }

        $rows   = array_map( fn( $line ) => str_getcsv( $line, ';' ), $lines );
        $header = array_map( 'trim', $rows[0] ?? [] );

        $expected = [ 'sku', 'nome', 'descrizione', 'prezzo', 'categoria', 'quantita', 'note' ];
        $missing  = array_diff( $expected, $header );

        if ( ! empty( $missing ) ) {
            wp_redirect( admin_url( 'admin.php?page=woosolid-listino&msg=badheader' ) );
            exit;
        }

        $indexes  = array_flip( $header );
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

            $existing_id = wc_get_product_id_by_sku( $sku );

            if ( $existing_id ) {
                $product = wc_get_product( $existing_id );
            } else {
                $product = new WC_Product_Simple();
                $product->set_sku( $sku );
            }

            $product->set_name( $nome );
            $product->set_description( $descrizione );

            $prezzo_norm = str_replace( ',', '.', $prezzo );
            if ( is_numeric( $prezzo_norm ) ) {
                $product->set_regular_price( $prezzo_norm );
            }

            if ( $categoria ) {
                $term = term_exists( $categoria, 'product_cat' );
                if ( ! $term ) {
                    $term = wp_insert_term( $categoria, 'product_cat' );
                }
                if ( ! is_wp_error( $term ) ) {
                    $product->set_category_ids( [ $term['term_id'] ] );
                }
            }

            if ( $quantita !== '' && is_numeric( $quantita ) ) {
                $product->set_manage_stock( true );
                $product->set_stock_quantity( (int) $quantita );
            }

            if ( $note ) {
                $product->set_short_description( $note );
            }

            $product->save();
            $imported++;
        }

        // Salvataggio metadati listino
        $listini = get_option( 'woosolid_listini', [] );
        if ( ! is_array( $listini ) ) {
            $listini = [];
        }

        $listini[] = [
            'id'        => time(),
            'filename'  => $filename,
            'uploaded'  => current_time( 'mysql' ),
            'products'  => $imported,
            'active'    => true,
        ];

        update_option( 'woosolid_listini', $listini );

        wp_redirect( admin_url( 'edit.php?post_type=product&woosolid_import=ok' ) );
        exit;
    }
}

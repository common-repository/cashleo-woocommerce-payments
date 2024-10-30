<?php
/**
 * Deactivation of plugin
 */

namespace Inc\Base;

class Deactivate {

    public static function deactivate() {
        delete_transient( 'woocashleo_token' );
        delete_transient( 'transactions_results' );
        delete_transient( 'filtered_transactions_results' );
    }

}
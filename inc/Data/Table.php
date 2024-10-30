<?php
/**
 *  Admin Transactions table.
 */

namespace Inc\Data;

class Table  {

    public function register() {
    
        add_action( 'plugins_loaded', array( $this, 'transactionTables' ) );
    
    }
    
    /**
    * display the Payment data in the order admin panel
    */
    public function getToken() {
    
        // Get any existing copy of our transient data - Auth token
        if ( false === ( $woocashleo_token = get_transient( 'woocashleo_token' ) ) ) {
    
            $account_email = get_option( 'woocommerce_woocashleo_gateway_settings' )['account_email'];
            $account_password = get_option( 'woocommerce_woocashleo_gateway_settings' )['account_password'];
    
            // It wasn't there, so regenerate the data and save the transient
            $passcode = json_encode( array( 'email' => $account_email, 'password' => $account_password ) );
    
    
            $response = wp_remote_post( 'https://app.ugmart.ug/api/login', 
                array ( 
                    'method' => 'POST', 
                    'headers' => array( 'Content-Type' => 'application/json', 'timeout' => 500,  ), 
                    'body' => $passcode  
                ) 
            );
    
            if ( is_wp_error( $response ) ) {
    
                $error_message = $response->get_error_message();
                echo "Something went wrong: $error_message";
                
            } else {
    
                $rep = json_decode( $response['body'], true );
                $woocashleo_token = $rep['token'];
                
                // Set a transient with token code.
                set_transient( 'woocashleo_token', $woocashleo_token, 1 * HOUR_IN_SECONDS );
            }
        }
    }
    
    public function transactionTables() {

        $this->getToken();

        // Check for results in options in table
        if ( false === ( $transactions_results = get_transient( 'transactions_results' ) ) ) {
    
            // Set the auth code from stored transient.
            $woocashleo_token = get_transient( 'woocashleo_token' );
    
            $auth = 'Bearer ' . $woocashleo_token;
    
            $new_response = wp_remote_post( 'https://app.ugmart.ug/api/transactions?limit=100', 
                array(
                    'method'   => 'GET',
                    'headers'  => array( 'Content-Type' => 'application/json', 'Authorization' => $auth )
                ) 
            );
    
            if ( is_wp_error( $new_response ) ) {
    
                $error_message = $new_response->get_error_message();
                echo "Something went wrong: $error_message";
    
            } else {
    
                $transactions_results = $new_response['body']; // use the content
    
                set_transient( 'transactions_results', $transactions_results, 5 * MINUTE_IN_SECONDS );
            }
    
        }
    
    }

}
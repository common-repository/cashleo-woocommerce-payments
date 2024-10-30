<?php
/*
* Gateway class Register
*/

class Woo_Cashleo extends WC_Payment_Gateway {
    
    public function __construct(){

        $this->id 					= 'woocashleo_gateway';
        $this->icon 				= apply_filters('woocommerce_woocashleo_icon', plugins_url( 'elements/images/paymart-logo.png' , dirname(__FILE__, 2) ) );
        $this->has_fields 			= false;
        $this->order_button_text 	= 'Make Payment';
        $this->payment_url 			= 'https://app.ugmart.ug/api/request-payment';
        $this->notify_url        	= WC()->api_request_url( 'woo_cashleo_gateway' );
        $this->method_title     	= 'woocashleo WooCommerce Payments';
        $this->method_description  	= 'Mobile Money (Airtel, MTN), Visa Card and MasterCard accepted';

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables
        $this->title 					= $this->get_option( 'title' );
        $this->description 				= $this->get_option( 'description' );
        $this->account_email 		    = $this->get_option( 'account_email' );
        $this->account_password 		= $this->get_option( 'account_password' );
        $this->collection_account 		= $this->get_option( 'collection_account' );
        $this->ugmart_account_name 	    = $this->get_option( 'ugmart_account_name' );

        $this->paying_phone_network 	    = $this->get_option( 'paying_phone_network' );
        $this->paying_phone_number 	    = $this->get_option( 'paying_phone_number' );

        // Check if the gateway can be used
        if ( ! $this->is_valid_for_use() ) {
            $this->enabled = false;
        }

        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        add_action( 'woocommerce_thankyou', array( $this, 'cashleo_redirect_custom' ) );

    }


    public function is_valid_for_use() {

        if( ! in_array( get_woocommerce_currency(), array( 'UGX', 'USD' ) ) ) {

            $this->msg = 'woocashleo doesn\'t support your store currency, set it to Ugandan Shillings UGX or United State Dollars &#36 <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-settings&tab=general">here</a>';

            return false;

        }

        return true;
    }


    /**
        * Check if this gateway is enabled
        */
    public function is_available() {

        if ( $this->enabled == "yes" ) {

            if ( ! $this->collection_account && ! $this->ugmart_account_name && ! $this->collection_account ) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Initialise Gateway Settings Form Fields
    **/
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' 		=> 'Enable/Disable',
                'type' 			=> 'checkbox',
                'label' 		=> 'Enable woocashleo Payment Gateway',
                'description' 	=> 'Enable or disable the gateway.',
                'desc_tip'      => true,
                'default' 		=> 'yes'
            ),
            'title' => array(
                'title' 		=> 'Title',
                'type' 			=> 'text',
                'description' 	=> 'This controls the title which the user sees during checkout.',
                'desc_tip'      => false,
                'default' 		=> 'Payment Gateway'
            ),
            'description' => array(
                'title' 		=> 'Description',
                'type' 			=> 'text',
                'description' 	=> 'This controls the description which the user sees during checkout.',
                'default' 		=> 'Mobile Money (Airtel & MTN) MasterCard and Visa Card accepted'
            ),
            'account_email' => array(
                'title' 		=> 'UGMart Account Email',
                'type' 			=> 'email',
                'description' 	=> 'This allows transactions for your account during checkout.',
                'desc_tip'      => true
            ),
            'account_password' => array(
                'title' 		=> 'Account Password',
                'type' 			=> 'password',
                'description' 	=> 'This allows transactions for your account during checkout.',
                'desc_tip'      => true
            ),
            'ugmart_account_name' => array(
                'title' 		=> 'Collection Account Name',
                'type' 			=> 'text',
                'description' 	=> 'Enter Collection Code e.g UGM1xxxxxxxxxxxxx. You can open an account at ugmart.ug',
                'default' 		=> '',
                'desc_tip'      => true
            ),
            'collection_account' => array(
                'title' 		=> 'Collection Account Code',
                'type' 			=> 'text',
                'description' 	=> 'Enter Collection Code e.g UGM1xxxxxxxxxxxxx. You can open an account at ugmart.ug',
                'default' 		=> '',
                'desc_tip'      => true
            ),
        );
    }

    /**
     * Process the payment and return the result
    **/
    public function process_payment( $order_id ) {

        $this->get_payment_cleared( $order_id );
        
    }

     
    public function cashleo_redirect_custom( $order_id ){

        $order = new WC_Order( $order_id );
    
        $url = bloginfo('url') + '/shop/';
    
        if ( $order->status != 'failed' ) {
            wp_redirect($url);
            exit;
        }
    }

    /*
    * Get API token
    */
    public static function getToken() {
    
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

    /**
     * Get woocashleo payment link
    **/
    public function get_payment_cleared( $order_id ) {
        
        global $woocommerce;

        // Get an instance of the WC_Order object (same as before)
        $order = new WC_Order( $order_id );

        // Get the order ID
        $order_id = $order->get_id();

        // Get the currency code
        $order_data = $order->get_data();
        $currency_symbol = $order_data['currency'];

        // Get cart total
        $order_total = $order->get_total();

        // Get any existing copy of our transient data - Auth token
        $this->getToken();

        // Set the auth code from stored transient.
        $woocashleo_token = get_transient( 'woocashleo_token' );
        $auth = 'Bearer ' . $woocashleo_token;
            
        $args = json_encode( 
            array ( 
                'account_code' => $this->collection_account,
                'transaction_id' => time(),
                'provider_id' => sanitize_text_field( $_POST['paying_network'] ), //mtn_mobile_money, visa_mastercard, airtel_money
                'msisdn' => sanitize_text_field( $_POST['payer_phone_number'] ),
                'currency' => $currency_symbol,
                'amount' => (int)$order_total,
                'application' => $this->ugmart_account_name,
                'description' => 'Payment for Order: ' . $order_id
            ) 
        );

        $request = wp_remote_get( $this->payment_url, 
            array(
                'method'   => 'POST',
                'headers'  => array( 'Content-Type' => 'application/json', 'timeout' => 3600, 'Authorization' => $auth ),
                'body'     => $args
            ) 
        );

        if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) && 'OK' == wp_remote_retrieve_response_message($request) ) {

            // Payment complete
            $order->payment_complete();

            wc_add_notice( 'Your order has been cleared.', 'success' );

            // Empty the cart
            $woocommerce->cart->empty_cart();

            // Empty awaiting payment session
            unset( $woocommerce->session->order_awaiting_payment );

            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );

        }

        elseif ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) && '' == wp_remote_retrieve_response_message($request) ) {

            wc_add_notice( 'Your order has been taken. Please check your payment account to clear the bill.' , 'error' );

            // Mark as on-hold (we're awaiting the cheque)
            $order->update_status('on-hold', __( 'Awaiting payment', 'woocommerce' ));

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            $woocommerce->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );

        } else {

            $reponse_error = json_decode( $request['body'], true );

            if ( $response_error['message'] == 'Request Failed. Insufficient Balance' ) {

                wc_add_notice( 'Sorry the payment is not complete. Please top up your mobile account and try again.', 'error' );
                
            }

            if ( $response_error['message'] == 'Transaction ID already exists. Provide a unique ID for every request. Try again' ) {

                wc_add_notice( 'Sorry the request was not completed. Please clear your cart and begin process again.', 'error' );
                
            }

            else {

                wc_add_notice( $response_error['message'] . 'Please Try again' , 'error' );

            }


            $response = array(
                'result'	=> 'fail',
                'redirect'	=> ''
            );

        }

    }
}
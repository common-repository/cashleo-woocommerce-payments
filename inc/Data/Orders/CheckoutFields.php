<?php
/**
 * Settings for payment gateway.
 */

namespace Inc\Data\Orders;

class CheckoutFields {

    public function register() {

        add_filter( 'woocommerce_gateway_description', array( $this, 'gateway_bacs_custom_fields' ), 20, 2 );
        add_action( 'woocommerce_checkout_process', array( $this, 'payer_phone_number_checkout_field_validation' ) );
        add_action( 'woocommerce_checkout_create_order', array( $this, 'save_payer_phone_number_to_order_meta_data' ), 10, 4 );
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'payer_phone_number_session_field' ), 20, 1);
        add_action( 'wp_ajax_payer_phone_number', array( $this, 'get_ajax_payer_phone_number' ) );
        add_action( 'wp_ajax_nopriv_payer_phone_number', array( $this, 'get_ajax_payer_phone_number' ) );
        add_action( 'wp_footer', array( $this, 'checkout_payer_phone_number_fields_script' ) );

    }
    
    // BACS payement gateway description: Append custom select field
    public function gateway_bacs_custom_fields( $description, $payment_id ) {
        //
        if( 'woocashleo_gateway' === $payment_id ){
            ob_start(); // Start buffering

                echo '<div class="payment-logos">
                    <img src="' . plugins_url( 'elements/images/mobile-payments.jpg' , dirname(__FILE__, 3) ) . '" alt="MTN Mobile Money & Airtel Money logos">
                </div>';

                echo '<div class="paymart-fields">';
                
                woocommerce_form_field( 'paying_network', 
                    array(
                        'type'          => 'select',
                        'label'         => __("Payment Options", "woocommerce"),
                        'class'         => array('form-row form-row-wide'),
                        'required'      => true,
                        'options'       => array(
                            ''          => __("Select the network", "woocommerce"),
                            'mtn_mobile_money'  => __("MTN Mobile Money", "woocommerce"),
                            'airtel_money'  => __("Airtel Money", "woocommerce"),
                        ),
                    ), 
                    ''
                );
                
                woocommerce_form_field( 'payer_phone_number', 
                    array(
                        'type'          => 'text',
                        'label'         => __("Billing Number e.g. (256771234567) without +", "woocommerce"),
                        'class'         => array('form-row form-row-wide'),
                        'required'      => true,
                    ),
                    ''
                );
                
                echo '<div>';
    
                $description .= ob_get_clean(); // Append buffered content
            }
    
            return $description;
    
    }
    
    // Process the field (validation)
    public function payer_phone_number_checkout_field_validation() {
        if ( $_POST['payment_method'] === 'woocashleo_gateway' && isset($_POST['payer_phone_number']) && empty($_POST['payer_phone_number']) )
            wc_add_notice( __( 'Please enter your number that is to be billed.' ), 'error' );
    
        if ( $_POST['payment_method'] === 'woocashleo_gateway' && isset($_POST['paying_network']) && empty($_POST['paying_network']) )
            wc_add_notice( __( 'Please select the matching network to phone number.' ), 'error' );
    }
    
    // Save payer_phone_number to the order as custom meta data
    public function save_payer_phone_number_to_order_meta_data( $order, $data ) {
        
        if( $data['payment_method'] === 'woocashleo_gateway' && isset( $_POST['payer_phone_number'] ) ) {
            $order->update_meta_data( '_payer_phone_number', sanitize_text_field( $_POST['payer_phone_number'] ) );
        }
        
    }
    
    // Keep only "woocashleo_gateway" method if the billing payer_phone_number number checkout field is filled
    public function payer_phone_number_session_field( $gateways ){
        foreach( $gateways as $gateway_id => $gateway ) {
    
            if( WC()->session->get( 'is_payer_phone_number' ) && $gateway_id != 'woocashleo_gateway' ){
                unset( $gateways[$gateway_id] );
            }
        }
        return $gateways;
    }
    
    // The Wordpress Ajax PHP receiver
    public function get_ajax_payer_phone_number() {
        if ( $_POST['payer_phone_number'] == '1' ){
            WC()->session->set('is_payer_phone_number', '1');
        } else {
            WC()->session->set('is_payer_phone_number', '0');
        }
        die();
    }
    
    // The jQuery Ajax request
    public function checkout_payer_phone_number_fields_script() {
        // Only checkout page
        if( is_checkout() && ! is_wc_endpoint_url() ):
    
        // Remove "is_payer_phone_number" custom WC session on load
        if( WC()->session->get('is_payer_phone_number') ){
            WC()->session->__unset('is_payer_phone_number');
        }
        ?>
        <script type="text/javascript">
            jQuery( function($){
                var a = 'input#payer_phone_number';
    
                // Ajax function
                function checkout_payer_phone_number( value ){
                        $.ajax({
                        type: 'POST',
                        url: wc_checkout_params.ajax_url,
                        data: {
                            'action': 'payer_phone_number',
                            'payer_phone_number': value != '' ? 1 : 0,
                        },
                        success: function (result) {
                            $('body').trigger('update_checkout');
                        }
                    });
                }
    
                // On start
                checkout_payer_phone_number($(a).val());
    
                // On change event
                $(a).change( function () {
                    checkout_payer_phone_number($(this).val());
                });
            });
        </script>
        <?php
        endif;
    }
}
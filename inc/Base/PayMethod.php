<?php
/**
	* Add Voguepay Gateway to WC
	**/
namespace Inc\Base;

Class PayMethod
{

    public function register(){
        
        add_filter( 'woocommerce_payment_gateways', array( $this, 'woocashleo_wc_add_gateway' ) );

    }
    
    public function woocashleo_wc_add_gateway($methods) {
        
        $methods[] = 'Woo_Cashleo';
        return $methods;

    }
    
}


	

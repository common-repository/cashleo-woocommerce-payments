<?php
/**
* only add the Ugandan Shillings currency and symbol if WC versions is less than 2.1
*/
namespace Inc\Data;

Class Currency
{

    public function register(){

        if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) <= 0 ) {
                
            if( ! function_exists( 'cashleo_add_my_currency' ) ) {
                add_filter( 'woocommerce_currencies', array( $this, 'cashleo_add_my_currency' ) );
            }
        
            if( ! function_exists( 'cashleo_add_my_currency_symbol' ) ) {
                add_filter('woocommerce_currency_symbol', array( $this, 'cashleo_add_my_currency_symbol' ), 10, 2);
            }
        }

    }

    /**
    * Add UGX as a currency in WC
    **/
    public function cashleo_add_my_currency( $currencies ) {
        $currencies['UGX'] = __( 'Ugandan Shillings', 'woocommerce' );
        return $currencies;
    }
    

    /**
    * Enable the Ugandan Shillings currency symbol in WC
    **/
    
    public function cashleo_add_my_currency_symbol( $currency_symbol, $currency ) {
            switch( $currency ) {
                case 'UGX': $currency_symbol = 'UGX '; break;
            }
            return $currency_symbol;
    }
    
}

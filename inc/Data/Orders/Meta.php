<?php
/*
 * display the Payment data in the order admin panel
 */

namespace Inc\Data\Orders;

class Meta
{
    public function register() {

        add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'displayPayDetails' ) );
    
    }
    
    public function displayPayDetails( $order ){
        ?>
            <div class=" form-field form-field-wide">
                <h3><?php _e( 'Payment Details' ); ?></h3>
                <p class="form-field form-field-wide"><strong><?php 
                    echo __( 'Billed Number' ) . ': </strong>' . get_post_meta( $order->get_id(), '_payer_phone_number', true ); 
                ?></p>
            </div>
        <?php 
    }
}


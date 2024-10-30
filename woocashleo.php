<?php
/*
	Plugin Name: Cashleo WooCommerce Payments
	Plugin URI: https://cashleo.com
	Description: Cashleo Woocommerce Payments allows you to accept payment on your Woocommerce store via Mobile Money (Airtel, MTN).
	Version: 1.0.1
	Author: Cashleo Limited
	Author URI: https://cashleo.com/
	License: GPL-3.0+
	License URI: http://www.gnu.org/licenses/gpl-2.0.txt
	WC requires at least: 3.0
	WC tested up to: 3.5.4
*/

// If this file is called firectly, abort!!!
defined( 'ABSPATH' ) or die( 'Unauthorized Access!' );

/**
 * Check if WooCommerce is active
 **/

function woocommerce_not_installed_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<p><?php _e( 'This Plugin requires WooCommerce 3+ to work. Please install it from repository', 'woowoocashleo' ); ?></p>
	</div>
	<?php
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'admin_notices', 'woocommerce_not_installed_notice' );
}

// Require once the Composer Autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * The code that runs during plugin deactivation
 */
function deactivateWooPaymartPlugin() {
	Inc\Base\Deactivate::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivateWooPaymartPlugin' );

function woocashleo_wc_init() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
    
    // Instatiate the class for gateway
    if ( file_exists( dirname( __FILE__ ) . '/inc/Data/WooCashleoGateway.php' ) ) {
        require_once dirname( __FILE__ ) . '/inc/Data/WooCashleoGateway.php';
	}

	add_action( 'wp_enqueue_scripts', 'woocashleo_enqueue' );

	function woocashleo_enqueue() {
		wp_enqueue_style( 'woocashleo-gateway', plugins_url( 'elements/css/checkout.css' , __FILE__ ) );
	}

	/**
	 * Initialize all the core classes
	 */
	if ( class_exists( 'Inc\\Init' ) ) {
		Inc\Init::register_services();
	}
	
}

add_action('plugins_loaded', 'woocashleo_wc_init', 0);
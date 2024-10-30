<?php
/**
 * Increase the timeout for curl requestes.
 */
namespace Inc\Base;

class CurlBoost {
	
	private $timeBoost;

	public function __construct() {
		
		// Time in seconds to increase for Curl Request. 
		// Just change 30 to desired value
		$this->timeBoost = 30;

	}

	public function register() {
		
		add_action( 'http_api_curl', array( $this, 'woocashleo_curl_timeout' ), 9999, 1 );
		add_filter( 'http_request_timeout', array( $this, 'woocashleo_http_request_timeout' ), 9999 );
		add_filter( 'http_request_args', array( $this, 'woocashleo_http_request_args' ), 9999, 1 );

	}


    /**
	 * Increase timeout for CURL requests to 30 seconds to allow the customer to make the purchase.
	 **/
	public function woocashleo_curl_timeout( $handle ){

		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $this->timeBoost );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $this->timeBoost );

	}

	/**
	 * @param  int  $timeout Initial timeout should be $this->timeBoost seconds, but something is changing it to 1.
	 * @return int
	 */
	public function woocashleo_http_request_timeout( $timeout ) {

		return $this->timeBoost; 

	}

	/**
	 * @param  arr  $args The args being used by WP when doing the cURL post.
	 * @return arr
	 */
	public function woocashleo_http_request_args( $args ){

		$args['timeout'] = $this->timeBoost;
		return $args;

	}
    
}
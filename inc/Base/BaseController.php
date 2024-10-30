<?php 
/**
 * @package  AlecadddPlugin
 */
namespace Inc\Base;

class BaseController
{
	public $plugin_path;
	public $plugin_url;
	public $plugin;
	public $extra_url;
    
	public function __construct() {

		$this->plugin_path = plugin_dir_path( dirname( __FILE__, 2 ) );
		$this->plugin_url = plugin_dir_url( dirname( __FILE__, 2 ) );
		$this->plugin = plugin_basename( dirname( __FILE__, 2 ) ) . '/woocashleo.php';
		$this->extra_url = 'admin.php?page=wc-settings&tab=checkout&section=woocashleo_gateway';
        
	}
}
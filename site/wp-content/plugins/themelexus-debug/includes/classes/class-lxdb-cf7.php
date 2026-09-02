<?php

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) exit;
if ( !lxdb_is_contactform_activated() ) return;

/**
 * Class LXDB_CF7
 *
 * Thats where we bring the plugin to life
 *
 * @package		LXDB
 * @subpackage	Classes/LXDB_CF7
 * @author		WPOPAL
 * @since		1.0.0
 */
class LXDB_CF7{

	static $instance;

	public static function getInstance()
	{
		if (!isset(self::$instance) && !(self::$instance instanceof LXDB_CF7)) {
			self::$instance = new LXDB_CF7();
		}
		return self::$instance;
	}


	/**
	 * Our LXDB_CF7 constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
		add_action('plugins_loaded', [$this, 'add_filter_after_loaded'], 10);

	}

	public function add_filter_after_loaded() {
		if (defined('THEMELEXUS_SITE_DEMO') && THEMELEXUS_SITE_DEMO) {
			add_filter( 'pre_wp_mail', '__return_true');
		}
	}

}

LXDB_CF7::getInstance();
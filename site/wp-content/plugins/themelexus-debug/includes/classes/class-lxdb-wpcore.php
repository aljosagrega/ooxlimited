<?php

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) exit;
if ( !lxdb_is_elementor_activated() ) return;

/**
 * Class LXDB_Wpcore
 *
 * Thats where we bring the plugin to life
 *
 * @package		LXDB
 * @subpackage	Classes/LXDB_Wpcore
 * @author		WPOPAL
 * @since		1.0.0
 */
class LXDB_Wpcore{

	/**
	 * Our LXDB_Wpcore constructor 
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
		if ( version_compare( $GLOBALS['wp_version'], '6.7.1', '>=' ) ) {
			add_filter('wp_img_tag_add_auto_sizes', '__return_false');
		}
	}

}

new LXDB_Wpcore();
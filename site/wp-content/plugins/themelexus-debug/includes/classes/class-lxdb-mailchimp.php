<?php

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) exit;
if ( !lxdb_is_mailchimp_activated() ) return;

/**
 * Class LXDB_MailChimp
 *
 * Thats where we bring the plugin to life
 *
 * @package		LXDB
 * @subpackage	Classes/LXDB_MailChimp
 * @author		WPOPAL
 * @since		1.0.0
 */
class LXDB_MailChimp{

	static $instance;

	public static function getInstance()
	{
		if (!isset(self::$instance) && !(self::$instance instanceof LXDB_MailChimp)) {
			self::$instance = new LXDB_MailChimp();
		}
		return self::$instance;
	}


	/**
	 * Our LXDB_MailChimp constructor 
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
		if (defined('MC4WP_VERSION') && version_compare(MC4WP_VERSION, '4.10.8', '>=')) {
			add_filter('mc4wp_settings', [$this, 'trigger_default_key']);
		}

		if (defined('THEMELEXUS_SITE_DEMO') && THEMELEXUS_SITE_DEMO) {
			add_action('mc4wp_form_api_error', [$this, 'trigger_response_api_error'], 99, 2);
			add_filter('mc4wp_form_errors', [$this, 'trigger_form_errors'], 99, 2);
		}
	}

	public function trigger_default_key($options) {

		if (empty($options['api_key']) && !defined('MC4WP_API_KEY')) {
			$options['api_key'] = 'Themelexus';
		}

		return $options;
	}

	public function trigger_response_api_error($form, $error_message) {
		$form->errors = [];
		$form->notices = [];
		$form->last_event = 'subscribed';
		$form->add_notice($form->messages['subscribed'], 'success');
	}

	public function trigger_form_errors($errors, $form) {
		return [];
	}

}

LXDB_MailChimp::getInstance();
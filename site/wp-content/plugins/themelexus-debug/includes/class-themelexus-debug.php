<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LXDB_Start_Instance' ) ) :

	/**
	 * Main LXDB_Start_Instance Class.
	 *
	 * @package		LXDB
	 * @subpackage	Classes/LXDB_Start_Instance
	 * @since		1.0.0
	 * @author		LexusTeam
	 */
	final class LXDB_Start_Instance {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|LXDB_Start_Instance
		 */
		private static $instance;

		/**
		 * LXDB helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 */
		public $helpers;

		/**
		 * LXDB settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'You are not allowed to clone this class.', 'themelexus-debug' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'You are not allowed to unserialize this class.', 'themelexus-debug' ), '1.0.0' );
		}

		/**
		 * Main LXDB_Start_Instance Instance.
		 *
		 * Insures that only one instance of LXDB_Start_Instance exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|LXDB_Start_Instance	The one true LXDB_Start_Instance
		 */
		public static function instance() {
			if ( !isset( self::$instance ) && !(self::$instance instanceof LXDB_Start_Instance)) {
				self::$instance	= new LXDB_Start_Instance;
				self::$instance->include_helpers();
				self::$instance->base_hooks();
				self::$instance->include_classes();
				
				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'LXDB/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function include_classes() {
			$files_custom = glob(LXDB_PLUGIN_DIR.'includes/classes/*.php');
			foreach ($files_custom as $file) {
                if (file_exists($file)) {
                    require_once $file;
                }
            }
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function include_helpers() {
			$files_custom = glob(LXDB_PLUGIN_DIR.'includes/helpers/*.php');
			foreach ($files_custom as $file) {
                if (file_exists($file)) {
                    require_once $file;
                }
            }
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_theme_support' ) );
		}

		/**
		 * Loads the theme support
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_theme_support() {
			require_once LXDB_PLUGIN_DIR.'includes/integrations/bootstrap.php';
		}

	}

endif; // End if class_exists check.
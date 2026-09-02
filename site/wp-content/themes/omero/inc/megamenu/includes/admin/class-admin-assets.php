<?php

defined( 'ABSPATH' ) || exit();

/**
 * Omero_Megamenu_Walker
 *
 * extends Walker_Nav_Menu
 */
class Omero_Admin_Megamenu_Assets {

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'elementor/editor/after_enqueue_scripts', array( __CLASS__, 'add_scripts_editor' ) );
	}

	public static function add_scripts_editor() {
		global $omero_version;
		if ( isset( $_REQUEST['omero-menu-editable'] ) && $_REQUEST['omero-menu-editable'] ) {
			wp_register_script( 'omero-elementor-menu', get_template_directory_uri() . '/inc/megamenu/assets/js/editor.js', [], $omero_version );
			wp_enqueue_script( 'omero-elementor-menu' );
		}
	}

	/**
	 * enqueue scripts
	 */
	public static function enqueue_scripts( $page ) {
		global $omero_version;
		if ( $page === 'nav-menus.php' ) {
			wp_enqueue_script( 'backbone' );
			wp_enqueue_script( 'underscore' );

			$suffix = '.min';
			wp_register_script(
				'jquery-elementor-select2',
				ELEMENTOR_ASSETS_URL . 'lib/e-select2/js/e-select2.full' . $suffix . '.js',
				[
					'jquery',
				],
				'4.0.6-rc.1',
				true
			);
			wp_enqueue_script( 'jquery-elementor-select2' );
			wp_register_style(
				'elementor-select2',
				ELEMENTOR_ASSETS_URL . 'lib/e-select2/css/e-select2' . $suffix . '.css',
				[],
				'4.0.6-rc.1'
			);
			wp_enqueue_style( 'elementor-select2' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_register_script( 'omero-megamenu', get_template_directory_uri() . '/inc/megamenu/assets/js/admin.js', array(
				'jquery',
				'backbone',
				'underscore'
			), $omero_version, true );
			wp_localize_script( 'omero-megamenu', 'omero_memgamnu_params', apply_filters( 'omero_admin_megamenu_localize_scripts', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'close' => esc_html__( 'Close', 'omero' ),
					'submit' => esc_html__( 'Save', 'omero' )
				),
				'nonces'  => array(
					'load_menu_data' => wp_create_nonce( 'omero-menu-data-nonce' )
				)
			) ) );
			wp_enqueue_script( 'omero-megamenu' );

			wp_enqueue_style( 'omero-megamenu', get_template_directory_uri() . '/inc/megamenu/assets/css/admin.css', [], $omero_version );
			wp_enqueue_style( 'omero-elementor-custom-icon', get_theme_file_uri( '/assets/css/admin/elementor/icons.css' ), [], $omero_version );
		}

	}

}

Omero_Admin_Megamenu_Assets::init();

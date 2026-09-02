<?php

// Direct load is not allowed
if (! defined('ABSPATH')) {
    die();
}

use Elementor\Plugin;

class Omero_Nested_Carousel_Init
{

    /**
     * The Constructor
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'register_widget_script'), 9999);
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'enqueue_script'));
        add_action('elementor/widgets/register', array($this, 'widget_init'));
    }

    public function register_widget_script()
    {
        global $omero_version;
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        wp_register_script('omero-nested-carousel-base', get_theme_file_uri('/assets/js/elementor-addon/nested-carousel/nested-carousel-class'.$suffix.'.js'), array('elementor-frontend'), $omero_version, true);
        wp_register_script('omero-nested-carousel', get_theme_file_uri('/assets/js/elementor-addon/nested-carousel/nested-carousel'.$suffix.'.js'), array('jquery', 'elementor-frontend', 'omero-nested-carousel-base'), $omero_version, true);

        $animations = \Elementor\Control_Animation::get_animations();
        $animation_keys = [];
        if (!empty($animations)) {
            foreach ($animations as $group) {
                $animation_keys = array_merge($animation_keys, array_keys($group));
            }
        }
        wp_localize_script( 'omero-nested-carousel-base', 'omero_nested_carousel', array(
            'animation_keys' => $animation_keys
        ));
    }

    public function enqueue_script()
    {
        global $omero_version;
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        wp_enqueue_script('omero-nested-carousel-admin', get_theme_file_uri('/assets/js/elementor-addon/nested-carousel/nested-carousel-admin'.$suffix.'.js'), array('nested-elements'), $omero_version, true);
    }

    public function widget_init($widgets_manager)
    {
        if (Plugin::$instance->experiments->is_feature_active('container')) {
            require_once get_theme_file_path('inc/elementor/nested-carousel/widget.php');;
        }
    }
}

new Omero_Nested_Carousel_Init;

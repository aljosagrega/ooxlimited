<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Themelexus_Sidebar')) :

    /**
     * The CPT Theme Lexus class
     */
    class Themelexus_Sidebar {

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct() {

            add_action( 'init', [$this, 'themelexus_register_sidebar'] );
            add_action( 'widgets_init', [$this, 'themelexus_register_widget'], 99 );

        }

        /**
         * Register Some custom sidebar
         *
         * @return void
         */
        public function themelexus_register_sidebar() {
            $sidebars = apply_filters('themelexus_custom_sidebar', []);

            if (!empty($sidebars)) {
                foreach ($sidebars as $post_type => $args) {
                    if (!is_string($post_type) || !post_type_exists($post_type)) continue;
                    if (!isset($args['id'])) continue;
                    if (!isset($args['name'])) continue;
                    
                    register_sidebar( $args );
                }
            }    
        }

        /**
         * Register Some custom widgets
         *
         * @return void
         */
        public function themelexus_register_widget() {
            $widgets = apply_filters('themelexus_custom_widgets', []);

            if (!empty($widgets)) {
                foreach ($widgets as $widget_class) {
                    if (empty($widget_class) || !class_exists($widget_class)) continue;
                    
                    register_widget( $widget_class );
                }
            }    
        }
    }

    new Themelexus_Sidebar();

endif;

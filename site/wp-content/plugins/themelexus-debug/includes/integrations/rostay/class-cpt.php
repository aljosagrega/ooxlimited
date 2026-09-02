<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Rostay_Handle_CPT')) :

    /**
     * The CPT Rostay class
     */
    class Rostay_Handle_CPT {

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct() {

            add_action( 'init', [$this, 'rostay_register_my_cpts'] );
            add_action( 'init', [$this, 'rostay_register_taxes'] );

            add_action( 'widgets_init', [$this, 'rostay_cpt_widgets_init'] );
            add_filter( 'rostay_theme_sidebar', [$this, 'set_sidebar'], 30 );
            add_filter( 'body_class', [$this, 'body_classes'], 30 );
            
        }

        function rostay_register_my_cpts() {

            /**
             * Post Type: Event.
             */

            $event_slug = self::get_theme_option('event_slug', 'event');
            $event_label = self::get_theme_option('event_label', __( "Events", "rostay" ));

            $args = [
                "label" => $event_label,
                "description" => "",
                "public" => true,
                "publicly_queryable" => true,
                "show_ui" => true,
                "show_in_rest" => true,
                "rest_base" => "",
                "rest_controller_class" => "WP_REST_Posts_Controller",
                "rest_namespace" => "wp/v2",
                "has_archive" => false,
                "show_in_menu" => true,
                "show_in_nav_menus" => true,
                "delete_with_user" => false,
                "exclude_from_search" => false,
                "capability_type" => "post",
                "map_meta_cap" => true,
                "hierarchical" => false,
                "can_export" => true,
                "rewrite" => [ "slug" => $event_slug, "with_front" => true ],
                "query_var" => true,
                "menu_position" => 20,
                "menu_icon" => "dashicons-calendar-alt",
                "supports" => [ "title", "editor", "thumbnail", "excerpt", "author" ],
                "show_in_graphql" => false,
                "taxonomies" => [ "event-category", "post_tag" ],
            ];

            $args = apply_filters('rostay_custom_args_event_pt', $args);

            register_post_type( "event", $args );
        }

        function rostay_register_taxes() {

            /**
             * Taxonomy: Event Category.
             */

            $labels = [
                "name" => self::get_theme_option('event_category_label', __( "Categories", "soudify" )),
                "singular_name" => self::get_theme_option('event_category_single_label', __( "Category", "soudify" )),
            ];
            $tax_slug = self::get_theme_option('event_category_slug', 'event-category');
        
            
            $args = [
                "label" => esc_html__( "Category", "rostay" ),
                "labels" => $labels,
                "public" => true,
                "publicly_queryable" => false,
                "hierarchical" => true,
                "show_ui" => true,
                "show_in_menu" => true,
                "show_in_nav_menus" => true,
                "query_var" => true,
                "rewrite" => [ 'slug' => $tax_slug, 'with_front' => false, ],
                "show_admin_column" => true,
                "show_in_rest" => false,
                "show_tagcloud" => false,
                "rest_base" => "event-category",
                "rest_controller_class" => "WP_REST_Terms_Controller",
                "rest_namespace" => "wp/v2",
                "show_in_quick_edit" => true,
                "sort" => false,
                "show_in_graphql" => false,
            ];
            register_taxonomy( "event-category", [ "event" ], apply_filters('rostay_custom_args_event_category', $args) );
        }

        function rostay_cpt_widgets_init() {
            register_sidebar( array(
                'name'          => __( 'Sidebar Single Event', 'rostay' ),
                'id'            => 'sidebar-event',
                'description'   => __( 'Display in event single page', 'rostay' ),
                'before_widget' => '<div id="%1$s" class="widg et %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<span class="gamma widget-title">',
                'after_title'   => '</span>',
            ) );

            require_once __DIR__ . '/class-wp-widget-event-details.php';
            require_once __DIR__ . '/class-wp-widget-event-contacts.php';
            register_widget('Rostay_WP_Widget_Event_Details');
            register_widget('Rostay_WP_Widget_Event_Contacts');
        }

        function set_sidebar($name) {
            if (is_singular('event')) {
                $name = 'sidebar-event';
            }
            return $name;
        }

        function body_classes($classes) {
            if (is_singular('event') && is_active_sidebar('sidebar-event')) {
                $classes[] = 'rostay-sidebar-event';
                $classes[] = 'rostay-sidebar-right';
            }
            return $classes;
        }

        private static function get_theme_option($option_name, $default = false) {

            if ($option = get_option('rostay_options_' . $option_name)) {
                $default = $option;
            }
    
            return $default;
        }

    }

endif;

return new Rostay_Handle_CPT();

<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Soudify_Handle_CPT')) :

    /**
     * The CPT Soudify class
     */
    class Soudify_Handle_CPT {

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct() {

            add_action( 'init', [$this, 'soudify_register_my_cpts'] );
            add_action( 'init', [$this, 'soudify_register_taxes'] );

            add_action( 'widgets_init', [$this, 'cpt_widgets_init'] );
            add_filter( 'soudify_theme_sidebar', [$this, 'set_sidebar'], 30 );
            add_filter( 'body_class', [$this, 'body_classes'], 30 );      
            
            add_action( 'wp_enqueue_scripts', array($this, 'cpt_scripts'), 20 );

            add_filter( 'soudify_check_full_width_container', [$this, 'check_full_width'], 30 );      
        }

        function soudify_register_my_cpts() {

            /**
             * Post Type: Services.
             */
        
            $service_slug = self::get_theme_option('service_slug', 'service');
            $service_label = self::get_theme_option('service_label', __( "Services", "soudify" ));
        
            $args = [
                "label" => $service_label,
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
                "rewrite" => [ "slug" => $service_slug, "with_front" => true ],
                "query_var" => true,
                "menu_position" => 20,
                "menu_icon" => "dashicons-feedback",
                "supports" => [ "title", "editor", "thumbnail", "excerpt", "author" ],
                "show_in_graphql" => false,
            ];

            $args = apply_filters('soudify_custom_args_service_pt', $args);
        
            register_post_type( "service", $args );

            /**
             * Post Type: Project.
             */

            $project_slug = self::get_theme_option('project_slug', 'project');
            $project_label = self::get_theme_option('project_label', __( "Projects", "soudify" ));

            $args = [
                "label" => $project_label,
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
                "rewrite" => [ "slug" => $project_slug, "with_front" => true ],
                "query_var" => true,
                "menu_position" => 20,
                "menu_icon" => "dashicons-calendar-alt",
                "supports" => [ "title", "editor", "thumbnail", "excerpt", "author" ],
                "show_in_graphql" => false,
                "taxonomies" => [ "project-category" ],
            ];

            $args = apply_filters('soudify_custom_args_project_pt', $args);

            register_post_type( "project", $args );

            /**
             * Post Type: Studios.
             */

            $studio_slug = self::get_theme_option('studio_slug', 'studio');
            $studio_label = self::get_theme_option('studio_label', __( "Studios", "soudify" ));

            $args = [
                "label" => $studio_label,
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
                "rewrite" => [ "slug" => $studio_slug, "with_front" => true ],
                "query_var" => true,
                "menu_position" => 20,
                "menu_icon" => "dashicons-layout",
                "supports" => [ "title", "editor", "thumbnail", "excerpt", "author" ],
                "show_in_graphql" => false,
            ];

            $args = apply_filters('soudify_custom_args_studio_pt', $args);

            register_post_type( "studio", $args );

            /**
             * Post Type: Engineers.
             */

            $engineer_slug = self::get_theme_option('engineer_slug', 'engineer');
            $engineer_label = self::get_theme_option('engineer_label', __( "Engineers", "soudify" ));

            $args = [
                "label" => $engineer_label,
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
                "rewrite" => [ "slug" => $engineer_slug, "with_front" => true ],
                "query_var" => true,
                "menu_position" => 20,
                "menu_icon" => "dashicons-id-alt",
                "supports" => [ "title", "editor", "thumbnail", "excerpt", "author" ],
                "show_in_graphql" => false,
            ];

            $args = apply_filters('soudify_custom_args_engineer_pt', $args);

            register_post_type( "engineer", $args );
        }

        function soudify_register_taxes() {

            /**
             * Taxonomy: Project Category.
             */
        
            $labels = [
                "name" => self::get_theme_option('project_category_label', __( "Categories", "soudify" )),
                "singular_name" => self::get_theme_option('project_category_single_label', __( "Category", "soudify" )),
            ];
            $tax_slug = self::get_theme_option('project_category_slug', 'project-category');
            
            $args = [
                "label" => esc_html__( "Category", "soudify" ),
                "labels" => $labels,
                "public" => true,
                "publicly_queryable" => true,
                "hierarchical" => true,
                "show_ui" => true,
                "show_in_menu" => true,
                "show_in_nav_menus" => true,
                "query_var" => true,
                "rewrite" => [ 'slug' => $tax_slug, 'with_front' => false, ],
                "show_admin_column" => true,
                "show_in_rest" => false,
                "show_tagcloud" => false,
                "rest_base" => "project-category",
                "rest_controller_class" => "WP_REST_Terms_Controller",
                "rest_namespace" => "wp/v2",
                "show_in_quick_edit" => true,
                "sort" => false,
                "show_in_graphql" => false,
            ];

            register_taxonomy( "project-category", [ "project" ], apply_filters('soudify_custom_args_project_category', $args) );
        }

        function cpt_widgets_init() {
            register_sidebar( array(
                'name'          => __( 'Sidebar Single Service', 'soudify' ),
                'id'            => 'sidebar-service',
                'description'   => __( 'Display in service single page', 'soudify' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<span class="gamma widget-title">',
                'after_title'   => '</span>',
            ) );
            register_sidebar( array(
                'name'          => __( 'Sidebar Single Project', 'soudify' ),
                'id'            => 'sidebar-project',
                'description'   => __( 'Display in project single page', 'soudify' ),
                'before_widget' => '<div id="%1$s" class="widg et %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<span class="gamma widget-title">',
                'after_title'   => '</span>',
            ) );

            require_once __DIR__ . '/class-wp-widget-service.php';
            register_widget('Soudify_WP_Widget_Service');
        }

        function set_sidebar($name) {
            if (is_singular('service')) {
                $name = 'sidebar-service';
            }
            return $name;
        }

        function body_classes($classes) {
            if (is_singular('service') && is_active_sidebar('sidebar-service')) {
                $classes[] = 'soudify-sidebar-service';
                $classes[] = 'soudify-sidebar-left';
            }
            return $classes;
        }

        function cpt_scripts() {
            if (is_singular('project')) {
                wp_enqueue_script('swiper');
                wp_enqueue_script('soudify-swiper');
            }
        }

        function check_full_width($full_width) {
            if (is_singular('project')) {
                return true;
            }
            return $full_width;
        }

        private static function get_theme_option($option_name, $default = false) {

            if ($option = get_option('soudify_options_' . $option_name)) {
                $default = $option;
            }
    
            return $default;
        }
    }

endif;

return new Soudify_Handle_CPT();

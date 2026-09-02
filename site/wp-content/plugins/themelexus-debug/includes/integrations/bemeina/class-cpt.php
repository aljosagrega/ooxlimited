<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Bemeina_Handle_CPT')) :

    /**
     * The CPT Bemeina class
     */
    class Bemeina_Handle_CPT {

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct() {

            add_action( 'init', [$this, 'bemeina_register_my_cpts'] );
            add_action( 'init', [$this, 'bemeina_register_taxes'] );

        }

        function bemeina_register_my_cpts() {

            /**
             * Post Type: Services.
             */
        
            $service_slug = self::get_theme_option('service_slug', 'service');
            $service_label = self::get_theme_option('service_label', __( "Services", 'themelexus-debug' ));
        
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

            $args = apply_filters('bemeina_custom_args_service_pt', $args);
        
            register_post_type( "service", $args );

            /**
             * Post Type: Portfolio.
             */

            $portfolio_slug = self::get_theme_option('portfolio_slug', 'portfolio');
            $portfolio_label = self::get_theme_option('portfolio_label', __( "Portfolios", 'themelexus-debug' ));

            $args = [
                "label" => $portfolio_label,
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
                "rewrite" => [ "slug" => $portfolio_slug, "with_front" => true ],
                "query_var" => true,
                "menu_position" => 20,
                "menu_icon" => "dashicons-portfolio",
                "supports" => [ "title", "editor", "thumbnail", "excerpt", "author" ],
                "show_in_graphql" => false,
                "taxonomies" => [ "portfolio-category" ],
            ];

            $args = apply_filters('bemeina_custom_args_portfolio_pt', $args);

            register_post_type( "portfolio", $args );

            /**
             * Post Type: Team.
             */

            $team_slug = self::get_theme_option('team_slug', 'team');
            $team_label = self::get_theme_option('team_label', __( "Teams", 'themelexus-debug' ));

            $args = [
                "label" => $team_label,
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
                "rewrite" => [ "slug" => $team_slug, "with_front" => true ],
                "query_var" => true,
                "menu_position" => 20,
                "menu_icon" => "dashicons-businessperson",
                "supports" => [ "title", "editor", "thumbnail", "author" ],
                "show_in_graphql" => false,
            ];

            $args = apply_filters('bemeina_custom_args_team_pt', $args);

            register_post_type( "team", $args );
        }

        function bemeina_register_taxes() {

            /**
             * Taxonomy: Portfolio Category.
             */
        
            $labels = [
                "name" => self::get_theme_option('portfolio_category_label', __( "Categories", 'themelexus-debug' )),
                "singular_name" => self::get_theme_option('portfolio_category_single_label', __( "Category", 'themelexus-debug' )),
            ];
            $tax_slug = self::get_theme_option('portfolio_category_slug', 'portfolio-category');
            
            $args = [
                "label" => esc_html__( "Category", 'themelexus-debug' ),
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
                "rest_base" => "portfolio-category",
                "rest_controller_class" => "WP_REST_Terms_Controller",
                "rest_namespace" => "wp/v2",
                "show_in_quick_edit" => true,
                "sort" => false,
                "show_in_graphql" => false,
            ];

            register_taxonomy( "portfolio-category", [ "portfolio" ], apply_filters('bemeina_custom_args_portfolio_category', $args) );
        }

        private static function get_theme_option($option_name, $default = false) {

            if ($option = get_option('bemeina_options_' . $option_name)) {
                $default = $option;
            }
    
            return $default;
        }

    }

endif;

return new Bemeina_Handle_CPT();

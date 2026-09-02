<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Themelexus_Taxonomy')) :

    /**
     * The CPT Theme Lexus class
     */
    class Themelexus_Taxonomy {

        private array $taxonomies;

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct(array $taxonomies = []) {
            $this->taxonomies = $taxonomies;
            
            // $this->themelexus_register_taxs();
            add_action( 'init', [$this, 'themelexus_register_taxs'] );
        }

        /**
         * Register Some custom taxonomies
         *
         * @return void
         */
        public function themelexus_register_taxs() {
            $taxonomies = apply_filters('themelexus_add_taxonomies', $this->taxonomies);

            if (!empty($taxonomies)) {
                foreach ($taxonomies as $taxonomy => $data) {
                    if (empty($data['post_types'])) {
                        continue;
                    }

                    $post_types = $data['post_types'];
                    $args = $data['args'] ?? array();
                    self::register_taxs($taxonomy, $post_types, $args);
                }
            }    
        }

        /**
         * Register a custom taxonomy
         *
         * @param string $taxonomy
         * @param array $post_types
         * @param string $label
         * @return void
         */
        private static function register_taxs(string $taxonomy, array $post_types, array $args = []) {
            if (taxonomy_exists($taxonomy)) {
                return;
            }

            /**
             * Taxonomy: $taxonomy.
             * Args: $args.
             */

            $defaults = [
                "label" => esc_html__( "Category", 'themelexus-debug' ),
                "public" => true,
                "publicly_queryable" => true,
                "hierarchical" => true,
                "show_ui" => true,
                "show_in_menu" => true,
                "show_in_nav_menus" => true,
                "query_var" => true,
                "show_admin_column" => true,
                "show_in_rest" => false,
                "show_tagcloud" => false,
                "rest_base" => $taxonomy,
                "rest_controller_class" => "WP_REST_Terms_Controller",
                "rest_namespace" => "wp/v2",
                "show_in_quick_edit" => true,
                "sort" => false,
                "show_in_graphql" => false,
            ];

            // echo '<pre>'; print_r(wp_parse_args( $args, $defaults )); echo '</pre>'; die();

            register_taxonomy( $taxonomy, $post_types, apply_filters('themelexus_custom_args_taxonomy_'.$taxonomy, wp_parse_args( $args, $defaults )) );
        }
    }

    new Themelexus_Taxonomy();

endif;

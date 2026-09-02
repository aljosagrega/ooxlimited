<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Themelexus_CPT')) :

    /**
     * The CPT Theme Lexus class
     */
    class Themelexus_CPT
    {

        private array $post_types;

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct(array $post_types = [])
        {
            $this->post_types = $post_types;

            // $this->themelexus_register_cpts();
            add_action('init', [$this, 'themelexus_register_cpts']);
            add_action('admin_head', [$this, 'admin_column_thumbnail_style']);
        }

        /**
         * Register Some custom post types
         *
         * @return void
         */
        public function themelexus_register_cpts()
        {
            $post_types = apply_filters('themelexus_add_post_types', $this->post_types);

            if (!empty($post_types)) {
                foreach ($post_types as $post_type => $args) {
                    self::register_cpts($post_type, $args);

                    // Add image column
                    add_filter("manage_{$post_type}_posts_columns", [$this, 'add_thumbnail_column']);
                    add_filter("manage_edit-{$post_type}_sortable_columns", [$this, 'thumbnail_column_sortable']);
                    add_action("manage_{$post_type}_posts_custom_column", [$this, 'show_thumbnail_column'], 10, 2);
                }
            }
        }

        /**
         * Register a custom post type
         *
         * @param string $post_type
         * @param string $label
         * @return void
         */
        private static function register_cpts(string $post_type, array $args)
        {
            if (post_type_exists($post_type)) {
                return;
            }

            /**
             * Post Type: $post_type.
             * Data: $data.
             */

            $defaults = [
                "label" => 'Post type label',
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
                "query_var" => true,
                "menu_position" => 20,
                "menu_icon" => "dashicons-admin-post",
                "supports" => ["title", "editor", "thumbnail", "excerpt", "author"],
                "show_in_graphql" => false,
            ];

            $args = apply_filters('themeluxus_custom_args_pt' . $post_type, wp_parse_args($args, $defaults));

            register_post_type($post_type, $args);
        }

        public function add_thumbnail_column($columns)
        {
            $new = [];
            foreach ($columns as $key => $value) {
                if ($key === 'title') {
                    $new['lexus_cpt_thumbnail'] = __('Thumbnail', 'themelexus-debug');
                }
                $new[$key] = $value;
            }
            return $new;
        }
        public function show_thumbnail_column($column, $post_id)
        {
            if ($column === 'lexus_cpt_thumbnail') {
                $thumbnail = get_the_post_thumbnail($post_id, [60, 60]);
                echo $thumbnail ?: '—';
            }
        }
        public function thumbnail_column_sortable($columns)
        {
            return $columns;
        }
        public function admin_column_thumbnail_style()
        {
            echo '<style>
                .column-lexus_cpt_thumbnail {
                    width: 70px;
                    text-align: center;
                }
                .column-lexus_cpt_thumbnail img {
                    width: 60px;
                    height: auto;
                }
            </style>';
        }
    }

    new Themelexus_CPT();

endif;

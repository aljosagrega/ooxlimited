<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Omero_Init_Post_Type')) {

    /**
     * Custom Admin Field By CMB2
     */
    class Omero_Init_Post_Type
    {
        static $instance;

        public static function getInstance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof Omero_Init_Post_Type)) {
                self::$instance = new Omero_Init_Post_Type();
            }
            return self::$instance;
        }

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct()
        {
            $this->load();

            // Setup Post Type
            add_filter('themelexus_add_post_types', [$this, 'setup_post_types']);
            add_filter('themelexus_add_taxonomies', [$this, 'setup_taxonomies']);

            add_action('omero_add_customize_field_post_type_team', [$this, 'add_customize_field_post_type_team']);

            add_filter( 'manage_edit-game_platform_columns', [ $this, 'taxonomy_columns' ] );
            add_filter( 'manage_game_platform_custom_column', [ $this, 'taxonomy_columns_content' ], 10, 3 );
            add_filter( 'term_name', [ $this, 'custom_term_name_column' ], 31, 2 );
        }

        public function load()
        {
            require get_theme_file_path('inc/post-type/functions.php');
            $files_custom = glob(get_theme_file_path('/inc/post-type/functions/*.php'));
            foreach ($files_custom as $file) {
                if (file_exists($file)) {
                    require_once $file;
                }
            }

            require get_theme_file_path('inc/post-type/class-content.php');
            require get_theme_file_path('inc/post-type/class-content-game-setup.php');
        }

        /**
         * Init post types for theme
         *
         * @return void
         */
        public function setup_post_types($post_types)
        {
            $post_types = [
                'service' => [
                    'name_default' => __('Service', 'omero'),
                    'label' => omero_get_theme_option('service_label', __('Services', 'omero')),
                    'rewrite' => [
                        'slug' => omero_get_theme_option('service_slug', 'service'),
                    ],
                    'menu_icon' => 'dashicons-feedback',
                    'supports' => ["title", "editor", "thumbnail", "excerpt"],
                ],
                'team' => [
                    'name_default' => __('Team', 'omero'),
                    'label' => omero_get_theme_option('team_label', __('Teams', 'omero')),
                    'rewrite' => [
                        'slug' => omero_get_theme_option('team_slug', 'team'),
                    ],
                    'menu_icon' => 'dashicons-groups',
                ],
            ];

            return apply_filters('omero_add_custom_post_types', $post_types);
        }

        /**
         * Init taxonomies for theme
         *
         * @return void
         */
        public function setup_taxonomies($taxonomies)
        {
            $taxonomies = [
                'game_genre' => [
                    'name_default' => __('Game Genre', 'omero'),
                    'post_types' => [
                        'game',
                    ],
                    'args' => [
                        'labels' => [
                            "name" => omero_get_theme_option('game_genre_label', __("Genres", 'omero')),
                            "singular_name" => omero_get_theme_option('game_genre_single_label', __("Category", 'omero')),
                        ],
                        'rewrite' => [
                            'slug' => omero_get_theme_option('game_genre_slug', 'game-genre'),
                        ],
                        'hierarchical' => false,
                        'show_ui' => true,
                        'show_admin_column' => true,
                        'meta_box_cb' => 'post_categories_meta_box',
                    ]
                ],
                'game_platform' => [
                    'name_default' => __('Game Platform', 'omero'),
                    'post_types' => [
                        'game',
                    ],
                    'args' => [
                        'labels' => [
                            "name" => omero_get_theme_option('game_platform_label', __("Platforms", 'omero')),
                            "singular_name" => omero_get_theme_option('game_platform_single_label', __("Platform", 'omero')),
                        ],
                        'rewrite' => [
                            'slug' => omero_get_theme_option('game_platform_slug', 'game-platform'),
                        ],
                        'hierarchical' => false,
                        'show_ui' => true,
                        'show_admin_column' => true,
                        'meta_box_cb' => 'post_categories_meta_box',
                    ]
                ],
            ];

            return apply_filters('omero_add_custom_taxonomies', $taxonomies);
        }

        /**
         * Add setting fields in Custimize page for Game Settings
         *
         * @return void
         */
        public function add_customize_field_post_type_team($wp_customize)
        {
            if (omero_is_elementor_activated()) {
                $wp_customize->add_section('omero_team_single', array(
                    'title'      => esc_html__('Single Team', 'omero'),
                    'panel'      => 'omero_team',
                    'capability' => 'edit_theme_options',
                ));

                $wp_customize->add_setting('omero_options_team_bottom_block', array(
                    'type'              => 'option',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ));

                $wp_customize->add_control('omero_options_team_bottom_block', array(
                    'section'     => 'omero_team_single',
                    'label'       => esc_html__('Choose Bottom Template', 'omero'),
                    'type'        => 'select',
                    'description' => __('Block will take templates name prefix is "Team"', 'omero'),
                    'choices'     => Omero_Customize::get_block('Team'),
                ));
            }
        }

        public function taxonomy_columns( $columns ) {
            
            $columns = omero_array_insert_before($columns, 'name', 'logo', esc_html__( 'Logo', 'omero' ));

            return $columns;
		}

		public function taxonomy_columns_content( $column, $column_name, $term_id ) {
			if ( $column_name === 'logo' ) {
                $logo_url = get_term_meta( $term_id, '_logo', true );
				if (empty($logo_url)) {
                    $logo_image = wc_placeholder_img( [ '40', '40' ] );
                } else {
                    $logo_image = sprintf('<img src="%s" width="40" height="40" style="object-fit: contain;" />', $logo_url);    
                }

                return sprintf(
                    '<div style="width: 50px; height: 50px; background-color: #e5e5e5; display: flex; justify-content: center; align-items: center;">%s</div>',
                    $logo_image
                );
			}

			return $column;
		}

		public function custom_term_name_column( $name, $term ) {
            if (!is_admin()) {
                return $name;
            }

            $show_color = false;
            if (wp_doing_ajax() && isset($_REQUEST['screen']) && $_REQUEST['screen'] == 'edit-game_genre') {
                $show_color = true;
            } else {
                $current_screen = get_current_screen();
                if ($current_screen->base == 'edit-tags' && isset($current_screen->taxonomy) && $current_screen->taxonomy == 'game_genre') {
                    $show_color = true;
                }
            }

            if (isset($term->term_id) && $show_color) {
                $color = get_term_meta( $term->term_id, '_genre_color', true );
                if (!empty($color)) {
                    $name = sprintf('<span style="color: %s">%s</span>', esc_attr($color), $name);
                }
            }

			return $name;
		}

    }

    Omero_Init_Post_Type::getInstance();
}

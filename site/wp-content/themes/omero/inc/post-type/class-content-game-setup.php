<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Omero_Init_Post_Type_Game')) {

    /**
     * Custom Admin Field By CMB2
     */
    class Omero_Init_Post_Type_Game
    {
        static $instance;

        public static function getInstance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof Omero_Init_Post_Type_Game)) {
                self::$instance = new Omero_Init_Post_Type_Game();
            }
            return self::$instance;
        }

        /**
         * Store the Game Archive page ID.
         *
         * @var integer
         */
        private static $game_archive_page = 0;

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct()
        {
            $this->set_game_archive_page();

            add_action('omero_add_customize_field_post_type_game', [$this, 'add_customize_field_game_archive']);

            // Setup post type archive page
            add_action('pre_get_posts', [$this, 'setup_pre_get_post']);
            add_filter('template_include', [$this, 'template_loader']);
            add_action('lxdb_before_breadcrumb_pagenum', [$this, 'before_breadcrumb_pagenum'], 10, 2);
            add_filter('display_post_states', [$this, 'add_display_post_states'], 10, 2);
            add_filter('omero_add_custom_post_types', [$this, 'filter_register_post_type_args']);
            add_filter('omero_breadcrumb_page_title', [$this, 'replace_breadcrumb_page_title']);
        }

        private function set_game_archive_page()
        {
            self::$game_archive_page = omero_get_game_archive_page();
        }

        public function filter_register_post_type_args($post_types)
        {
            if (!isset($post_types['game'])) {
                return $post_types;
            }
            if (!empty(self::$game_archive_page)) {
                $post_types['game']['has_archive'] = false;
            }
            return $post_types;
        }

        public function replace_breadcrumb_page_title($title)
        {
            $page_id = self::$game_archive_page;
            if (!empty($page_id) && omero_is_game_archive_page($page_id)) {
                return get_the_title($page_id);
            }
            return $title;
        }

        /**
         * Add setting fields in Custimize page for Game Settings
         *
         * @return void
         */
        public function add_customize_field_game_archive($wp_customize)
        {
            $wp_customize->add_section('omero_game_archive', array(
                'title'      => esc_html__('Archive Game', 'omero'),
                'panel'      => 'omero_game',
                'capability' => 'edit_theme_options',
            ));

            $wp_customize->add_setting('omero_options_game_archive_page', array(
                'type'              => 'option',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_game_archive_page', array(
                'section'     => 'omero_game_archive',
                'label'       => esc_html__('Choose the Game Archive page', 'omero'),
                'type'        => 'select',
                'description' => sprintf(__('After select page, If error 404 appears, please update <a target="_blank" href="%s">the permalinks</a> in the Settings page', 'omero'), esc_url(admin_url('options-permalink.php'))),
                'choices'     => array_merge(['' => __('Default', 'omero')], omero_get_pages_slug_title_array()),
            ));

            $wp_customize->add_setting('omero_options_show_archive_game_header_content', array(
                'type'              => 'option',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('omero_options_show_archive_game_header_content', array(
                'section'     => 'omero_game_archive',
                'label'       => esc_html__('Hide/Show Header Header Content', 'omero'),
                'type'        => 'select',
                'description' => __('Display content in the header of Game Archive page', 'omero'),
                'choices'     => [
                    '' => __('Hide', 'omero'),
                    'yes' => __('Show', 'omero'),
                ],
            ));
        }

        /**
         * Setup Post type Archive page pre get post
         *
         */
        public function setup_pre_get_post($query)
        {
            if (is_admin() || !$query->is_main_query()) {
                return;
            }

            $page_id = self::$game_archive_page;
            if ($query->is_post_type_archive('game') || is_tax(['game_genre', 'game_platform'])) {
                $query->set('posts_per_page', 6);
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
            } elseif (!empty($page_id) && is_page($page_id) && $page_id == get_queried_object_id()) {
    
                $query->set('page_id', '');
                $query->set('pagename', '');
                $query->set('posts_per_page', 6);
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                $query->set('post_type', 'game');

                if (isset($query->query['paged'])) {
                    $query->set('paged', $query->query['paged']);
                }

                global $wp_post_types;

                $game_page = get_post($page_id);

                $wp_post_types['game']->ID         = $game_page->ID;
                $wp_post_types['game']->post_title = $game_page->post_title;
                $wp_post_types['game']->post_name  = $game_page->post_name;
                $wp_post_types['game']->post_type  = $game_page->post_type;
                $wp_post_types['game']->ancestors  = get_ancestors($game_page->ID, $game_page->post_type);

                $query->is_page              = false;
                $query->is_singular          = false;
                $query->is_archive           = true;
                $query->is_post_type_archive = true;

                add_filter('post_type_archive_title', function () use ($page_id) {
                    return get_the_title($page_id);
                }, 5);
            }
        }

        /**
         * Setup template loader
         *
         */
        public function template_loader($template)
        {
            $page_id = self::$game_archive_page;
            if (!empty($page_id) && omero_is_game_archive_page($page_id)) {
                $archive_template = locate_template('archive-game.php');
                if ($archive_template) {
                    return $archive_template;
                }
            }

            return $template;
        }

        public function before_breadcrumb_pagenum(&$breadcrumb, $instance)
        {
            $archive_page_id = self::$game_archive_page;
            $all_link = $archive_page_id ? get_permalink( $archive_page_id ) : get_post_type_archive_link('game');
            $all_title = $archive_page_id ? get_the_title( $archive_page_id ) : esc_html__('Game', 'omero');
            if (is_singular('game')) {
                $args = !$archive_page_id ? $all_title : [
                    'title' => esc_html($all_title),
                    'link' => esc_url($all_link),
                ];
                $breadcrumb = omero_array_insert_before(
                    $breadcrumb, 
                    'single_game', 
                    'archive_game_all', 
                    $instance->template($args)
                );
            } else {
                if (is_post_type_archive('game') && isset($breadcrumb['archive_game'])) {
                    if (omero_is_game_archive_page($archive_page_id)) {
                        $breadcrumb['archive_game'] = $instance->template([
                            'link' => get_the_permalink($archive_page_id),
                            'title' => get_the_title($archive_page_id),
                        ]);
                    }
                } elseif (is_tax('game_genre') && isset($breadcrumb['archive_game_genre'])) {
                    $title = esc_html__('Game Genre', 'omero');
                    $args = !$archive_page_id ? $title : [
                        'title' => $title,
                        'link' => esc_url($all_link),
                    ];
                    $breadcrumb = omero_array_insert_before(
                        $breadcrumb, 
                        'archive_game_genre', 
                        'archive_game_genre_all', 
                        $instance->template($args)
                    );
                } elseif (is_tax('game_platform') && isset($breadcrumb['archive_game_platform'])) {
                    $title = esc_html__('Game Platform', 'omero');
                    $args = !$archive_page_id ? $title : [
                        'title' => $title,
                        'link' => esc_url($all_link),
                    ];
                    $breadcrumb = omero_array_insert_before(
                        $breadcrumb, 
                        'archive_game_platform', 
                        'archive_game_platform_all', 
                        $instance->template($args)
                    );
                }
            }
        }

        /**
         * Add a post display state for special WC pages in the page list table.
         *
         * @param array   $post_states An array of post display states.
         * @param WP_Post $post        The current post object.
         */
        public function add_display_post_states($post_states, $post)
        {
            if (self::$game_archive_page === $post->ID) {
                $post_states['omero_archive_game'] = __('Archive Game Page', 'omero');
            }

            return $post_states;
        }
    }

    Omero_Init_Post_Type_Game::getInstance();
}

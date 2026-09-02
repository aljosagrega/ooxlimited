<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Omero_CMB2_Options_Admin')) :

    /**
     * Custom Admin Field By CMB2
     */
    class Omero_CMB2_Options_Admin
    {

        /**
         * Setup class.
         *
         * @since 1.0
         */
        public function __construct()
        {
            add_action('cmb2_init', [$this, 'add_game_metabox']);
            add_action('cmb2_init', [$this, 'add_team_metabox']);
            add_action('cmb2_init', [$this, 'add_game_platform_metabox']);
            add_action('cmb2_init', [$this, 'add_game_genre_metabox']);
            add_action('cmb2_init', [$this, 'add_post_team_writer_metabox']);
        }

        public function add_game_metabox()
        {

            $objs = apply_filters('omero_meta_apply_for_game', ['game']);

            $cmb = new_cmb2_box(array(
                'id'           => 'game_meta',
                'title'        => __('Game Detail', 'omero'),
                'object_types' => $objs,
                'context'      => 'normal',
                'priority'     => 'high',
            ));

            do_action('omero_before_game_meta', $cmb);

            $cmb->add_field(array(
                'name' => __('Studio Logo', 'omero'),
                'desc' => __("Upload The Logo of Game's Studio (Allow svg/png image)", 'omero'),
                'id'   => '_logo',
                'type' => 'file',
                'options' => array(
                    'url' => false,
                ),
                'query_args' => array(
                    'type' => array(
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/svg+xml',
                    ),
                ),
                'preview_size' => 'thumbnail',
            ));

            $cmb->add_field(array(
                'name' => __('Website:', 'omero'),
                'id' => '_game_website',
                'type' => 'text_url',
            ));
            $cmb->add_field(array(
                'name' => __('Number of Players:', 'omero'),
                'id' => '_game_number_of_players',
                'type' => 'text',
            ));
            $cmb->add_field(array(
                'name' => __('Age Rating:', 'omero'),
                'id' => '_game_age_rating',
                'type' => 'text',
            ));
            $cmb->add_field(array(
                'name' => __('Release Date:', 'omero'),
                'id' => '_game_date',
                'type' => 'text_date',
            ));

            do_action('omero_after_game_meta', $cmb);
        }

        public function add_team_metabox()
        {

            $objs = apply_filters('omero_cmb2_meta_apply_for_team', ['team']);

            $cmb = new_cmb2_box(array(
                'id'           => '_team_meta',
                'title'        => __('Team Meta', 'omero'),
                'object_types' => $objs,
                'context'      => 'normal',
                'priority'     => 'high',
            ));

            do_action('omero_cmb2_before_team_meta', $cmb);

            // Position
            $cmb->add_field(array(
                'name' => __('Position', 'omero'),
                'id'   => '_team_position',
                'type' => 'text',
            ));

            // Skills (repeater)
            $skills_group = $cmb->add_field(array(
                'id'      => '_team_skills_group',
                'type'    => 'group',
                'options' => array(
                    'group_title'   => __('Skill {#}', 'omero'),
                    'add_button'    => __('Add Another Skill', 'omero'),
                    'remove_button' => __('Remove Skill', 'omero'),
                    'sortable'      => true,
                    'closed'        => true,
                ),
            ));
            $cmb->add_group_field($skills_group, array(
                'name' => __('Skill Title', 'omero'),
                'id'   => 'title',
                'type' => 'text',
            ));

            // Programs (repeater)
            $programs_group = $cmb->add_field(array(
                'id'      => '_team_programs_group',
                'type'    => 'group',
                'options' => array(
                    'group_title'   => __('Program {#}', 'omero'),
                    'add_button'    => __('Add Another Program', 'omero'),
                    'remove_button' => __('Remove Program', 'omero'),
                    'sortable'      => true,
                    'closed'        => true,
                ),
            ));
            $cmb->add_group_field($programs_group, array(
                'name' => __('Program Title', 'omero'),
                'id'   => 'title',
                'type' => 'text',
            ));

            // Featured Projects (repeater)
            $projects_group = $cmb->add_field(array(
                'id'      => '_team_projects_group',
                'type'    => 'group',
                'options' => array(
                    'group_title'   => __('Project {#}', 'omero'),
                    'add_button'    => __('Add Another Project', 'omero'),
                    'remove_button' => __('Remove Project', 'omero'),
                    'sortable'      => true,
                    'closed'        => true,
                ),
            ));
            $cmb->add_group_field($projects_group, array(
                'name' => __('Project Title', 'omero'),
                'id'   => 'title',
                'type' => 'text',
            ));

            // Socials (non-repeatable group)
            $socials_group = $cmb->add_field(array(
                'id'         => '_team_socials_group',
                'type'       => 'group',
                'repeatable' => false,
                'options'    => array(
                    'group_title' => __('Socials', 'omero'),
                    'closed'      => false,
                ),
            ));
            $cmb->add_group_field($socials_group, array(
                'name' => __('Facebook', 'omero'),
                'id'   => 'facebook',
                'type' => 'text_url',
            ));
            $cmb->add_group_field($socials_group, array(
                'name' => __('Instagram', 'omero'),
                'id'   => 'instagram',
                'type' => 'text_url',
            ));
            $cmb->add_group_field($socials_group, array(
                'name' => __('LinkedIn', 'omero'),
                'id'   => 'linkedin',
                'type' => 'text_url',
            ));
            $cmb->add_group_field($socials_group, array(
                'name' => __('Email', 'omero'),
                'id'   => 'email',
                'type' => 'text_email',
            ));

            // Q&A (repeater)
            $qa_group = $cmb->add_field(array(
                'id'      => '_team_qa_group',
                'type'    => 'group',
                'options' => array(
                    'group_title'   => __('Q&A {#}', 'omero'),
                    'add_button'    => __('Add Another Q&A', 'omero'),
                    'remove_button' => __('Remove Q&A', 'omero'),
                    'sortable'      => true,
                    'closed'        => true,
                ),
            ));
            $cmb->add_group_field($qa_group, array(
                'name' => __('Question', 'omero'),
                'id'   => 'question',
                'type' => 'text',
            ));
            $cmb->add_group_field($qa_group, array(
                'name' => __('Answer', 'omero'),
                'id'   => 'answer',
                'type' => 'wysiwyg',
                'options' => array(
                    'textarea_rows' => 5,
                    'media_buttons' => false,
                ),
            ));

            do_action('omero_cmb2_after_team_meta', $cmb);
        }

        public function add_game_platform_metabox()
        {

            $objs = apply_filters('omero_meta_apply_for_game_platform', ['game_platform']);

            $cmb = new_cmb2_box(array(
                'id'           => '_platform_meta_fields',
                'title'        => __('Platform Meta', 'omero'),
                'object_types' => array('term'),
                'taxonomies'   => $objs,
                'new_term_section' => true,
            ));

            do_action('omero_before_game_platform_meta', $cmb);

            $cmb->add_field(array(
                'name' => __('Logo', 'omero'),
                'desc' => __('Upload The Logo for this Platform.', 'omero'),
                'id'   => '_logo',
                'type' => 'file',
                'options' => array(
                    'url' => false,
                ),
                'query_args' => array(
                    'type' => array(
                        // 'image/jpeg',
                        // 'image/jpg',
                        'image/png',
                        'image/svg+xml',
                    ),
                ),
                'preview_size' => 'thumbnail',
            ));

            do_action('omero_after_game_platform_meta', $cmb);
        }

        public function add_game_genre_metabox()
        {

            $objs = apply_filters('omero_meta_apply_for_game_genre', ['game_genre']);

            $cmb = new_cmb2_box(array(
                'id'           => '_genre_meta_fields',
                'title'        => __('Genre Meta', 'omero'),
                'object_types' => array('term'),
                'taxonomies'   => $objs,
                'new_term_section' => true,
            ));

            do_action('omero_before_game_genre_meta', $cmb);

            $cmb->add_field(array(
                'name'    => __('Genre Color', 'omero'),
                'id'      => '_genre_color',
                'type'    => 'colorpicker',
                'default' => '#8562DE',
                'options' => array(
                	'alpha' => true,
                ),
            ));

            do_action('omero_after_game_genre_meta', $cmb);
        }

        /**
         * Post sidebar: assign a Team CPT member as the credited writer (blog grids, etc.).
         */
        public function add_post_team_writer_metabox()
        {
            if (!function_exists('new_cmb2_box')) {
                return;
            }
            if (!post_type_exists('team')) {
                return;
            }

            $options = array();
            $teams   = get_posts(
                array(
                    'post_type'              => 'team',
                    'post_status'            => 'publish',
                    'posts_per_page'         => -1,
                    'orderby'                => 'title',
                    'order'                  => 'ASC',
                    'no_found_rows'          => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                )
            );
            foreach ($teams as $team_post) {
                $options[ $team_post->ID ] = $team_post->post_title;
            }

            $cmb = new_cmb2_box(
                array(
                    'id'           => 'omero_post_team_writer',
                    'title'        => __('Post writer', 'omero'),
                    'object_types' => array('post'),
                    'context'      => 'side',
                    'priority'     => 'default',
                )
            );

            $cmb->add_field(
                array(
                    'name'             => __('Written by (team)', 'omero'),
                    'desc'             => __('Shown on blog layouts instead of the WordPress author when set.', 'omero'),
                    'id'               => '_post_team_writer_id',
                    'type'             => 'select',
                    'show_option_none' => __('— WordPress author —', 'omero'),
                    'options'          => $options,
                )
            );
        }
    }

endif;

return new Omero_CMB2_Options_Admin();

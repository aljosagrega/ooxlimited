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

            $cmb->add_field(array(
                'name' => __('Job position', 'omero'),
                'id' => '_team_job',
                'type' => 'text',
            ));

            $cmb->add_field(array(
                'name' => __('Experience', 'omero'),
                'id' => '_team_experience',
                'type' => 'text',
            ));

            $cmb->add_field(array(
                'name' => __('Responsibility', 'omero'),
                'id' => '_team_responsibility',
                'type' => 'text',
            ));

            $cmb->add_field(array(
                'name' => __('Email', 'omero'),
                'id' => '_team_email',
                'type' => 'text_email',
            ));

            $group_field_id = $cmb->add_field(array(
                'id'          => '_omero_socials_group',
                'type'        => 'group',
                'repeatable'  => false,
                'options'     => array(
                    'closed'         => false,
                    'group_title' => __('Socials', 'omero')
                ),
            ));
            $cmb->add_group_field($group_field_id, array(
                'name' => __('Facebook', 'omero'),
                'id'   => 'social_fb',
                'type' => 'text_url',
            ));
            $cmb->add_group_field($group_field_id, array(
                'name' => __('X', 'omero'),
                'id'   => 'social_x',
                'type' => 'text_url',
            ));
            $cmb->add_group_field($group_field_id, array(
                'name' => __('Instagram', 'omero'),
                'id'   => 'social_ig',
                'type' => 'text_url',
            ));

            do_action('omero_cmb2_team_socials', $group_field_id, $cmb);


            $cmb->add_field(array(
                'name' => __('Professional Skills', 'omero'),
                'id'   => '_team_professional_title',
                'type' => 'title',
            ));

            $cmb->add_field(array(
                'name' => __('Professional Skills Description', 'omero'),
                'id' => '_team_skill_description',
                'type' => 'textarea_small'
            ));

            $group_field_id = $cmb->add_field(array(
                'id'          => '_team_skills_group',
                'type'        => 'group',
                // 'repeatable'  => false, // use false if you want non-repeatable group
                'options'     => array(
                    'group_title'       => __('Skill {#}', 'omero'),
                    'add_button'        => __('Add Another Skill', 'omero'),
                    'remove_button'     => __('Remove Skill', 'omero'),
                    'sortable'          => true,
                    'closed'         => false,
                ),
            ));
            $cmb->add_group_field($group_field_id, array(
                'name' => 'Skill Title',
                'id'   => 'title',
                'type' => 'text',
            ));
            $cmb->add_group_field($group_field_id, array(
                'name' => 'Skill Level',
                'id'   => 'level',
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'pattern' => '\d*',
                    'min' => 0,
                    'max' => 100,
                ),
                'sanitization_cb' => 'absint',
                'escape_cb'       => 'absint',
            ));

            do_action('omero_team_skills_group', $group_field_id, $cmb);

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
    }

endif;

return new Omero_CMB2_Options_Admin();

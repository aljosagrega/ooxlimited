<?php
add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_setting('lexus_options_mouse_cursor', array(
        'type'              => 'option',
        'default'           => 'yes',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('lexus_options_mouse_cursor', array(
        'section' => 'title_tagline',
        'label'   => esc_html__('Show Mouse Cursor', 'themelexus-debug'),
        'type'    => 'select',
        'choices' => [
            'no' => esc_html__('No', 'themelexus-debug'),
            'yes' => esc_html__('Yes', 'themelexus-debug'),
        ]
    ));
}, 20);

add_action('wp_enqueue_scripts', function () {
    $mouse_cursor = lexus_get_theme_option('mouse_cursor', 'no');
    if ($mouse_cursor === 'no' || wp_is_mobile()) {
        wp_dequeue_script('antra-mouse-cursor');
        wp_deregister_script('antra-mouse-cursor');
    }
}, 20);

add_action('elementor/widget/before_render_content', function ($widget) {
    if ($widget->get_name() !== 'antra-project-video') {
        return;
    }

    $settings = $widget->get_settings_for_display();
    $project_show = empty($settings['project_show']) ? 'single' : $settings['project_show'];
    if ($project_show == 'single') {
        if (is_singular('project') || get_post_type() == 'project') {
            $project_id = get_the_ID();
        }
    } else {
        if (!empty($settings['choose_project'])) {
            $project_id = absint($settings['choose_project']);
        }
    }
    $project_id = $project_id ?? antra_get_default_project();

    $video_bg_img = get_post_meta($project_id, '_project_video_background', true);
    if (empty($video_bg_img)) {
        return;
    }

    ?>
    <style id="antra-elementor-project-video-<?php echo esc_attr($widget->get_id()) ?>">
        .elementor-widget-antra-project-video.elementor-element-<?php echo esc_html($widget->get_id()) ?> .elementor-widget-container {
            background: url(<?php echo esc_url($video_bg_img) ?>) no-repeat center /cover;
        }
    </style>
    <?php

}, 10);

add_action('antra_after_project_meta', function ($cmb) {
    $cmb->remove_field('_project_video');

    $cmb->add_field(array(
        'name' => __('Video Youtube', 'antra'),
        'id'   => '_project_video',
        'type' => 'oembed',
    ));

    $cmb->add_field(array(
        'name' => __('Image Video Background', 'antra'),
        'id'   => '_project_video_background',
        'type'    => 'file',
        'query_args' => array('type' => 'image'),
        'preview_size' => 'large',
    ));
}, 10);

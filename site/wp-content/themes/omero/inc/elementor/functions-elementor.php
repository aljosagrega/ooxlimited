<?php

if (!function_exists('omero_elementor_parse_text_editor')) {
    function omero_elementor_parse_text_editor($content, $obj) {
        $content = apply_filters('widget_text', $content, $obj->get_settings());

        $content = shortcode_unautop($content);
        $content = do_shortcode($content);
        $content = wptexturize($content);

        if ($GLOBALS['wp_embed'] instanceof \WP_Embed) {
            $content = $GLOBALS['wp_embed']->autoembed($content);
        }

        return $content;
    }
}

if (!function_exists('omero_elementor_get_strftime')) {
    function omero_elementor_get_strftime($instance, $obj) {
        $string = '';
        if ($instance['show_days']) {
            $string .= $obj->render_countdown_item($instance, 'label_days', 'days', 'elementor-countdown-days');
        }
        if ($instance['show_hours']) {
            $string .= $obj->render_countdown_item($instance, 'label_hours', 'hours', 'elementor-countdown-hours');
        }
        if ($instance['show_minutes']) {
            $string .= $obj->render_countdown_item($instance, 'label_minutes', 'minutes', 'elementor-countdown-minutes');
        }
        if ($instance['show_seconds']) {
            $string .= $obj->render_countdown_item($instance, 'label_seconds', 'seconds', 'elementor-countdown-seconds');
        }

        return $string;
    }
}

if (!function_exists('omero_elementor_breakpoints')) {
    function omero_elementor_breakpoints() {
        $container_width = \Elementor\Plugin::$instance->kits_manager->get_active_kit()->get_settings_for_display('container_width');
        if (!empty($container_width['size']) && !empty($container_width['unit'])) {
            $var = '.col-full{max-width:' . $container_width['size'] . $container_width['unit'] . '}';
            wp_add_inline_style('omero-style', $var);
        }
    }
}

function omero_get_hotspots() {
    global $post;

    $options[''] = esc_html__('Select Hotspots', 'omero');
    if (!omero_is_elementor_activated()) {
        return;
    }
    $args = array(
        'post_type'      => 'elementor_library',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        's'              => 'Hotspots',
        'order'          => 'ASC',
    );

    $query = new WP_Query($args);
    while ($query->have_posts()) {
        $query->the_post();
        $options[$post->post_name] = $post->post_title;
    }
    wp_reset_postdata();
    return $options;
}

function omero_get_file_contents($path) {
    if (is_file($path)) {
        $prifix = 'file_get'.'_contents';
        return $prifix($path);
    }

    return false;
}

function omero_get_icon_svg($path, $color = '', $width = '') {
    $content = omero_get_file_contents($path);
    if ($content) {
        $re = '/<svg(([^\n]*\n)+)<\/svg>/';
        preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);
        if (count($matches) > 0) {
            $content = $matches[0][0];
            $css     = '';
            if ($color) {
                $content = preg_replace('/stroke="[^"]*"/', 'stroke="' . $color . '"', $content);
                $css     .= 'fill:' . $color . ';';
            }
            if ($width) {
                $css .= 'width:' . $width . '; height: auto;';
            }
            $content = preg_replace("/(<svg[^>]*)(style=(\"|')([^(\"|')]*)('|\"))/m", '$1 style="' . $css . '$4"', $content);
        }
    }

    return $content;
}

function omero_update_selector_group_control(object $element, $group_id, array $selectors) {
    $controls_manager = \Elementor\Plugin::$instance->controls_manager;
    $typographyGroup = $controls_manager->get_control_groups($group_id);

    foreach ($typographyGroup->get_fields() as $field_key => $field) {

        $control_id = "{$group_id}_{$field_key}";
        $old_control_data = $controls_manager->get_control_from_stack($element->get_unique_name(), $control_id);
        $selector_value = ! empty( $old_control_data['selector_value'] ) ? $old_control_data['selector_value'] : str_replace( '_', '-', $field_key ) . ': {{VALUE}};';

        $new_args = [
            'selectors'  => []
        ];

        foreach ($selectors as $item) {
            $new_args['selectors'][$item] = $selector_value;
        }

        if (isset($old_control_data['responsive']) && $old_control_data['responsive']) {
            $element->update_responsive_control($control_id, $new_args);
        } else {
            $element->update_control($control_id, $new_args);
        }        
    }
}

if (!function_exists('omero_get_default_post')) {
    function omero_get_default_post($post_type = 'post') {
        $args = [
            'numberposts' => 1,
            'post_type'   => $post_type,
            'fields' => 'ids',
            'orderby' => 'date',
            'order' => 'ASC'
        ];
        $post_id = get_posts($args);
        if(!empty($post_id) && isset($post_id[0])){
            return $post_id[0];
        }else{
            return false;
        }

    }
}


if (!function_exists('omero_get_elementor_block')) {
    /**
     * Get Options with key as Block Slug
     *
     * @param string $kw
     * @return void
     */
    function omero_get_elementor_block(string $kw = '')
    {
        global $post;

        $options[''] = esc_html__('Select Block', 'omero');
        if (!omero_is_elementor_activated()) {
            return;
        }
        $args = array(
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'          => 'publish',
        );

        if (!empty($kw)) {
            $args['s'] = sanitize_title($kw);
        }

        $query1 = new WP_Query($args);
        while ($query1->have_posts()) {
            $query1->the_post();
            if (!empty($post->post_name)) {
                $options[$post->post_name] = $post->post_title;
            }
        }

        wp_reset_postdata();
        return $options;
    }
}

if (!function_exists('omero_render_elementor_block')) {
    /**
     * Render Elementor Template
     *
     * @param integer|string $template
     * @return void
     */
    function omero_render_elementor_block($template)
    {
        if (empty($template)) {
            return;
        }

        if (is_int($template)) {
            $id_template = $template;
        } else {
            $template_obj = omero_get_page_by_slug(sanitize_title($template), 'elementor_library');
            if (!is_object($template_obj) || !isset($template_obj->ID)) {
                return;
            }
            $id_template = $template_obj->ID;    
        }

        echo Elementor\Plugin::instance()->frontend->get_builder_content_for_display($id_template);
    }
}
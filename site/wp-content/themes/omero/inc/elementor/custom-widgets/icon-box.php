<?php

use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Icon_Box;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

add_action('elementor/element/icon-box/section_icon/before_section_end', function ($element, $args) {
    $element->add_control(
        'effect_icon_line',
        [
            'label' => esc_html__('Effter Box', 'omero'),
            'type'         => Controls_Manager::SWITCHER,
            'prefix_class' => 'show-effect-icon-line-',
        ]
    );
}, 10, 2);

add_action('elementor/element/icon-box/section_icon/before_section_end', function ($element, $args) {
    $element->add_control(
        'effect_icon_gradient',
        [
            'label' => esc_html__('Effter Icon Gradient', 'omero'),
            'type'         => Controls_Manager::SWITCHER,
            'prefix_class' => 'show-effect-icon-gradient-',
        ]
    );
}, 10, 2);

add_action('elementor/element/icon-box/section_style_box/before_section_end', function ($element, $args) {
    $element->add_control(
        'icon_box_background_color',
        [
            'label'     => __('Background Color', 'omero'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [
                '{{WRAPPER}} .elementor-icon-box-wrapper'=> 'background-color: {{VALUE}}; fill:{{VALUE}}',
            ],
        ]
    );

    $element->add_responsive_control(
        'icon_box_padding',
        [
            'label'      => esc_html__('Padding', 'omero'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors'  => [
                '{{WRAPPER}} .elementor-icon-box-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $element->add_responsive_control(
        'icon_box_border_radius',
        [
            'label'      => esc_html__('Border Radius', 'omero'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => [
                '{{WRAPPER}} .elementor-icon-box-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

}, 10, 2);

add_action('elementor/element/icon-box/section_style_content/before_section_end', function ($element, $args) {
    $element->add_responsive_control(
        'icon_box_margin',
        [
            'label'      => esc_html__('Margin', 'omero'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors'  => [
                '{{WRAPPER}} .elementor-icon-box-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

}, 10, 2);
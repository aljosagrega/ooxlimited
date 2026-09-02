<?php

use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Icon_Box;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

add_action('elementor/element/icon-list/section_icon_style/before_section_end', function ($element, $args) {
     $element->add_control(
        'icon_list_background_color',
        [
            'label'     => __('Background Color', 'omero'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [
                '{{WRAPPER}} .elementor-icon-list-icon i, {{WRAPPER}} .elementor-icon-list-icon svg'=> 'background-color: {{VALUE}}; fill:{{VALUE}}',
            ],
        ]
    );

    $element->add_responsive_control(
        'icon_list_padding',
        [
            'label'      => esc_html__('Icon Padding', 'omero'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors'  => [
                '{{WRAPPER}} .elementor-icon-list-icon i, {{WRAPPER}} .elementor-icon-list-icon svg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $element->add_responsive_control(
        'icon_list_border_radius',
        [
            'label'      => esc_html__('Border Radius', 'omero'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => [
                '{{WRAPPER}} .elementor-icon-list-icon i, {{WRAPPER}} .elementor-icon-list-icon svg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $element->add_responsive_control(
        'icon_list_rotate',
        [
            'label' => esc_html__( 'Rotate', 'omero' ),
            'type' => Controls_Manager::SLIDER,
            'size_units' => [ 'deg', 'rad', 'turn' ],
            'range' => [
                'deg' => [
                    'min' => 0,
                    'max' => 360,
                ],
            ],
            'selectors' => [
               '{{WRAPPER}} .elementor-icon-list-icon i, {{WRAPPER}} .elementor-icon-list-icon svg' => 'transform: rotate({{SIZE}}{{UNIT}});',
            ],
        ]
    );
}, 10, 2);

add_action( 'elementor/element/icon-list/section_icon_list/before_section_end', function ( $element, $args ) {
    $element->add_control(
        'theme_style',
        [
            'label'     => esc_html__( 'Theme style', 'omero' ),
            'type'      => Controls_Manager::SWITCHER,
            'default' => '',
            'prefix_class'	=> 'omero-theme-style-'
        ]
    );

}, 10, 2 );
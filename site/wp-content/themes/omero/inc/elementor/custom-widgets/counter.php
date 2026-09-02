<?php
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;

add_action('elementor/element/counter/section_counter/before_section_end', function ($element, $args) {

    $element->add_responsive_control(
        'position_title',
        [
            'label'        => __('Alignment Title', 'omero'),
            'type'         => Controls_Manager::CHOOSE,
            'options'      => [
                'left' => [
                    'title' => __('Left', 'omero'),
                    'icon'  => 'eicon-text-align-left',
                ],
                'center'     => [
                    'title' => __('Center', 'omero'),
                    'icon'  => 'eicon-text-align-center',
                ],
                'right'   => [
                    'title' => __('Right', 'omero'),
                    'icon'  => 'eicon-text-align-right',
                ]
            ],
            'toggle'       => false,
            'prefix_class' => 'elementor-position-',
            'default'      => 'center',
            'selectors'    => [
                '{{WRAPPER}} .elementor-counter' => 'text-align: {{VALUE}}',
            ],
        ]
    );

    $element->add_control(
        'color_gradient',
        [
            'label' => esc_html__('Color Gradient', 'omero'),
            'type'         => Controls_Manager::SWITCHER,
            'prefix_class' => 'color-gradient-',
        ]
    );

}, 10, 2);

add_action('elementor/element/counter/section_number/before_section_end', function ($element, $args) {

    $element->add_control(
        'prefix_color',
        [
            'label' => esc_html__( 'Color Prefix', 'omero' ),
            'type' => Controls_Manager::COLOR,
            'default' => '',
            'selectors' => [
                '{{WRAPPER}} .elementor-counter-number-prefix' => 'color: {{VALUE}};',
            ],
        ],
    );

    $element->add_control(
        'suffix_color',
        [
            'label' => esc_html__( 'Color Suffix', 'omero' ),
            'type' => Controls_Manager::COLOR,
            'default' => '',
            'selectors' => [
                '{{WRAPPER}} .elementor-counter-number-suffix' => 'color: {{VALUE}};',
            ],
        ],
    );

}, 10, 2);

add_action('elementor/element/counter/section_title/before_section_end', function ($element, $args) {
    $element->add_group_control(
        Group_Control_Border::get_type(),
        [
            'name'        => 'wrapper_border',
            'placeholder' => '1px',
            'default'     => '1px',
            'selector'    => '{{WRAPPER}} .elementor-counter-title',
        ]
    );
    $element->add_responsive_control(
        'title_margin',
        [
            'label' => esc_html__( 'Padding', 'omero' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors' => [
                '{{WRAPPER}} .elementor-counter-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
}, 10, 2);
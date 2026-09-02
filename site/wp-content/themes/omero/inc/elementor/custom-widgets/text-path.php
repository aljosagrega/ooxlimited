<?php
use Elementor\Controls_Manager;

add_action('elementor/element/text-path/section_style_text_path/before_section_end', function ($element, $args) {

    $element->add_responsive_control(
        'min_height',
        [
            'label' => esc_html__( 'Min Height', 'omero' ),
            'type' => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%', 'em', 'rem' ],
            'range' => [
                '%' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 10,
                ],
                'px' => [
                    'max' => 800,
                    'step' => 50,
                ],
            ],
            'selectors' => [
                '{{WRAPPER}}' => 'min-height: {{SIZE}}{{UNIT}};',
            ],
        ],
        [
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'size',
            ]
        ]
    );
}, 10, 2);
<?php
// Button
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Includes\Widgets\Traits\Button_Trait;
use Elementor\Plugin;

add_action('elementor/element/button/section_button/before_section_end', function ($element, $args) {
    $element->update_control(
        'size',
        [
            'label' => esc_html__( 'Size', 'omero' ),
            'type' => Controls_Manager::SELECT,
            'default' => 'md',
            'options' => (new class { use Button_Trait; })::get_button_sizes(),
            'style_transfer' => true,
            'condition' => false
        ]
    );

    $element->update_control(
        'selected_icon',
        [
            'label' => esc_html__( 'Icon', 'omero' ),
            'type' => Controls_Manager::ICONS,
            'fa4compatibility' => 'icon',
            'skin' => 'inline',
            'label_block' => false,
            'condition' => false,
            'icon_exclude_inline_options' => [],
            'default' => [
                'value' => 'fas fa-arrow-right',
                'library' => 'fa-solid',
            ],
        ]
    );

}, 10, 2);


add_action('elementor/element/button/section_button/after_section_end', function ($element, $args) {
    $element->update_control(
        'button_type',
        [
            'label'        => esc_html__('Type', 'omero'),
            'type'         => Controls_Manager::SELECT,
            'default'      => 'default',
            'options'      => [
                'default'  => esc_html__('Default', 'omero'),
                'secondary'=> esc_html__('Secondary', 'omero'),
                'outline'  => esc_html__('OutLine', 'omero'),
                'info'     => esc_html__('Info', 'omero'),
                'success'  => esc_html__('Success', 'omero'),
                'warning'  => esc_html__('Warning', 'omero'),
                'danger'   => esc_html__('Danger', 'omero'),
                'link'     => esc_html__('Link', 'omero'),
            ],
            'prefix_class' => 'elementor-button-',
        ]
    );

}, 10, 2);

add_action( 'elementor/element/button/section_style/after_section_end', function ($element, $args ) {

    $element->update_control(
        'background_color',
        [
            'global' => [
                'default' => '',
            ],
            'selectors' => [
                '{{WRAPPER}} .elementor-button' => ' background-color: {{VALUE}};',
            ],
        ]
    );

    omero_update_selector_group_control($element, 'typography', [
        '{{WRAPPER}} .elementor-button.omero-button-effect .hover-text',
        '{{WRAPPER}} .elementor-button:not(.omero-button-effect)'
    ]);

}, 10, 2 );

add_action('elementor/element/button/section_style/before_section_end', function ($element, $args) {

    $element->add_control(
        'icon_button_size',
        [
            'label' => esc_html__('Icon Size', 'omero'),
            'type' => Controls_Manager::SLIDER,
            'range' => [
                'px' => [
                    'min' => 6,
                    'max' => 300,
                ],
            ],
            'selectors' => [
                '{{WRAPPER}} .elementor-button .elementor-button-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .elementor-button .elementor-button-icon'   => 'display: flex; align-items: center;',
            ],
            'condition' => [
                'selected_icon[value]!' => '',
            ],
        ]
    );

    $element->add_control(
        'button_icon_color',
        [
            'label'     => esc_html__('Icon Color', 'omero'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [
                '{{WRAPPER}} .elementor-button .elementor-button-icon i' => 'fill: {{VALUE}}; color: {{VALUE}};',
                '{{WRAPPER}}.elementor-button-link .elementor-button:after' => 'color: {{VALUE}};',
                '{{WRAPPER}}.elementor-button-link .elementor-button:hover:before' => 'background-color: {{VALUE}};',
            ],

        ]
    );

    $element->add_control(
        'button_icon_color_hover',
        [
            'label'     => esc_html__('Icon Color Hover', 'omero'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [
                '{{WRAPPER}} .elementor-button:hover .elementor-button-icon i' => 'fill: {{VALUE}}; color: {{VALUE}};',
                '{{WRAPPER}} .elementor-button:focus .elementor-button-icon i' => 'fill: {{VALUE}}; color: {{VALUE}};',
                '{{WRAPPER}}.elementor-button-link .elementor-button:hover:after' => 'color: {{VALUE}};',
                '{{WRAPPER}}.elementor-button-link .elementor-button:hover:before' => 'background-color: {{VALUE}};',
            ],

        ]
    );
}, 10, 2);

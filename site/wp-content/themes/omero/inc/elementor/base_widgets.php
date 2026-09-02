<?php

namespace Omero\Elementor;

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use Elementor\Repeater;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

abstract class Omero_Base_Widgets extends Widget_Base {
    /**
     * Register column widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function get_controls_column($condition = false, $default = 4, $condition_column = false) {
        $column = range(1, 10);
        $column = array_combine($column, $column);

        $args_section = [
            'label' => esc_html__('Column Options', 'omero')
        ];

        if ($condition && !$condition_column) {
            $args_section['condition'] = $condition;
        }
        $this->start_controls_section(
            'section_column_options',
            $args_section
        );

        $args_column = [
            'label'              => esc_html__('Columns', 'omero'),
            'type'               => Controls_Manager::SELECT,
            'default'            => $default,
            'options'            => [
                                        '' => esc_html__('Default', 'omero'),
                                    ] + $column,
            'frontend_available' => true,
            'render_type'        => 'template',
            'prefix_class'       => 'elementor-grid%s-',
            'selectors'          => [
                '{{WRAPPER}}'                             => '--e-global-column-to-show: {{VALUE}}',
                //                    '(widescreen){{WRAPPER}} .grid__item'     => 'width: calc((100% - {{column_spacing_widescreen.SIZE}}{{column_spacing_widescreen.UNIT}}*({{column_widescreen.VALUE}} - 1)) / {{column_widescreen.VALUE}})',
                'body:not(.rtl) {{WRAPPER}} .swiper-slide'               => 'width: calc((100% - {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}*({{column.VALUE}} - 1)) / {{column.VALUE}}); margin-right: {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}',
                'body.rtl {{WRAPPER}} .swiper-slide'      => 'width: calc((100% - {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}*({{column.VALUE}} - 1)) / {{column.VALUE}}); margin-left: {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}',
                '(laptop){{WRAPPER}} .swiper-slide'       => 'width: calc((100% - {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}*({{column_laptop.VALUE}} - 1)) / {{column_laptop.VALUE}});',
                '(tablet_extra){{WRAPPER}} .swiper-slide' => 'width: calc((100% - {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}*({{column_tablet_extra.VALUE}} - 1)) / {{column_tablet_extra.VALUE}});',
                '(tablet){{WRAPPER}} .swiper-slide'       => 'width: calc((100% - {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}*({{column_tablet.VALUE}} - 1)) / {{column_tablet.VALUE}});',
                '(mobile_extra){{WRAPPER}} .swiper-slide' => 'width: calc((100% - {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}*({{column_mobile_extra.VALUE}} - 1)) / {{column_mobile_extra.VALUE}});',
                '(mobile){{WRAPPER}} .swiper-slide'       => 'width: calc((100% - {{column_spacing_swiper.SIZE}}{{column_spacing_swiper.UNIT}}*({{column_mobile.VALUE}} - 1)) / {{column_mobile.VALUE}});',

            ],
        ];
        if ($condition && $condition_column) {
            $args_column['condition'] = $condition;
        }

        $this->add_responsive_control(
            'column',
            $args_column
        );

        $this->add_responsive_control(
            'column_spacing_swiper',
            [
                'label'              => esc_html__('Column/Row Spacing', 'omero'),
                'type'               => Controls_Manager::SLIDER,
                'range'              => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'default'            => [
                    'size' => 30,
                ],
                'condition'          => [
                    'enable_carousel' => 'yes',
                ],
                'frontend_available' => true,
                'render_type'        => 'template',
                'separator'          => 'after',
                'selectors'          => [
                    '{{WRAPPER}} .swiper-slide' => '--grid-column-gap: {{SIZE}}{{UNIT}}; --grid-row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'column_spacing',
            [
                'label'              => esc_html__('Column/Row Spacing', 'omero'),
                'type'               => Controls_Manager::SLIDER,
                'range'              => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'default'            => [
                    'size' => 30,
                ],
                'condition'          => [
                    'enable_carousel!' => 'yes',
                ],
                'frontend_available' => true,
                'separator'          => 'after',
                'selectors'          => [
                    '{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}}; --grid-row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register style column widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function get_control_style_column($atts = array()) {
        $selectors = isset($atts['selectors']) ? $atts['selectors'] : '.item-inner';
        $prefix    = isset($atts['name']) ? $atts['name'] : 'item';
        $this->start_controls_section(
            'section_' . $prefix . '_style',
            [
                'label' => ucfirst($prefix),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            $prefix . '_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} ' . $selectors => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . '_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} ' . $selectors => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => $prefix . '_background',
                'selector' => '{{WRAPPER}} ' . $selectors,
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => $prefix . '_box_shadow',
                'selector' => '{{WRAPPER}} ' . $selectors,
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => $prefix . '_border',
                'selector'  => '{{WRAPPER}} ' . $selectors,
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register Carousel widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function get_control_carousel($condition = false, $alway_show = false) {

        $this->get_control_carousel_options($condition, $alway_show);
        $this->get_control_carousel_additional($condition);
        $this->get_control_carousel_style_navigation($condition);
    }

    /**
     * Register Control Carousel options.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function get_control_carousel_options($condition = false, $alway_show = false) {
        $args_caroseul_section = [
            'label' => esc_html__('Caroseul Options', 'omero'),
        ];
        if ($condition) {
            $args_caroseul_section['condition'] = $condition;
        }
        $this->start_controls_section(
            'section_swiperjs_options',
            $args_caroseul_section
        );

        if ($alway_show) {
            $this->add_control(
                'enable_carousel',
                [
                    'label' => esc_html__( 'Enable Carousel', 'omero' ),
                    'type' => Controls_Manager::HIDDEN,
                    'default' => 'yes',
                ]
            ); 
        }
        else {
            $this->add_control(
                'enable_carousel',
                [
                    'label' => esc_html__('Enable Carousel', 'omero'),
                    'type'  => Controls_Manager::SWITCHER,
                ]
            );
        }



        $this->add_control(
            'swiper_overflow',
            [
                'label'              => esc_html__('Overflow', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'none',
                'options'            => [
                    'none'  => esc_html__('None', 'omero'),
                    'left'  => esc_html__('Overflow to the left', 'omero'),
                    'right' => esc_html__('Overflow to the right', 'omero'),
                    'center' => esc_html__('Overflow to the center', 'omero'),
                ],
                'frontend_available' => true,
                'prefix_class'       => 'overflow-to-',
                'condition'          => [
                    'enable_carousel' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'navigation',
            [
                'label'              => esc_html__('Navigation', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'both',
                'options'            => [
                    'both'   => esc_html__('Arrows and Dots', 'omero'),
                    'arrows' => esc_html__('Arrows', 'omero'),
                    'dots'   => esc_html__('Dots', 'omero'),
                    'none'   => esc_html__('None', 'omero'),
                ],
                'frontend_available' => true,
                'condition'          => [
                    'enable_carousel' => 'yes'
                ],

            ]
        );

        $this->add_control('custom_navigation',
            [
                'label'              => esc_html__('Custom Navigation', 'omero'),
                'type'               => Controls_Manager::SWITCHER,
                'frontend_available' => true,
                'conditions'         => [
                    'relation' => 'and',
                    'terms'    => [
                        [
                            'name'     => 'enable_carousel',
                            'operator' => '=',
                            'value'    => 'yes',
                        ],
                        [
                            'relation' => 'or',
                            'terms'    => [
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'both',
                                ],
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'arrows',
                                ],
                            ],
                        ]
                    ],

                ],
            ]
        );
        $this->add_control(
            'custom_navigation_previous',
            [
                'label'              => esc_html__('Navigation Previous Selector', 'omero'),
                'type'               => Controls_Manager::TEXT,
                'frontend_available' => true,
                'conditions'         => [
                    'relation' => 'and',
                    'terms'    => [
                        [
                            'name'     => 'enable_carousel',
                            'operator' => '=',
                            'value'    => 'yes',
                        ],
                        [
                            'name'     => 'custom_navigation',
                            'operator' => '=',
                            'value'    => 'yes',
                        ],
                        [
                            'relation' => 'or',
                            'terms'    => [
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'both',
                                ],
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'arrows',
                                ],
                            ],
                        ]
                    ],

                ],
            ]
        );
        $this->add_control(
            'custom_navigation_next',
            [
                'label'              => esc_html__('Navigation Next Selector', 'omero'),
                'type'               => Controls_Manager::TEXT,
                'frontend_available' => true,
                'conditions'         => [
                    'relation' => 'and',
                    'terms'    => [
                        [
                            'name'     => 'enable_carousel',
                            'operator' => '=',
                            'value'    => 'yes',
                        ],
                        [
                            'name'     => 'custom_navigation',
                            'operator' => '=',
                            'value'    => 'yes',
                        ],
                        [
                            'relation' => 'or',
                            'terms'    => [
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'both',
                                ],
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'arrows',
                                ],
                            ],
                        ]
                    ],

                ],
            ]
        );

        $this->add_control(
            'navigation_previous_icon',
            [
                'label'            => esc_html__('Previous Arrow Icon', 'omero'),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin'             => 'inline',
                'label_block'      => false,
                'skin_settings'    => [
                    'inline' => [
                        'none' => [
                            'label' => 'Default',
                            'icon'  => 'eicon-chevron-left',
                        ],
                        'icon' => [
                            'icon' => 'eicon-star',
                        ],
                    ],
                ],
                'recommended'      => [
                    'fa-regular' => [
                        'arrow-alt-circle-left',
                        'caret-square-left',
                    ],
                    'fa-solid'   => [
                        'angle-double-left',
                        'angle-left',
                        'arrow-alt-circle-left',
                        'arrow-circle-left',
                        'arrow-left',
                        'caret-left',
                        'caret-square-left',
                        'chevron-circle-left',
                        'chevron-left',
                        'long-arrow-alt-left',
                    ],
                ],
                'conditions'       => [
                    'relation' => 'and',
                    'terms'    => [
                        [
                            'name'     => 'enable_carousel',
                            'operator' => '=',
                            'value'    => 'yes',
                        ],
                        [
                            'relation' => 'or',
                            'terms'    => [
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'both',
                                ],
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'arrows',
                                ],
                            ],
                        ]
                    ],

                ],
            ]
        );

        $this->add_control(
            'navigation_next_icon',
            [
                'label'            => esc_html__('Next Arrow Icon', 'omero'),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin'             => 'inline',
                'label_block'      => false,
                'skin_settings'    => [
                    'inline' => [
                        'none' => [
                            'label' => 'Default',
                            'icon'  => 'eicon-chevron-right',
                        ],
                        'icon' => [
                            'icon' => 'eicon-star',
                        ],
                    ],
                ],
                'recommended'      => [
                    'fa-regular' => [
                        'arrow-alt-circle-right',
                        'caret-square-right',
                    ],
                    'fa-solid'   => [
                        'angle-double-right',
                        'angle-right',
                        'arrow-alt-circle-right',
                        'arrow-circle-right',
                        'arrow-right',
                        'caret-right',
                        'caret-square-right',
                        'chevron-circle-right',
                        'chevron-right',
                        'long-arrow-alt-right',
                    ],
                ],
                'conditions'       => [
                    'relation' => 'and',
                    'terms'    => [
                        [
                            'name'     => 'enable_carousel',
                            'operator' => '=',
                            'value'    => 'yes',
                        ],
                        [
                            'relation' => 'or',
                            'terms'    => [
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'both',
                                ],
                                [
                                    'name'     => 'navigation',
                                    'operator' => '=',
                                    'value'    => 'arrows',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register Control Carousel Additional.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function get_control_carousel_additional($condition = false) {
        $args_caroseul_condition = [
            'enable_carousel' => 'yes'
        ];
        if ($condition) {
            $args_caroseul_condition = array_merge($args_caroseul_condition, $condition);
        }

        $this->start_controls_section(
            'section_additional_options',
            [
                'label'     => esc_html__('Additional Options', 'omero'),
                'condition' => $args_caroseul_condition,
            ]
        );

        $this->add_control(
            'center_carousel',
            [
                'label'              => esc_html__('Center Slides', 'omero'),
                'type'               => Controls_Manager::SWITCHER,
                'frontend_available' => true,
                'prefix_class' => 'center-carousel-',
                'render_type' => 'template'
            ]
        );

        $this->add_control(
            'lazyload',
            [
                'label'              => esc_html__('Lazyload', 'omero'),
                'type'               => Controls_Manager::SWITCHER,
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'drag_move',
            [
                'label'              => esc_html__('Drag/Touch Move', 'omero'),
                'type'               => Controls_Manager::SWITCHER,
                'frontend_available' => true,
                'default'            => 'yes',
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label'              => esc_html__('Autoplay', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'yes',
                'options'            => [
                    'yes' => esc_html__('Yes', 'omero'),
                    'no'  => esc_html__('No', 'omero'),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'pause_on_hover',
            [
                'label'              => esc_html__('Pause on Hover', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'yes',
                'options'            => [
                    'yes' => esc_html__('Yes', 'omero'),
                    'no'  => esc_html__('No', 'omero'),
                ],
                'condition'          => [
                    'autoplay' => 'yes',
                ],
                'render_type'        => 'none',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'pause_on_interaction',
            [
                'label'              => esc_html__('Pause on Interaction', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'yes',
                'options'            => [
                    'yes' => esc_html__('Yes', 'omero'),
                    'no'  => esc_html__('No', 'omero'),
                ],
                'condition'          => [
                    'autoplay' => 'yes',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'direction',
            [
                'label'   => esc_html__('Direction', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'rtl',
                'options' => [
                    'rtl' => esc_html__('Right to Left', 'omero'),
                    'ltr' => esc_html__('Left to Right', 'omero'),
                    // 'ctl' => esc_html__('Center', 'omero'),
                ],
                'frontend_available' => true,
                'condition'          => [
                    'autoplay' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'smooth_swiper',
            [
                'label'     => esc_html__('Smooth Scroll', 'omero'),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => '',
                'condition'          => [
                    'autoplay' => 'yes',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'autoplay_speed',
            [
                'label'              => esc_html__('Autoplay Speed', 'omero'),
                'type'               => Controls_Manager::NUMBER,
                'default'            => 5000,
                'condition'          => [
                    'autoplay' => 'yes',
                    'smooth_swiper!' => 'yes',
                ],
                'render_type'        => 'none',
                'frontend_available' => true,
            ]
        );

        // Loop requires a re-render so no 'render_type = none'
        $this->add_control(
            'infinite',
            [
                'label'              => esc_html__('Infinite Loop', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'yes',
                'options'            => [
                    'yes' => esc_html__('Yes', 'omero'),
                    'no'  => esc_html__('No', 'omero'),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'mousewheel',
            [
                'label'              => esc_html__('Using Mouse Wheel', 'omero'),
                'type'               => Controls_Manager::SWITCHER,
                'frontend_available' => true,
                // 'condition'          => [
                //     'infinite!' => 'yes',
                // ],
            ]
        );

        $this->add_control(
            'effect',
            [
                'label'              => esc_html__('Effect', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'slide',
                'options'            => [
                    'slide' => esc_html__('Slide', 'omero'),
                    'fade'  => esc_html__('Fade', 'omero'),
                ],
                'condition'          => [
                    'slides_to_show' => '1',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'speed',
            [
                'label'              => esc_html__('Animation Speed', 'omero'),
                'type'               => Controls_Manager::NUMBER,
                'default'            => 500,
                'render_type'        => 'none',
                'frontend_available' => true,
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register Control Carousel Style Navigation.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */

    protected function get_control_carousel_style_navigation($condition = false) {
        $args_caroseul_condition = [
            'navigation'      => ['arrows', 'dots', 'both'],
            'enable_carousel' => 'yes',
        ];
        if ($condition) {
            $args_caroseul_condition = array_merge($args_caroseul_condition, $condition);
        }

        $this->start_controls_section(
            'section_style_navigation',
            [
                'label'     => esc_html__('Navigation', 'omero'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => $args_caroseul_condition,
            ]
        );

        $this->add_control(
            'heading_style_arrows',
            [
                'label'     => esc_html__('Arrows', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_control(
            'arrows_size',
            [
                'label'     => esc_html__('Size', 'omero'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 20,
                        'max' => 60,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .omero-swiper-wrapper .elementor-swiper-button i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_responsive_control(
            'arrows_width',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'default'    => [
                    'unit' => 'px',
                ],
                'size_units' => ['%', 'px', 'vw'],
                'range'      => [
                    '%'  => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrows_height',
            [
                'label'      => esc_html__('Height', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'default'    => [
                    'unit' => 'px',
                ],
                'size_units' => ['%', 'px', 'vw'],
                'range'      => [
                    '%'  => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(

            Group_Control_Border::get_type(),
            [
                'name'      => 'arrows_border',
                'selector'  => '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'arrows_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'arrows_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next',
            ]
        );

        $this->start_controls_tabs('arrows_tabs');

        $this->start_controls_tab('arrows_normal',
            [
                'label' => esc_html__('Normal', 'omero'),
            ]
        );

        $this->add_control(
            'arrows_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev i, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next i'         => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev svg path, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next svg path' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_control(
            'arrows_background_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('arrows_hover',
            [
                'label' => esc_html__('Hover', 'omero'),
            ]
        );

        $this->add_control(
            'arrows_color_hover',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev:hover i, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next:hover i'         => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev:hover svg path, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next:hover svg path' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_control(
            'arrows_background_color_hover',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev:hover, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next:hover' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_control(
            'arrows_border_color_hover',
            [
                'label'     => esc_html__('Border Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev:hover, {{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'arrows_next_heading',
            [
                'label'     => esc_html__('Next button', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_control(
            'arrows_next_vertical',
            [
                'label'        => esc_html__('Next Vertical', 'omero'),
                'type'         => Controls_Manager::CHOOSE,
                'label_block'  => false,
                'options'      => [
                    'top'    => [
                        'title' => esc_html__('Top', 'omero'),
                        'icon'  => 'eicon-v-align-top',
                    ],
                    'bottom' => [
                        'title' => esc_html__('Bottom', 'omero'),
                        'icon'  => 'eicon-v-align-bottom',
                    ],
                ],
                'prefix_class' => 'elementor-swiper-button-next-vertical-',
                'condition'    => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_responsive_control(
            'arrows_next_vertical_value',
            [
                'type'       => Controls_Manager::SLIDER,
                'show_label' => false,
                'size_units' => ['px', '%'],
                'range'      => [
                    'px' => [
                        'min'  => -1000,
                        'max'  => 1000,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'unit' => '%',
                    'size' => 50,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next' => 'top: unset; bottom: unset; {{arrows_next_vertical.value}}: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'navigation'           => ['arrows', 'both'],
                    'arrows_next_vertical' => ['top', 'bottom'],
                ],
            ]
        );

        $this->add_control(
            'arrows_next_horizontal',
            [
                'label'        => esc_html__('Next Horizontal', 'omero'),
                'type'         => Controls_Manager::CHOOSE,
                'label_block'  => false,
                'options'      => [
                    'left'  => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'prefix_class' => 'elementor-swiper-button-next-horizontal-',
                'condition'    => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );
        $this->add_responsive_control(
            'next_horizontal_value',
            [
                'type'       => Controls_Manager::SLIDER,
                'show_label' => false,
                'size_units' => ['px', 'em', '%'],
                'range'      => [
                    'px' => [
                        'min'  => -1000,
                        'max'  => 1000,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => -45,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-next' => 'left: unset; right: unset;{{arrows_next_horizontal.value}}: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'navigation'             => ['arrows', 'both'],
                    'arrows_next_horizontal' => ['left', 'right'],
                ],

            ]
        );

        $this->add_control(
            'arrows_prev_heading',
            [
                'label'     => esc_html__('Prev button', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_control(
            'arrows_prev_vertical',
            [
                'label'        => esc_html__('Prev Vertical', 'omero'),
                'type'         => Controls_Manager::CHOOSE,
                'label_block'  => false,
                'render_type'  => 'ui',
                'options'      => [
                    'top'    => [
                        'title' => esc_html__('Top', 'omero'),
                        'icon'  => 'eicon-v-align-top',
                    ],
                    'bottom' => [
                        'title' => esc_html__('Bottom', 'omero'),
                        'icon'  => 'eicon-v-align-bottom',
                    ],
                ],
                'prefix_class' => 'elementor-swiper-button-prev-vertical-',
                'condition'    => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );

        $this->add_responsive_control(
            'arrows_prev_vertical_value',
            [
                'type'       => Controls_Manager::SLIDER,
                'show_label' => false,
                'size_units' => ['px', '%'],
                'range'      => [
                    'px' => [
                        'min'  => -1000,
                        'max'  => 1000,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'unit' => '%',
                    'size' => 50,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev' => 'top: unset; bottom: unset; {{arrows_prev_vertical.value}}: {{SIZE}}{{UNIT}};',
                ],

                'condition' => [
                    'navigation'           => ['arrows', 'both'],
                    'arrows_prev_vertical' => ['top', 'bottom'],
                ],
            ]
        );

        $this->add_control(
            'arrows_prev_horizontal',
            [
                'label'        => esc_html__('Prev Horizontal', 'omero'),
                'type'         => Controls_Manager::CHOOSE,
                'label_block'  => false,
                'options'      => [
                    'left'  => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'prefix_class' => 'elementor-swiper-button-prev-horizontal-',
                'condition'    => [
                    'navigation' => ['arrows', 'both'],
                ],
            ]
        );
        $this->add_responsive_control(
            'arrows_prev_horizontal_value',
            [
                'type'       => Controls_Manager::SLIDER,
                'show_label' => false,
                'size_units' => ['px', 'em', '%'],
                'range'      => [
                    'px' => [
                        'min'  => -1000,
                        'max'  => 1000,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-swiper-button.elementor-swiper-button-prev' => 'left: unset; right: unset; {{arrows_prev_horizontal.value}}: {{SIZE}}{{UNIT}};',
                ],

                'condition' => [
                    'navigation'             => ['arrows', 'both'],
                    'arrows_prev_horizontal' => ['left', 'right'],
                ],
            ]
        );

        $this->add_control(
            'heading_style_dots',
            [
                'label'     => esc_html__('Pagination', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->add_responsive_control(
            'dots_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'condition'  => [
                    'navigation' => ['dots', 'both'],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .swiper-pagination' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dots_position',
            [
                'label'        => esc_html__('Position', 'omero'),
                'type'         => Controls_Manager::SELECT,
                'default'      => 'outside',
                'options'      => [
                    'outside' => esc_html__('Outside', 'omero'),
                    'inside'  => esc_html__('Inside', 'omero'),
                ],
                'prefix_class' => 'elementor-pagination-position-',
                'condition'    => [
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->add_control(
            'dots_size',
            [
                'label'     => esc_html__('Size', 'omero'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 5,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet'                              => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-pagination-style-3 .swiper-pagination-bullet' => 'width:100%',
                    '{{WRAPPER}} .swiper-pagination-bullet::before'                              => 'height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->add_control(
            'dots_inactive_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    // The opacity property will override the default inactive dot color which is opacity 0.2.
                    '{{WRAPPER}} .swiper-pagination-bullet:not(.swiper-pagination-bullet-active)' => 'background: {{VALUE}}; opacity: 1',
                ],
                'condition' => [
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->add_control(
            'dots_color',
            [
                'label'     => esc_html__('Active Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}};',
                ],
                'condition' => [
                    'navigation' => ['dots', 'both'],
                ],
            ]
        );

        $this->end_controls_section();

        $args_carousel_dot = [
            'enable_carousel' => 'yes',
            'navigation!' => ['none', 'arrows'],
        ];
        if ($condition) {
            $args_carousel_dot = array_merge($args_carousel_dot, $condition);
        }        
        $this->start_controls_section(
            'carousel_dots',
            [
                'label'      => esc_html__('Carousel Dots', 'omero'),
                'condition' => $args_carousel_dot,
            ]
        );


        $this->add_control(
            'style_dot',
            [
                'label'        => esc_html__('Style Dot', 'omero'),
                'type'         => Controls_Manager::SELECT,
                'options'      => [
                    'style-1' => esc_html__('Style 1', 'omero'),
                    'style-2' => esc_html__('Style 2', 'omero'),
                    'style-3' => esc_html__('Style 3', 'omero'),
                ],
                'default'      => 'style-1',
                'prefix_class' => 'elementor-pagination-'
            ]
        );

        $this->start_controls_tabs('tabs_carousel_dots_style');

        $this->start_controls_tab(
            'tab_carousel_dots_normal',
            [
                'label' => esc_html__('Normal', 'omero'),
            ]
        );

        $this->add_control(
            'carousel_dots_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'carousel_dots_opacity',
            [
                'label'     => esc_html__('Opacity', 'omero'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'max'  => 1,
                        'min'  => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_carousel_dots_hover',
            [
                'label' => esc_html__('Hover', 'omero'),
            ]
        );

        $this->add_control(
            'carousel_dots_color_hover',
            [
                'label'     => esc_html__('Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .swiper-pagination-bullet:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'carousel_dots_opacity_hover',
            [
                'label'     => esc_html__('Opacity', 'omero'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'max'  => 1,
                        'min'  => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet:hover' => 'opacity: {{SIZE}};',
                    '{{WRAPPER}} .swiper-pagination-bullet:focus' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_carousel_dots_activate',
            [
                'label' => esc_html__('Activate', 'omero'),
            ]
        );

        $this->add_control(
            'carousel_dots_color_activate',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet-active:before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'carousel_dots_opacity_activate',
            [
                'label'     => esc_html__('Opacity', 'omero'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'max'  => 1,
                        'min'  => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'dots_vertical_value',
            [
                'label'      => esc_html__('Spacing', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range'      => [
                    'px' => [
                        'min'  => -1000,
                        'max'  => 1000,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'unit' => '%',
                    'size' => '',
                ],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-pagination-position-outside .swiper-pagination'   => 'bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.elementor-pagination-position-inside .swiper-pagination'    => 'bottom: {{SIZE}}{{UNIT}};',
                ]
            ]
        );

        $this->add_responsive_control(
            'dots_width',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 2000,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'unit' => '%',
                    'size' => '',
                ],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-pagination-style-3 .swiper-pagination-bullets' => 'width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'style_dot' => 'style-3'
                ],
            ]
        );

        $this->add_responsive_control(
            'Alignment_text',
            [
                'label'     => esc_html__('Alignment text', 'omero'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'flex-start' => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'   => 'center',
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination' => 'justify-content: {{VALUE}};',
                ],
                'condition' => [
                    'style_dot' => 'style-1'
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function get_swiper_navigation($slides_count = 0, $print = 1) {
        $settings = $this->get_settings_for_display();
        if ($settings['enable_carousel'] != 'yes') {
            return;
        }
        $show_dots   = (in_array($settings['navigation'], ['dots', 'both']));
        $show_arrows = (in_array($settings['navigation'], ['arrows', 'both']));

        ob_start();
        ?>
        <?php if (isset($slides_count) && $slides_count > 1) : ?>
            <?php if ($show_dots) : ?>
                <div class="swiper-pagination swiper-pagination-<?php echo esc_attr($this->get_id()) ?>"></div>
            <?php endif; ?>
            <?php if ($show_arrows && $settings['custom_navigation'] != 'yes') : ?>
                <div class="elementor-swiper-button elementor-swiper-button-prev elementor-swiper-button-prev-<?php echo esc_attr($this->get_id()) ?>">
                    <?php $this->render_swiper_button('previous'); ?>
                    <span class="elementor-screen-only"><?php echo esc_html__('Previous', 'omero'); ?></span>
                </div>
                <div class="elementor-swiper-button elementor-swiper-button-next elementor-swiper-button-next-<?php echo esc_attr($this->get_id()) ?>">
                    <?php $this->render_swiper_button('next'); ?>
                    <span class="elementor-screen-only"><?php echo esc_html__('Next', 'omero'); ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php
        if ($print) {
            printf('%s', ob_get_clean());
        } else {
            return ob_get_clean();
        }
    }

    protected function render_swiper_button($type, $html = false) {
        $direction     = 'next' === $type ? 'right' : 'left';
        $icon_settings = $this->get_settings_for_display('navigation_' . $type . '_icon');

        if (empty($icon_settings['value'])) {
            $icon_settings = [
                'library' => 'eicons',
                'value'   => 'eicon-chevron-' . $direction,
            ];
        }

        if ($html === true) {
            return Icons_Manager::try_get_icon_html($icon_settings, ['aria-hidden' => 'true']);
        }
        Icons_Manager::render_icon($icon_settings, ['aria-hidden' => 'true']);
    }

    protected function get_swiper_navigation_for_product() {
        $settings = $this->get_settings_for_display();
        if ($settings['enable_carousel'] != 'yes') {
            return;
        }
        $settings_navigation = '';
        $show_dots           = (in_array($settings['navigation'], ['dots', 'both']));
        $show_arrows         = (in_array($settings['navigation'], ['arrows', 'both']));


        if ($show_dots) {
            $settings_navigation .= '<div class="swiper-pagination swiper-pagination-' . $this->get_id() . '"></div>';
        }
        if ($show_arrows && $settings['custom_navigation'] != 'yes') {
            $settings_navigation .= '<div class="elementor-swiper-button elementor-swiper-button-prev elementor-swiper-button-prev-' . $this->get_id() . '">';
            $settings_navigation .= $this->render_swiper_button('previous', true);
            $settings_navigation .= '<span class="elementor-screen-only">' . esc_html__('Previous', 'omero') . '</span>';
            $settings_navigation .= '</div>';
            $settings_navigation .= '<div class="elementor-swiper-button elementor-swiper-button-next elementor-swiper-button-next-' . $this->get_id() . '">';
            $settings_navigation .= $this->render_swiper_button('next', true);
            $settings_navigation .= '<span class="elementor-screen-only">' . esc_html__('Next', 'omero') . '</span>';
            $settings_navigation .= '</div>';
        }
        return $settings_navigation;
    }

    /**
     * Get data elementor columns
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function get_data_elementor_columns() {

        $settings = $this->get_settings_for_display();
        //item
        $this->add_render_attribute('wrapper', 'class', 'omero-wrapper');
        $this->add_render_attribute('container', 'class', 'omero-con');
        $this->add_render_attribute('inner', 'class', 'omero-con-inner');
        if (isset($settings['enable_carousel']) && $settings['enable_carousel'] === 'yes') {
            $swiper_class = Plugin::$instance->experiments->is_feature_active('e_swiper_latest') ? 'swiper' : 'swiper-container';
            $this->add_render_attribute('wrapper', 'class', 'omero-swiper-wrapper swiper');
            $this->add_render_attribute('container', 'class', [$swiper_class, 'omero-swiper']);
            $this->add_render_attribute('inner', 'class', 'swiper-wrapper');
            $this->add_render_attribute('item', 'class', 'swiper-slide');
        } else {
            $this->add_render_attribute('inner', 'class', 'elementor-grid');
        }
    }


    protected function get_control_pagination() {
        $this->start_controls_section(
            'section_pagination',
            [
                'label' => esc_html__('Pagination', 'omero'),
            ]
        );

        $this->add_control(
            'pagination_type',
            [
                'label'   => esc_html__('Pagination', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    ''                      => esc_html__('None', 'omero'),
                    'numbers'               => esc_html__('Numbers', 'omero'),
                    'prev_next'             => esc_html__('Previous/Next', 'omero'),
                    'numbers_and_prev_next' => esc_html__('Numbers', 'omero') . ' + ' . esc_html__('Previous/Next', 'omero'),
                ],
            ]
        );

        $this->add_control(
            'pagination_page_limit',
            [
                'label'     => esc_html__('Page Limit', 'omero'),
                'default'   => '5',
                'condition' => [
                    'pagination_type!' => '',
                ],
            ]
        );

        $this->add_control(
            'pagination_numbers_shorten',
            [
                'label'     => esc_html__('Shorten', 'omero'),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => '',
                'condition' => [
                    'pagination_type' => [
                        'numbers',
                        'numbers_and_prev_next',
                    ],
                ],
            ]
        );

        $this->add_control(
            'pagination_prev_label',
            [
                'label'     => esc_html__('Previous Label', 'omero'),
                'default'   => esc_html__('&laquo; Previous', 'omero'),
                'condition' => [
                    'pagination_type' => [
                        'prev_next',
                        'numbers_and_prev_next',
                    ],
                ],
            ]
        );

        $this->add_control(
            'pagination_next_label',
            [
                'label'     => esc_html__('Next Label', 'omero'),
                'default'   => esc_html__('Next &raquo;', 'omero'),
                'condition' => [
                    'pagination_type' => [
                        'prev_next',
                        'numbers_and_prev_next',
                    ],
                ],
            ]
        );

        $this->add_control(
            'pagination_align',
            [
                'label'     => esc_html__('Alignment', 'omero'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'  => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'   => 'center',
                'selectors' => [
                    '{{WRAPPER}} .elementor-pagination' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                    'pagination_type!' => '',
                ],
            ]
        );
        $this->end_controls_section();
    }

    public function get_current_page() {
        if ('' === $this->get_settings('pagination_type')) {
            return 1;
        }

        return max(1, get_query_var('paged'), get_query_var('page'));
    }

    public function get_posts_nav_link($page_limit = null) {
        if (!$page_limit) {
            $page_limit = $this->query_posts()->max_num_pages;
        }

        $return = [];

        $paged = $this->get_current_page();

        $link_template     = '<a class="page-numbers %s" href="%s">%s</a>';
        $disabled_template = '<span class="page-numbers %s">%s</span>';

        if ($paged > 1) {
            $next_page = intval($paged) - 1;
            if ($next_page < 1) {
                $next_page = 1;
            }

            $return['prev'] = sprintf($link_template, 'prev', get_pagenum_link($next_page), $this->get_settings('pagination_prev_label'));
        } else {
            $return['prev'] = sprintf($disabled_template, 'prev', $this->get_settings('pagination_prev_label'));
        }

        $next_page = intval($paged) + 1;

        if ($next_page <= $page_limit) {
            $return['next'] = sprintf($link_template, 'next', get_pagenum_link($next_page), $this->get_settings('pagination_next_label'));
        } else {
            $return['next'] = sprintf($disabled_template, 'next', $this->get_settings('pagination_next_label'));
        }

        return $return;
    }

    protected function render_loop_footer() {
        $settings = $this->get_settings_for_display();
        if (!$settings['pagination_type'] || empty($settings['pagination_type'])) {
            return;
        }
        $parent_settings = $this->get_settings();
        if ('' === $parent_settings['pagination_type']) {
            return;
        }

        $page_limit = $this->query_posts()->max_num_pages;
        if ('' !== $parent_settings['pagination_page_limit']) {
            $page_limit = min($parent_settings['pagination_page_limit'], $page_limit);
        }

        if (2 > $page_limit) {
            return;
        }

        $this->add_render_attribute('pagination', 'class', 'elementor-pagination');

        $has_numbers   = in_array($parent_settings['pagination_type'], ['numbers', 'numbers_and_prev_next']);
        $has_prev_next = in_array($parent_settings['pagination_type'], ['prev_next', 'numbers_and_prev_next']);

        $links = [];

        if ($has_numbers) {
            $links = paginate_links([
                'type'               => 'array',
                'current'            => $this->get_current_page(),
                'total'              => $page_limit,
                'prev_next'          => false,
                'show_all'           => 'yes' !== $parent_settings['pagination_numbers_shorten'],
                'before_page_number' => '<span class="elementor-screen-only">' . esc_html__('Page', 'omero') . '</span>',
            ]);
        }

        if ($has_prev_next) {
            $prev_next = $this->get_posts_nav_link($page_limit);
            array_unshift($links, $prev_next['prev']);
            $links[] = $prev_next['next'];
        }

        ?>
        <div class="pagination">
            <nav class="elementor-pagination" aria-label="<?php esc_attr_e('Pagination', 'omero'); ?>">
                <?php echo implode(PHP_EOL, $links); ?>
            </nav>
        </div>
        <?php
    }

}
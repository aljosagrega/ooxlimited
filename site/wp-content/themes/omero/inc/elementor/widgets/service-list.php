<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!post_type_exists('service')) {
    return;
}

use Omero\Elementor\Omero_Base_Widgets;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Omero\Elementor\Omero_Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Icons_Manager;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Omero_Elementor_Widget_Service_List extends Omero_Base_Widgets {


    public function get_categories() {
        return array('omero-addons');
    }

    /**
     * Get widget name.
     *
     * Retrieve tabs widget name.
     *
     * @return string Widget name.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_name() {
        return 'omero-services-list';
    }

    /**
     * Get widget title.
     *
     * Retrieve tabs widget title.
     *
     * @return string Widget title.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_title() {
        return esc_html__('Omero Services List', 'omero');
    }

    /**
     * Get widget icon.
     *
     * Retrieve tabs widget icon.
     *
     * @return string Widget icon.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_icon() {
        return 'eicon-post-list';
    }


    public function get_script_depends() {
        return [
            'omero-elementor-service-list',
            'omero-scrolltrigger',
            'omero-gsap',
        ];
    }

    public function get_repeater_controls_style() {
        foreach (self::get_style() as $style => $label) {
            $repeater = new Repeater();
    
            $repeater->add_control(
                'choose_service',
                [
                    'label'     => __('Service', 'omero'),
                    'type'      => 'service',
                    'multiple'    => false,
                    'label_block' => true,
                    'separator' => 'after'
                ]
            );
            
            if ($style == 'list-1') {
                $repeater->add_control(
                    'selected_icon',
                    [
                        'label' => esc_html__('Icon Image', 'omero'),
                        'type'       => Controls_Manager::MEDIA,
                        'show_label' => true,
                        'label_block' => true,
                    ]
                );

                $repeater->add_control(
                    'override_thumbnail',
                    [
                        'label'   => esc_html__('Override Thumbnail', 'omero'),
                        'default' => '',
                        'type'    => Controls_Manager::SWITCHER,
                        'render_type' => 'template',
                    ]
                );
        
                $repeater->add_control(
                    'override_image',
                    [
                        'label'      => esc_html__('Override Image', 'omero'),
                        'type'       => Controls_Manager::MEDIA,
                        'show_label' => false,
                        'condition' => [
                            'override_thumbnail' => 'yes'
                        ]
                    ]
                );
            }
    
            $this->add_control(
                'services_list_'.$style,
                [
                    'label'       => esc_html__('Services', 'omero'),
                    'type'        => Controls_Manager::REPEATER,
                    'fields'      => $repeater->get_controls(),
                    'default' => [
                        [
                            'selected_icon' => '',
                        ],
                    ],
                    'condition' => [
                        'show_option' => 'select',
                        'style' => strval($style),
                    ],
                ]
            );
        }
    }

    protected static function get_style() {
        return [
            'list-1'  => esc_html__('Style 1', 'omero'),
            'list-2'  => esc_html__('Style 2', 'omero'),
        ];
    }

    /**
     * Register tabs widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function register_controls() {

        //Section Query
        $this->start_controls_section(
            'section_setting',
            [
                'label' => esc_html__('Settings', 'omero'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_option',
            [
                'label'   => esc_html__('Show Service', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'all',
                'options' => [
                    'all'  => esc_html__('All', 'omero'),
                    'select' => esc_html__('Select', 'omero'),
                ],
            ]
        );

        $this->get_repeater_controls_style();

        $this->add_control(
            'limit',
            [
                'label'   => esc_html__('Posts Per Page', 'omero'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'paginate',
            [
                'label'   => esc_html__('Paginate', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'       => esc_html__('None', 'omero'),
                    'pagination' => esc_html__('Pagination', 'omero'),
                    'loadmore' => esc_html__('Load More', 'omero'),
                ],
            ]
        );

        $this->add_responsive_control(
            'paginate_margin',
            [
                'label'      => esc_html__('Paginate Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .pagination'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'paginate!' => 'none',
                ]
            ]
        );

        $this->add_responsive_control(
            'paginate_align',
            [
                'label'        => esc_html__('Paginate Align', 'omero'),
                'type'         => Controls_Manager::CHOOSE,
                'options'      => [
                    'left'    => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .omero-loadmore' => 'text-align: {{value}}',
                    '{{WRAPPER}} .pagination ul.page-numbers' => 'justify-content: {{value}}',
                ],
                'condition' => [
                    'paginate!' => 'none',
                ],
                'separator' => 'after'
            ]
        );

        $this->add_control(
            'advanced',
            [
                'label' => esc_html__('Advanced', 'omero'),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => esc_html__('Order By', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'post__in',
                'options' => [
                    'post__in' => esc_html__('Services Selected', 'omero'),
                    'date'       => esc_html__('Date', 'omero'),
                    'id'         => esc_html__('Service ID', 'omero'),
                    'menu_order' => esc_html__('Menu Order', 'omero'),
                    'title'      => esc_html__('Service Title', 'omero'),
                    'rand'       => esc_html__('Random', 'omero'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => esc_html__('Order', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => [
                    'asc'  => esc_html__('ASC', 'omero'),
                    'desc' => esc_html__('DESC', 'omero'),
                ],
            ]
        );

        $this->add_control(
            'style',
            [
                'label'     => esc_html__('List Style', 'omero'),
                'type'      => Controls_Manager::SELECT,
                'default'   => array_key_first(self::get_style()),
                'render_type' => 'template',
                'options'   => self::get_style(),
                'prefix_class' => 'omero-services-list-style-'
            ]
        );
        
        $this->add_group_control(
            Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'      => 'image_thumbnail',
                'default'   => 'medium_large',
                'exclude' => ['custom']
            ]
        );

        $this->add_control(
            'column',
            [
                'label'   => esc_html__('Column', 'omero'),
                'type'    => Controls_Manager::HIDDEN,
                'default' => 1,
                'frontend_available' => true,
                'render_type'        => 'template',
                'prefix_class'       => 'elementor-grid%s-',
                'selectors'          => [
                    '{{WRAPPER}}' => '--e-global-column-to-show: 1',
                    '{{WRAPPER}} .swiper-slide'       => 'width: 100%',
                ],
            ]
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
//                 'condition'          => [
//                     'enable_carousel' => 'yes',
//                 ],
                'frontend_available' => true,
                'render_type'        => 'template',
                'separator'          => 'after',
                'selectors'          => [
                    '{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}}; --grid-row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'scroll_sticky',
            [
                'label'   => esc_html__('Scroll Sticky', 'omero'),
                'default' => 'yes',
                'type'    => Controls_Manager::SWITCHER,
                'condition' => [
                    'style' => 'list-1',
                    'enable_carousel!' => 'yes',
                ],
                'prefix_class' => 'omero-scroll-sticky-'
            ]
        );

        $this->add_control(
            'scroll_offset',
            [
                'label'      => esc_html__('Sticky Offset', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'size_units' => ['px'],
                'condition' => [
                    'style' => 'list-1',
                    'enable_carousel!' => 'yes',
                    'scroll_sticky' => 'yes',
                ],
                'render_type' => 'template'
            ]
        );

        $this->end_controls_section();


        //Section Query
        $this->start_controls_section(
            'section_service_style',
            [
                'label' => esc_html__('Service Block', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'block_wrapper',
            [
                'label'     => esc_html__('Block Wrapper', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'none',
                 'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_responsive_control(
            'block_service_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service-block-wrapper'   => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );


        $this->add_control(
            'wrapper_heading',
            [
                'label'     => esc_html__('Wrapper', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'none',
            ]
        );

        $this->add_responsive_control(
            'service_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-service-block'   => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'service_box_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-service-block' => 'border-radius: {{SIZE}}{{UNIT}}; --path-radius: {{SIZE}}',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

         $this->add_control(
            'service_box_background_color',
            [
                'label'     => esc_html__('Background Color ', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-service-block' => 'background-color: {{VALUE}}',
                ],
            ]
         );

          $this->add_control(
             'service_box_background_color_hover',
             [
                 'label'     => esc_html__('Background Color Hover ', 'omero'),
                 'type'      => Controls_Manager::COLOR,
                 'selectors' => [
                     '{{WRAPPER}} .omero-service-block:hover' => 'background-color: {{VALUE}}',
                 ],
             ]
          );

        $this->add_control(
            'image_service',
            [
                'label'     => esc_html__('Image', 'omero'),
                'type'      => Controls_Manager::HEADING,
                //'separator' => 'before'
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_responsive_control(
            'service_image_width',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 200,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service-image-box' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'image_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service-image-box' => 'border-radius: {{SIZE}}{{UNIT}}; --path-radius: {{SIZE}}',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'image_figure_service',
            [
                'label'     => esc_html__('Image Figure', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_responsive_control(
            'service_image_figure_width',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 200,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service_icon img' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_responsive_control(
            'horizontal_figure',
            [
                'label'      => esc_html__('Horizontal', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service_icon' => 'right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_responsive_control(
            'vertical_figure',
            [
                'label'      => esc_html__('Vertical', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service_icon' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_service_style',
            [
                'label' => esc_html__('Content', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'service_padding_content',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-service-block .service-caption'   => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'box_title_head',
            [
                'label'     => esc_html__('Service Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'align-title',
            [
                'label'        => esc_html__('Alignment', 'omero'),
                'type'         => Controls_Manager::CHOOSE,
                'options'      => [
                    'left'    => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'  => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'   => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'      => '',
                'selectors'    => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                   'style' => 'list-2'
                ]
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .object-loop-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .object-loop-title a'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .object-loop-title:before'   => 'color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'title_color_hover',
            [
                'label'     => esc_html__('Text Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .object-loop-title a:hover'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .object-loop-title:hover:before'   => 'color: {{VALUE}};',
                    '{{WRAPPER}}.omero-services-style-3 ul.omero-service li.actived .object-loop-title a'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_gradient',
            [
                'label'        => esc_html__('Hover Gradient', 'omero'),
                'type'         => Controls_Manager::SWITCHER,
                'prefix_class' => 'title-hover-gradient-'
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .object-loop-title, {{WRAPPER}} .object-loop-title a',
            ]
        );

        $this->add_responsive_control(
            'exerpt_width_title',
            [
                'label'      => esc_html__('Title Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .object-loop-title' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'box_exerpt',
            [
                'label'     => esc_html__('Service Exerpt', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_responsive_control(
            'exerpt_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .object-loop-exerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'exerpt_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .object-loop-exerpt'   => 'color: {{VALUE}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'exerpt_typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .object-loop-exerpt',
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_responsive_control(
            'exerpt_width',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .object-loop-exerpt' => 'width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'box_index_head',
            [
                'label'     => esc_html__('Service Index', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'index_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-index-item span'   => '-webkit-text-stroke-color: {{VALUE}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'index_typography',
                'selector' => '{{WRAPPER}} .service-index-item span',
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'box_button_head',
            [
                'label'     => esc_html__('Service Button', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->start_controls_tabs('color_tabs');

        $this->start_controls_tab('colors_normal',
            [
                'label' => esc_html__('Normal', 'omero'),
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-button'   => 'color: {{VALUE}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'button_icon_color',
            [
                'label'     => esc_html__('Icon Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-button i'   => 'color: {{VALUE}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'colors_hover',
            [
                'label' => esc_html__('Hover', 'omero'),
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'button_color_hover',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-button:hover'   => 'color: {{VALUE}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );

        $this->add_control(
            'button_icon_color_hover',
            [
                'label'     => esc_html__('Icon Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-button:hover i'   => 'color: {{VALUE}};',
                ],
                'condition' => [
                   'style' => 'list-1'
                ]
            ]
        );


       $this->end_controls_tab();

       $this->end_controls_tabs();

        $this->end_controls_section();

        $this->get_control_carousel();

    }

    /**
     * Render tabs widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $this->handle_setting($settings);
    }

    private function handle_setting($settings) {
        
        $class = '';
        $atts  = [
            'limit'          => $settings['limit'],
            'columns'        => 1,
            'orderby'        => $settings['orderby'],
            'order'          => $settings['order'],
            'image_size'      => $settings['image_thumbnail_size'] ?? 'large',
            'style' => $settings['style'] ?? array_key_first(self::get_style())
        ];

        $class         .= ' elementor-service';
        $class         .= ' elementor-service-style-' . $atts['style'];
        
        // Carousel
        if ($settings['enable_carousel'] === 'yes') {
            $atts['enable_carousel']   = 'yes';
            $atts['carousel_settings'] = $this->get_swiper_navigation_cpt();
            $class                     = ' omero-swiper-wrapper swiper';
        }
        if ($settings['paginate'] !== 'none') {
            $atts['paginate'] = true;
            $atts['paginate_type'] = empty($settings['paginate']) ? 'pagination' : $settings['paginate'];
        }

        $atts['class'] = $class;

        $datas = $this->get_options_data($atts);

        $offset_sticky = isset($settings['scroll_offset']['size']) ? $settings['scroll_offset']['size'] : 40;
        if (isset($settings['scroll_sticky']) && $settings['scroll_sticky'] === 'yes') {
            printf(
                '<span id="scroll-offset-%s" data-sticky-offset="%d" class="scroll-offset-data d-none"></span>', 
                esc_attr($this->get_id()), 
                absint($offset_sticky)
            );
        }

        echo (new Omero_Posttype('service', $atts))->get_content($datas); // WPCS: XSS ok
        
    }

    protected function get_options_data(&$atts) {
        $settings = $this->get_settings_for_display();

        $services_key = 'services_list_'.$atts['style'];

        $datas = [];
        if (isset($settings['show_option']) && $settings['show_option'] == 'select') {
            if (!empty($settings[$services_key])) {
                $ids = [];
                $icon = [];
                $override_image = [];
                $classes = [];

                foreach ($settings[$services_key] as $item) {
                    if (empty($item['choose_service'])) {
                        continue;
                    }
                    $id = absint($item['choose_service']);
                    if (!in_array($id, $ids)) {
                        $ids[] = $id;

                        if (!empty($item['selected_icon']['id'])) {
                            $icon[$id] = wp_get_attachment_image($item['selected_icon']['id'], 'large');
                        }

                        $classes[$id] = ' elementor-repeater-item-'.$item['_id'];
                        if (isset($item['override_thumbnail']) && $item['override_thumbnail'] === 'yes') {
                            if (!empty($item['override_image']['id'])) {
                                $item['override_image_size'] = $settings['image_thumbnail_size'];
                                $override_image[$id] = \Elementor\Group_Control_Image_Size::get_attachment_image_src(absint($item['override_image']['id']), 'override_image', $item);
                            }
                        }
                    }
                }
                if (!empty($ids)) {
                    $atts['ids'] = implode(',', $ids);
                }
                if (!empty($icon)) {
                    $datas['icon'] = $icon;
                }
                if (!empty($override_image)) {
                    $datas['override_image'] = $override_image;
                }
                if (!empty($classes)) {
                    $datas['classes'] = $classes;
                }
            }
        }

        return $datas;
    }

    protected function get_swiper_navigation_cpt() {
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
}

$widgets_manager->register(new Omero_Elementor_Widget_Service_List());

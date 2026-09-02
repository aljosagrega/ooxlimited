<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Omero\Elementor\Omero_Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;
use Omero\Elementor\Omero_Base_Widgets;


class Omero_Elementor_Testimonials_Carousel extends Omero_Base_Widgets
{
    public $testimonial_layout = '1';

    /**
     * Get widget name.
     *
     * Retrieve testimonial widget name.
     *
     * @return string Widget name.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_name()
    {
        return 'omero-testimonials-carousel';
    }

    /**
     * Get widget title.
     *
     * Retrieve testimonial widget title.
     *
     * @return string Widget title.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_title()
    {
        return esc_html__('Omero Testimonials Carousel', 'omero');
    }

    /**
     * Get widget icon.
     *
     * Retrieve testimonial widget icon.
     *
     * @return string Widget icon.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_icon()
    {
        return 'eicon-testimonial-carousel';
    }

    public function get_script_depends()
    {
        return ['omero-elementor-testimonial-carousel'];
    }

    public function get_categories()
    {
        return array('omero-addons');
    }

    /**
     * Register testimonial widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function register_controls()
    {
        $this->start_controls_section(
            'section_testimonial',
            [
                'label' => esc_html__('Testimonials', 'omero'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'testimonial_icon',
            [
                'label' => esc_html__('Icon', 'omero'),
                'type'  => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'exclude_inline_options' => ['svg'],
                'separator' => 'after'
            ]
        );

        $repeater->add_control(
            'testimonial_rating',
            [
                'label'   => esc_html__('Rating', 'omero'),
                'default' => 5,
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    0 => esc_html__('Hidden', 'omero'),
                    1 => esc_html__('Very poor', 'omero'),
                    2 => esc_html__('Not that bad', 'omero'),
                    3 => esc_html__('Average', 'omero'),
                    4 => esc_html__('Good', 'omero'),
                    5 => esc_html__('Perfect', 'omero'),
                ]
            ]
        );
        $repeater->add_control(
            'testimonial_content',
            [
                'label'       => esc_html__('Content', 'omero'),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
                'label_block' => true,
                'rows'        => '10',
            ]
        );

        $repeater->add_control(
            'testimonial_name',
            [
                'label'   => esc_html__('Name', 'omero'),
                'default' => 'John Doe',
                'type'    => Controls_Manager::TEXT,
            ]
        );

        $repeater->add_control(
            'testimonial_job',
            [
                'label'   => esc_html__('Date', 'omero'),
                'default' => 'Fresh Design',
                'type'    => Controls_Manager::TEXT,
            ]
        );

        $repeater->add_control(
            'testimonial_image',
            [
                'label'      => esc_html__('Choose Image', 'omero'),
                'type'       => Controls_Manager::MEDIA,
                'show_label' => false,
            ]
        );

        $repeater->add_control(
            'testimonial_link',
            [
                'label'       => esc_html__('Link to', 'omero'),
                'placeholder' => esc_html__('https://your-link.com', 'omero'),
                'type'        => Controls_Manager::URL,
            ]
        );


        $this->add_control(
            'testimonials',
            [
                'label'       => esc_html__('Items', 'omero'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ testimonial_name }}}',
                'min_items' => 1,
                'default' => [
                    [
                        'testimonial_rating' => 5,
                    ],
                    [
                        'testimonial_rating' => 5,
                    ],
                    [
                        'testimonial_rating' => 5,
                    ],
                ],
            ]
        );

        $this->add_control(
            'view',
            [
                'label'   => esc_html__('View', 'omero'),
                'type'    => Controls_Manager::HIDDEN,
                'default' => 'traditional',
            ]
        );

        $this->add_control(
            'testimonial_layout',
            [
                'label'   => esc_html__('Layout', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => 'Style 1',
                    '2' => 'Style 2',
                ],
                'render_type' => 'template',
                'prefix_class' => 'omero-testimonial-layout-',
            ]
        );

        $this->add_control(
            'coverflow_effect',
            [
                'type'    => Controls_Manager::HIDDEN,
                'default' => 'yes',
                'frontend_available' => true,
                'condition' => [
                    'testimonial_layout' => '2'
                ]
            ]
        );

        $this->add_group_control(
            Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'      => 'testimonial_image',
                'default'   => 'medium',
                'separator' => 'none',
                'condition' => [
                    'testimonial_layout' => '1'
                ]
            ]
        );

        $this->add_responsive_control(
            'column',
            [
                'label'              => esc_html__('Columns', 'omero'),
                'type'               => Controls_Manager::HIDDEN,
                'default'            => 1,
                'frontend_available' => true,
                'render_type'        => 'template',
                'prefix_class'       => 'elementor-grid%s-',
                'selectors'          => [
                    '{{WRAPPER}}' => '--e-global-column-to-show: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'column_spacing_swiper',
            [
                'label'              => esc_html__('Column Spacing', 'omero'),
                'type'               => Controls_Manager::SLIDER,
                'range'              => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'default'            => [
                    'size' => 30,
                ],
                'frontend_available' => true,
                'render_type'        => 'template',
                'separator'          => 'after',
                'selectors'          => [
                    '{{WRAPPER}} .swiper-slide' => '--grid-column-gap: {{SIZE}}{{UNIT}}; --grid-row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // WRAPPER STYLE
        $this->start_controls_section(
            'section_style_testimonial_wrapper',
            [
                'label' => esc_html__('Wrapper', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,

            ]
        );

        $this->add_responsive_control(
            'testimonial_width',
            [
                'label'          => esc_html__('Width', 'omero'),
                'type'           => Controls_Manager::SLIDER,
                'default'        => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units'     => ['%', 'px', 'vw'],
                'range'          => [
                    '%'  => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'      => [
                    '{{WRAPPER}} .elementor-testimonial-item-wrapper .inner' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'testimonial_height',
            [
                'label'          => esc_html__('Height', 'omero'),
                'type'           => Controls_Manager::SLIDER,
                'default'        => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units'     => ['%', 'px', 'vw'],
                'range'          => [
                    '%'  => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'      => [
                    '{{WRAPPER}} .elementor-testimonial-item-wrapper .inner' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'padding_testimonial_wrapper',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.omero-testimonial-layout-2 .inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'margin_testimonial_wrapper',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .inner' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.omero-testimonial-layout-2 .inner' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'color_testimonial_background',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .inner' => 'background: {{VALUE}};',
                    '{{WRAPPER}}.omero-testimonial-layout-1 .elementor-testimonial-item-wrapper' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'testimonial_alignment',
            [
                'label'       => esc_html__('Alignment Content', 'omero'),
                'type'        => Controls_Manager::CHOOSE,
                'options'     => [
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
                'label_block' => false,
                'selectors'   => [
                    '{{WRAPPER}} .elementor-testimonial-item-wrapper .inner'  => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .omero-testimonial-top-content .inner'  => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'wrapper_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .inner',
                'separator'   => 'before',

            ]
        );

        $this->add_control(
            'wrapper_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.omero-testimonial-layout-2 .elementor-testimonial-item .inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .swiper-slide' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-testimonial-item-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'section_style_testimonial_img_side_wrapper',
            [
                'label' => esc_html__('Image Side', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'testimonial_layout' => '1'
                ]
            ]
        );

        $this->add_responsive_control(
            'testimonial_width_img_side_wrapper',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%', 'vw'],
                'selectors'  => [
                    '{{WRAPPER}}.omero-testimonial-layout-1 .omero-elementor-testimonial-box' => '--images-side-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'testimonial_height_img_side_wrapper',
            [
                'label'      => esc_html__('Height', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px' ,'%', 'vh'],
                'selectors'  => [
                    '{{WRAPPER}}.omero-testimonial-layout-1 .omero-elementor-testimonial-box' => '--images-side-height: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}}.omero-testimonial-layout-1 .omero-elementor-testimonial-box img' => '--images-side-height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'testimonial_radius_img_side',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.omero-testimonial-layout-1 .omero-testimonial-images-side-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_testimonial_img',
            [
                'label' => esc_html__('Image', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'testimonial_width_img_side',
            [
                'label'      => esc_html__('Width Images Side', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%', 'vw'],
                'selectors'  => [
                    '{{WRAPPER}}.omero-testimonial-layout-2 .omero-testimonial-images-side-wrapper' => 'width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'testimonial_layout' => '2'
                ]
            ]
        );

        $this->add_responsive_control(
            'testimonial_width_img',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%', 'vw'],
                'selectors'  => [
                    '{{WRAPPER}} .caption-top .elementor-testimonial-image img' => 'width: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}}.omero-testimonial-layout-2 img' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'testimonial_height_img',
            [
                'label'      => esc_html__('Height', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', 'vh'],
                'selectors'  => [
                    '{{WRAPPER}} .caption-top .elementor-testimonial-image img' => 'height: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}}.omero-testimonial-layout-2 img' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'testimonial_radius_img',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .caption-top .elementor-testimonial-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.omero-testimonial-layout-2 img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding_testimonial_img',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .caption-top .elementor-testimonial-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}}.omero-testimonial-layout-2 .omero-testimonial-images-side-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'margin_testimonial_img',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .caption-top .elementor-testimonial-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'testimonial_layout' => '1'
                ]
            ]
        );

        $this->end_controls_section();

        // Content style
        $this->start_controls_section(
            'section_style_testimonial_style',
            [
                'label' => esc_html__('Content', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'testimonial_width_content',
            [
                'label'          => esc_html__('Width', 'omero'),
                'type'           => Controls_Manager::SLIDER,
                'default'        => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units'     => ['%', 'px', 'vw'],
                'range'          => [
                    '%'  => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'      => [
                    '{{WRAPPER}} .elementor-testimonial-item-wrapper .inner' => 'max-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .omero-testimonial-top-content .inner' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_content_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'content_content_color_hover',
            [
                'label'     => esc_html__('Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .inner:hover .content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'content_typography',
                'selector' => '{{WRAPPER}} .content',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'content_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .content',
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'details_spacing',
            [
                'label'      => esc_html__('Caption Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .details' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        // Name.
        $this->start_controls_section(
            'section_style_testimonial_name',
            [
                'label' => esc_html__('Name', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'name_text_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .name, {{WRAPPER}} .name a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'name_text_color_hover',
            [
                'label'     => esc_html__('Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .inner .name:hover, {{WRAPPER}} .inner .name a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'name_typography',
                'selector' => '{{WRAPPER}} .name',
            ]
        );

        $this->add_responsive_control(
            'name_padding',
            [
                'size_units' => ['px', 'em', '%'],
                'label'      => esc_html__('Spacing', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .omero-testimonial-top-content .name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Job.
        $this->start_controls_section(
            'section_style_testimonial_job',
            [
                'label' => esc_html__('Job', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'job_text_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .job' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'job_text_color_hover',
            [
                'label'     => esc_html__('Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .inner:hover .job' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'job_typography',
                'selector' => '{{WRAPPER}} .job',
            ]
        );

        $this->end_controls_section();

        // rating
        $this->start_controls_section(
            'section_style_testimonial_rating',
            [
                'label' => esc_html__('Rating', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'rating_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-testimonial-rating i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'rating_spacing',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-testimonial-rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Icon
        $this->start_controls_section(
            'section_style_testimonial_icon',
            [
                'label' => esc_html__('Icon', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-testimonial-item-wrapper .icon i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color_hover',
            [
                'label'     => esc_html__('Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-testimonial-item-wrapper .inner:hover .icon i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color_gradient',
            [
                'label' => esc_html__('Color Gradient', 'omero'),
                'type'         => Controls_Manager::SWITCHER,
                'prefix_class' => 'icon-color-gradient-',
            ]
        );

        $this->add_responsive_control(
            'icon_size_inner',
            [
                'label'      => __('Size', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .inner .icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .inner .icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Carousel options
        $this->get_control_carousel(false, $alway_show = true, $alway_center = true);
    }

    /**
     * Render testimonial widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        if (!empty($settings['testimonials']) && is_array($settings['testimonials'])) {
            $this->add_render_attribute('wrapper', 'class', 'elementor-testimonial-item-wrapper');
            // Row
            $this->get_data_elementor_columns();
            // Item
            $this->add_render_attribute('item', 'class', 'elementor-grid-item elementor-testimonial-item');
            $this->add_render_attribute('details', 'class', 'details');

            if (!empty($settings['testimonial_layout'])) {
                $this->testimonial_layout = $settings['testimonial_layout'];
            }

            $images_side = '';
            $image_size = $this->testimonial_layout != '1' ? 'thumbnail' : '';
            ?>
            <div class="omero-elementor-testimonial-box">
                <div <?php $this->print_render_attribute_string('wrapper'); ?>>
                    <div <?php $this->print_render_attribute_string('container'); ?>>
                        <div <?php $this->print_render_attribute_string('inner'); ?>>
                            <?php foreach ($settings['testimonials'] as $index => $testimonial): 
                                ob_start();
                                $show_information = $this->testimonial_layout == '2';
                                $this->render_image_thumbnail($testimonial, $settings, $image_size, $show_information);
                                $images_side .= ob_get_clean();
                                ?>
                                <div <?php $this->print_render_attribute_string('item'); ?>>
                                    <div class="item-inner inner">
                                        <div class="testimonial-content-text">
                                            <?php $this->render_item_rating($testimonial); ?>
                                            <?php $this->render_item_content($testimonial, $settings); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $this->get_swiper_navigation(count($settings['testimonials'])); ?>
                </div>
                <div class="omero-testimonial-images-side-wrapper swiper-container swiper">
                    <div class="omero-testimonial-images-side swiper-wrapper">
                        <?php printf('%s', $images_side); ?>
                    </div>
                </div>
            </div>
        <?php
        }
    }

    private function render_image_thumbnail($testimonial, $settings, $size = '', $show_information = false)
    {
        $class_item = 'elementor-testimonial-image swiper-slide';
        if ($show_information) {
            $class_item .= ' show_infomation';
        }

        printf('<div class="%s">', esc_attr($class_item));
            if (!empty($testimonial['testimonial_image']['url'])) {
                if (empty($size)) {
                    $testimonial['testimonial_image_size'] = $settings['testimonial_image_size'];
                    $testimonial['testimonial_image_custom_dimension'] = $settings['testimonial_image_custom_dimension'];
                } else {
                    $testimonial['testimonial_image_size'] = $size;
                }
                echo Group_Control_Image_Size::get_attachment_image_html($testimonial, 'testimonial_image');
            } else {
                omero_print_placeholder_image(['class' => 'attachment-full']);
            }
            if ($show_information) {
                $this->render_item_information($testimonial);
            }
        echo '</div>';
    }

    private function render_item_rating($testimonial)
    {
        if ($testimonial['testimonial_rating'] && $testimonial['testimonial_rating'] > 0) {
            echo '<div class="elementor-testimonial-rating">';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $testimonial['testimonial_rating']) {
                    echo '<i class="omero-icon-star active" aria-hidden="true"></i>';
                }
            }
            echo '</div>';
        }
    }

    private function render_item_content($testimonial, $settings) {
        if (!empty($testimonial['testimonial_icon']['value'])): ?>
            <div class="icon"><?php \Elementor\Icons_Manager::render_icon($testimonial['testimonial_icon'], ['aria-hidden' => 'true']); ?></div>
        <?php endif; ?>

        <?php if (!empty($testimonial['testimonial_content'])) : ?>
            <div class="content"><?php printf('%s', $testimonial['testimonial_content']); ?></div>
        <?php endif; ?>

        <div class="testimonial-caption">
            <?php 
            if ($this->testimonial_layout == '1') { 
                ?>
                <div class="caption-top">
                    <?php $this->render_image_thumbnail($testimonial, $settings, 'thumbnail'); ?>
                </div>
                <?php 
                $this->render_item_information($testimonial);
            }
            ?>
        </div>
        <?php
    }

    private function render_item_information($testimonial) {
        ?>
        <div class="caption-bottom">
            <div <?php $this->print_render_attribute_string('details'); ?>>
                <?php
                $testimonial_name_html = $testimonial['testimonial_name'];
                if (!empty($testimonial['testimonial_link']['url'])) :
                    $testimonial_name_html = '<a href="' . esc_url($testimonial['testimonial_link']['url']) . '">' . esc_html($testimonial_name_html) . '</a>';
                endif;
                printf('<span class="name">%s</span>', $testimonial_name_html);
                ?>
                <?php if ($testimonial['testimonial_job']): ?>
                    <span class="job"><?php echo esc_html($testimonial['testimonial_job']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

$widgets_manager->register(new Omero_Elementor_Testimonials_Carousel());

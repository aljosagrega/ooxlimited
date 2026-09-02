<?php

use Omero\Elementor\Woocommerce\Omero_Elementor_Product_Base_Trait;
use Omero\Elementor\Omero_Base_Widgets;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!omero_is_woocommerce_activated()) {
    return;
}

/**
 * @since 1.0.0
 */
class Omero_Elementor_Widget_Product_Gallery extends Omero_Base_Widgets {

    use Omero_Elementor_Product_Base_Trait;

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
        return 'omero-product-gallery';
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
        return esc_html__('Omero Product Gallery', 'omero');
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
        return 'eicon-slider-push';
    }

    public function get_script_depends() {
        return ['omero-elementor-product-gallery', 'omero-fancybox'];
    }

    public function get_style_depends() {
        return ['omero-fancybox'];
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
        $this->register_product_controls();

        $this->start_controls_section(
            'section_content_product_style',
            [
                'label' => esc_html__('Gallery', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'style',
            [
                'label'        => esc_html__('Style', 'omero'),
                'type'         => Controls_Manager::SELECT,
                'render_type'        => 'template',
                'default'      => 'slideshow',
                'options'      => [
                    'slideshow' => __('Slideshow', 'omero'),
                    'grid' => __('Grid', 'omero'),
                    'mansory' => __('Masonry', 'omero'),
                ],
                'prefix_class' => 'product-gallery-style-'
            ]
        ); 

        $this->add_responsive_control(
            'img_height',
            [
                'label'      => esc_html__('Image Height', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 2000,
                    ],
                ],
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}}' => '--gallery-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'bar_border_radius',
            [
                'label'      => esc_html__('Image Radius', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-wrapper .product-slideshow-inner img' => 'border-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .product-slideshow-item.last-item .product-gallery-viewmore' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();

        $this->get_controls_column();
        // Carousel Option

        $carousel_condition = [
            'style' => 'slideshow'
        ];
        $this->get_control_carousel($carousel_condition, true);
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

        $product = $this->get_product_widget($settings);

        if ( ! $product || ! $product instanceof WC_Product ) {
            return '';
        }

        $product_id = $product->get_id();
        $gallery = [];

        $post_thumbnail_id = $product->get_image_id();
        if ($post_thumbnail_id) {
            $thumb_url = get_the_post_thumbnail_url($post_thumbnail_id, 'full');
            if (!empty($gallery)) {
                $gallery[$post_thumbnail_id] = $thumb_url;
            }
        }

        $attachment_ids = $product->get_gallery_image_ids();
        if (!empty($attachment_ids)) {
            foreach ($attachment_ids as $id) {
                if ($url = wp_get_attachment_url($id)) {
                    $gallery[$id] = $url;
                }
            }
        }

        if (!empty($gallery)) {
            $style = empty($settings['style']) ? 'slideshow' : $settings['style'];
            $count = count((array) $gallery);
            if ($style == 'slideshow') {
                $swiper_pagination = $this->get_swiper_navigation($count, false);
            }
            $inner_class = 'elementor-widget-inner';
            if ($count < 2) {
                $inner_class .= ' single-gallery';
            }
            echo '<div class="'.$inner_class.'" data-viewmore="'.__('View more', 'omero').'">';
                get_template_part('woocommerce/gallery/'.$style, '', [
                    'product_id' => $product_id,
                    'gallery' => $gallery,
                    'swiper_pagination' => $swiper_pagination ?? ''
                ]);
            echo '</div>';
        } else {
            ?><pre><?php _e('Please add the gallery for this product!', 'omero'); ?></pre><?php
        }
    }

    

    protected function get_controls_column($condition = false, $default = 1, $condition_column = false) {
        $column = range(1, 10);
        $column = array_combine($column, $column);

        $this->start_controls_section(
            'section_column_options',
            [
                'label' => esc_html__('Column Options', 'omero')
            ]
        );

        $this->add_responsive_control(
            'column',
            [
                'label'              => esc_html__('Columns', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => $default,
                'options'            => [
                    '' => esc_html__('Default', 'omero'),
                ] + $column,
                'frontend_available' => true,
                'render_type'        => 'template',
                'prefix_class'       => 'omero-list-template elementor-grid%s-',
                'selectors'          => [
                    '{{WRAPPER}}' => '--e-global-column-to-show: {{VALUE}}',
                ],
                'condition'          => [
                    'style!' => 'mansory',
                ],
            ]
        );

        $this->add_control(
            'column_mansory',
            [
                'label'              => esc_html__('Columns', 'omero'),
                'type'               => Controls_Manager::HIDDEN,
                'default'            => 4,
                'frontend_available' => true,
                'prefix_class'       => 'omero-list-template elementor-grid-',
                'selectors'          => [
                    '{{WRAPPER}}' => '--e-global-column-to-show: {{VALUE}}',
                ],
                'condition'          => [
                    'style' => 'mansory',
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
                'condition'          => [
                    'enable_carousel' => 'yes',
                    'style' => 'slideshow',
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
                'condition'          => [
                    'style!' => 'slideshow',
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

}

$widgets_manager->register(new Omero_Elementor_Widget_Product_Gallery());

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
class Omero_Elementor_Widget_Service_Accordion extends Omero_Base_Widgets {

    private $image_size = 'large';

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
        return 'omero-services-accordion';
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
        return esc_html__('Omero Services Accordion', 'omero');
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
        return 'eicon-accordion';
    }


    public function get_script_depends() {
        return [
            'omero-elementor-service-accordion',
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
                'type'    => Controls_Manager::HIDDEN,
                'default' => 'select',
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'choose_service',
            [
                'label'     => __('Service', 'omero'),
                'type'      => 'service',
                'multiple'    => false,
                'label_block' => true,
                // 'separator' => 'before'
            ]
        );

        $repeater->add_control(
            'selected_icon',
            [
                'label' => esc_html__('Icon Image', 'omero'),
                'type'       => Controls_Manager::MEDIA,
                'show_label' => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'services_list',
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
                    'show_option' => 'select'
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
                'type'    => Controls_Manager::HIDDEN,
                'default' => 'post__in',
                
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

        $this->add_group_control(
            Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'      => 'image_thumbnail',
                'default'   => 'large',
                'exclude' => ['custom']
            ]
        );

        $this->end_controls_section();


        //Section Title
        $this->start_controls_section(
            'section_service_title',
            [
                'label' => esc_html__('Side Left', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'image_service',
            [
                'label'     => esc_html__('Image', 'omero'),
                'type'      => Controls_Manager::HEADING,
                //'separator' => 'before'
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
                    '{{WRAPPER}} .service-block .post-thumbnail' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service-block' => 'border-radius: {{SIZE}}{{UNIT}}; --path-radius: {{SIZE}}',
                ],
            ]
        );

        $this->add_control(
            'image_figure_service',
            [
                'label'     => esc_html__('Image Figure', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
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
                    '{{WRAPPER}} .service-icon img' => 'width: {{SIZE}}{{UNIT}};',
                ],
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
                    '{{WRAPPER}} .service-icon' => 'right: {{SIZE}}{{UNIT}};',
                ],
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
                    '{{WRAPPER}} .service-icon' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        //Section Query
        $this->start_controls_section(
            'section_service_style',
            [
                'label' => esc_html__('Side Right', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'box_title_list',
            [
                'label'     => esc_html__('Service List', 'omero'),
                'type'      => Controls_Manager::HEADING,
                //'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'title_padding_list',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-service-side-titles'     => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
            'title_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service-title'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-service-item-titles'    => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .omero-service-item-titles .service-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-service-item-titles .service-title'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label'     => esc_html__('Text Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-service-item-titles .service-title:hover'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color_actived',
            [
                'label'     => esc_html__('Text Color Actived', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-service-side-titles li.omero-service-item-titles.actived .service-title'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'box_index',
            [
                'label'     => esc_html__('Service Index', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'index_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service-index-item span'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'index_typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .service-index-item span',
            ]
        );

        $this->add_control(
            'index_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-index-item span'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'index_color_hover',
            [
                'label'     => esc_html__('Text Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-service-side-titles li.omero-service-item-titles:hover .service-index-item span'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'index_color_actived',
            [
                'label'     => esc_html__('Text Color Actived', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-service-side-titles li.omero-service-item-titles.actived .service-index-item span'   => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'box_exerpt_head',
            [
                'label'     => esc_html__('Service Exerpt', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'excerpt_margin_show',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .service-loop-exerpt'   => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'service_exerpt_width',
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
                    '{{WRAPPER}} .service-loop-exerpt' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'excerpt_typography_show',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .service-loop-exerpt',
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-loop-exerpt'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'box_button',
            [
                'label'     => esc_html__('Service Button', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->start_controls_tabs('color_tabs');

        $this->start_controls_tab('colors_normal',
            [
                'label' => esc_html__('Normal', 'omero'),
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
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'colors_hover',
            [
                'label' => esc_html__('Hover', 'omero'),
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
            ]
        );


       $this->end_controls_tab();

       $this->end_controls_tabs();

        $this->end_controls_tab();
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
        if (!empty($settings['image_thumbnail_size'])) {
            $this->image_size = $settings['image_thumbnail_size'];
        }

        $side_title = '';
        $icon_urls = [];
        if (!empty($settings['services_list'])) {
            $i = 1;
            foreach ($settings['services_list'] as $item) {
                if (empty($item['choose_service'])) {
                    continue;
                }
                $_id = $item['_id'];
                $id = absint($item['choose_service']);

                $icon_url = '';
                if (!empty($item['selected_icon']['url'])) {
                    $icon_url = $item['selected_icon']['url'];
                }
                $icon_urls[] = $icon_url;

                global $post;
                $post = get_post($id);
                setup_postdata($post);

                $side_title .= $this->get_render_side_titles($i, $_id);

                wp_reset_postdata();
                $i++;
            }
        }

        if (empty($side_title)) {
            ?><pre><?php _e('Please choose the service!', 'omero'); ?></pre><?php
            return;
        }

        $this->add_render_attribute('service-wrapper', 'class', 'omero-service-wrapper omero-service-side-images');
        $this->add_render_attribute('service-wrapper-titles', 'class', 'omero-service-side-titles');
        ?>
        <div <?php $this->print_render_attribute_string('service-wrapper-titles'); ?>>
            <ul class="omero-service-list-titles">
                <?php printf('%s', $side_title); ?>
            </ul>
        </div>
        <div <?php $this->print_render_attribute_string('service-wrapper'); ?>>
            <div class="omero-service-side-images-swiper swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($icon_urls as $url) : ?>
                    <div class="swiper-slide">
                        <div class="service-block">
                            <img src="/wp-content/uploads/2026/01/Group-6361.png" class="service-block-image-bg"/>
                            <img src="<?php echo esc_url($url); ?>" class="service-block-image-front"/>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        
    }

    private function get_render_side_titles($index, $_id) {
        ob_start();
        $item_class = 'omero-service-item-titles';
        if ($index === 1) {
            $item_class .= ' actived';
        }
        ?>
        <li class="<?php echo esc_attr($item_class); ?>" data-goto="<?php echo esc_attr($index-1) ?>">
            <div class="service-item-bottom">
                <div class="service-title-wrapper">
                    <span class="service-title"><?php the_title() ?></span>
                    <?php omero_service_loop_index($index, $before = '{', $after = '}') ?>
                </div>
                <div class="service-information-wrapper" style="<?php if ($index !== 1) echo 'display: none'; ?>">
                    <div class="information-inner">
                        <?php omero_service_loop_excerpt(); ?>
                        <?php omero_service_loop_button(); ?>
                    </div>
                </div>
            </div>
        </li>
        <?php
        return ob_get_clean();
    }

}

$widgets_manager->register(new Omero_Elementor_Widget_Service_Accordion());
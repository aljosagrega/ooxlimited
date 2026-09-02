<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;
use Omero\Elementor\Omero_Base_Widgets;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Widget_Base;

class Omero_Elementor_Accordion_Image extends Widget_Base {

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
    public function get_name() {
        return 'omero-accordion-image';
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
    public function get_title() {
        return esc_html__('Omero Accordion Image', 'omero');
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
    public function get_icon() {
        return 'eicon-accordion';
    }

    public function get_script_depends() {
        return ['omero-elementor-accordion-image'];
    }

    public function get_categories() {
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
    protected function register_controls() {

        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('General', 'omero'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'image_link_source',
            [
                'label'      => esc_html__('Choose Image', 'omero'),
                'type'       => Controls_Manager::MEDIA,
                // 'show_label' => false,
            ]
        );

        $repeater->add_control(
            'image_title',
            [
                'label'       => esc_html__('Navigation label', 'omero'),
                'type'        => Controls_Manager::TEXT,
                'default'     => '#Title',
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'image_description',
            [
                'label'       => esc_html__('Description', 'omero'),
                'type'        => Controls_Manager::WYSIWYG,
                'default'     => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'image-carousel',
            [
                'label'       => esc_html__('Items', 'omero'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ image_title }}}',
                'min_items' => 1,
                'default' => [
                    [
                        'title' => '#Title',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'      => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `brand_image_size` and `brand_image_custom_dimension`.
                'default'   => 'full',
                'separator' => 'none',
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
            
        $this->end_controls_section();


        $this->start_controls_section(
            'banner_style',
            [
                'label' => esc_html__('Image', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'heading_box_image',
            [
                'type'      => Controls_Manager::HEADING,
                'label'     => esc_html__('Box Image', 'omero'),
            ]
        );

        $this->add_responsive_control(
            'banner_img_height',
            [
                'label'      => esc_html__('Image height', 'omero'),
                'type'           => Controls_Manager::SLIDER,
                'default'        => [
                    'unit' => 'px',
                    'size' => '500',
                ],
                'tablet_default' => [
                    'unit' => 'px',
                    'size' => '500',
                ],
                'mobile_default' => [
                    'unit' => 'px',
                    'size' => '300',
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
                'selectors'  => [
                    '{{WRAPPER}} .elementor-accordion-image-wrapper' => '--accordion-image-img-height: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'img_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .img-wrapper-accordion-image .img-accordion-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'box-img-padding',
            [
                'label' => esc_html__( 'Padding', 'omero' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-accordion-image-right' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Box Text
        $this->start_controls_section(
            'box_text_style',
            [
                'label' => esc_html__('Box Text', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'box-text-margin',
            [
                'label' => esc_html__( 'Margin Box Text', 'omero' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-accordion-image-left' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        // Box Heading
        $this->add_control(
            'heading_box',
            [
                'type'      => Controls_Manager::HEADING,
                'label'     => esc_html__('Heading', 'omero'),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-nav-accordion-image h4 .accordion-image-wrapper-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'heading_color_hover',
            [
                'label'     => esc_html__('Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-nav-accordion-image h4:hover .accordion-image-wrapper-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'heading_color_active',
            [
                'label'     => esc_html__('Color Active', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-nav-accordion-image.active h4 .accordion-image-wrapper-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'heading_typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-nav-accordion-image h4 .accordion-image-wrapper-title',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Stroke::get_type(),
            [
                'name'     => 'heading_stroke',
                'selector' => '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-nav-accordion-image h4',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name'     => 'heading_shadow',
                'selector' => '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-nav-accordion-image h4',
            ]
        );

        $this->add_responsive_control(
            'box-heading-margin',
            [
                'label' => esc_html__( 'Margin', 'omero' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-nav-accordion-image h4' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        // Box Description      
        $this->add_control(
            'heading_box_description',
            [
                'type'      => Controls_Manager::HEADING,
                'label'     => esc_html__('Description', 'omero'),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordionimage-description' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'sub-typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .elementor-accordionimage-description',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Stroke::get_type(),
            [
                'name'     => 'sub_text_stroke',
                'selector' => '{{WRAPPER}} .elementor-accordionimage-description',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name'     => 'sub_text_shadow',
                'selector' => '{{WRAPPER}} .elementor-accordionimage-description',
            ]
        );

        $this->add_responsive_control(
            'box-text-description-margin',
            [
                'label' => esc_html__( 'Margin', 'omero' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-accordionimage-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'description_width',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 1500,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-accordion-image-wrapper .elementor-accordionimage-description' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );


        $this->end_controls_section();

        // $this->get_controls_column();
        // // Carousel options
        // $this->get_control_carousel();

    }

    /**
     * Render testimonial widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        if (!empty($settings['image-carousel']) && is_array($settings['image-carousel'])) {
            $this->add_render_attribute('wrapper', 'class', 'elementor-accordion-image-wrapper');
            $this->add_render_attribute('item', 'class', 'elementor-accordion-image-item');

            $image = '';
            ?>
            <div <?php $this->print_render_attribute_string('wrapper'); ?>>
                <div class="elementor-accordion-image-left">
                    <div class="elementor-accordion-image-inner">
                        <?php 
                        foreach ($settings['image-carousel'] as $index => $item) {
                            $title = !empty($item['image_title']) ? $item['image_title'] : '#Title '.($index + 1);
                            ?>
                            <div class="elementor-nav-accordion-image <?php if($index == 0) echo 'active'; ?>" data-goto="<?php echo esc_attr($index); ?>">
                                <h4 class="accordion-image-nav-title"><span class="accordion-image-wrapper-title"><?php echo esc_html($title) ?> </span></h4>
                                <div class="elementor-accordion-image-item <?php if($index == 0) echo 'show'; ?>" style="<?php if($index != 0) echo 'display: none'; ?>;">
                                    <div class="elementor-accordion-image-wrap-content">
                                        <?php if (!empty($item['image_description'])) : ?>
                                            <div class="elementor-accordionimage-description">
                                                <?php echo wp_kses_post($item['image_description']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php 

                            ob_start();
                            ?>
                            <div class="swiper-slide accordion-image-image <?php if($index == 0) echo 'show'; ?>" data-goto="<?php echo esc_attr($index); ?>">
                                <?php
                                $image_url = Group_Control_Image_Size::get_attachment_image_src($item['image_link_source']['id'], 'image', $settings);
                                if (!$image_url && isset($attachment['url'])) {
                                    $image_url = $item['url'];
                                }
                                if (empty($image_url)) {
                                    $image_url = \Elementor\Utils::get_placeholder_image_src();
                                }
                                ?>
                                <figure class="img-wrapper-accordion-image">
                                    <img class="image img-accordion-image" src="<?php echo esc_url($image_url); ?>" alt="image">
                                </figure>
                            </div>
                            <?php
                            $image .= ob_get_clean();
                        }
                        ?>
                    </div>    
                </div>
                <div class="elementor-accordion-image-right">
                    <div class="elementor-accordion-image-inner">
                        <div class="omero-swiper swiper swiper-image-effect">
                            <div class="swiper-wrapper">
                                <?php printf('%s', $image); ?>
                            </div>
                        </div>
                    </div>    
                </div>
            </div>
            
            <?php
        }
    }

}

$widgets_manager->register(new Omero_Elementor_Accordion_Image());


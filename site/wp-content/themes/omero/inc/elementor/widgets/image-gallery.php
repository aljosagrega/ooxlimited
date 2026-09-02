<?php

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Omero\Elementor\Omero_Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;
use Omero\Elementor\Omero_Base_Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor image gallery widget.
 *
 * Elementor widget that displays a set of images in an aligned grid.
 *
 * @since 1.0.0
 */
class Omero_Elementor_Image_Gallery extends Omero_Base_Widgets {

    /**
     * Get widget name.
     *
     * Retrieve image gallery widget name.
     *
     * @return string Widget name.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_name() {
        return 'omero-image-gallery';
    }

    /**
     * Get widget title.
     *
     * Retrieve image gallery widget title.
     *
     * @return string Widget title.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_title() {
        return esc_html__('Omero Image Gallery', 'omero');
    }

    public function get_script_depends() {
        return [
            'isotope',
            'masonry-pkgd',
            'omero-elementor-image-gallery'
        ];
    }

    public function get_style_depends() {
        return ['magnific-popup'];
    }

    public function get_categories() {
        return ['omero-addons'];
    }

    /**
     * Get widget icon.
     *
     * Retrieve image gallery widget icon.
     *
     * @return string Widget icon.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     *
     * @return array Widget keywords.
     * @since  2.1.0
     * @access public
     *
     */
    public function get_keywords() {
        return ['image', 'photo', 'visual', 'gallery'];
    }

    /**
     * Register image gallery widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function register_controls() {
        $this->start_controls_section(
            'section_gallery',
            [
                'label' => esc_html__('Image Gallery', 'omero'),
            ]
        );

        $this->add_control(
            'wp_gallery',
            [
                'label'      => esc_html__('Add Images', 'omero'),
                'type'       => Controls_Manager::GALLERY,
                'show_label' => false,
                'dynamic'    => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'layout',
            [
                'label'        => esc_html__('Layout', 'omero'),
                'type'         => Controls_Manager::SELECT,
                'default'      => 'default',
                'options'      => [
                    'default' => esc_html__('Default', 'omero'),
                    'masonry' => esc_html__('Masonry', 'omero'),
                ],
                'render_type' => 'template',
                'prefix_class' => 'omero-image-gallery-'
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name'      => 'thumbnail',
                // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
                //                'exclude'   => ['custom'],
                'separator' => 'none',
                'default'   => 'maisonco-gallery-image'
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

        $this->add_control('per_page',
            [
                'label'   => esc_html__('Number Per Page', 'omero'),
                'type'    => Controls_Manager::NUMBER,
                'title'   => esc_html__('The distance between the origin and the tooltip in pixels, default is 6', 'omero'),
                'default' => 8,
                'min' => 1
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_design_image',
            [
                'label' => esc_html__('Image', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'image_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .grid__item a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .grid__item a img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'content_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .grid__item a img',
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label'          => esc_html__('Height', 'omero'),
                'type'           => Controls_Manager::SLIDER,
                'default'        => [
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'unit' => 'px',
                ],
                'size_units'     => ['px','%', 'vh'],
                'range'          => [
                    'px' => [
                        'min' => 1,
                        'max' => 500,
                    ],
                    'vh' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors'      => [
                    '{{WRAPPER}}:not(.omero-image-gallery-masonry) .grid__item a img'     => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}'     => '--gallery-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->get_controls_column();
    }

    /**
     * Render image gallery widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function render() {
        $settings      = $this->get_settings_for_display();
        $image_gallery = array();
        if (!empty($settings['wp_gallery'])):
            foreach ($settings['wp_gallery'] as $items => $attachment) {
                $attachment['thumbnail_url'] = Group_Control_Image_Size::get_attachment_image_src($attachment['id'], 'thumbnail', $settings);
                $image_gallery[]             = $attachment;
            }
        endif;

        $this->add_render_attribute('wrapper', 'class', 'elementor-omero-image-gallery');
        $this->add_render_attribute('item', 'class', 'elementor-omero-item-image-gallery');
        
        $this->get_data_elementor_columns();

        $img_data = [];
        foreach ($image_gallery as $index => $item) {
            $image_url = Group_Control_Image_Size::get_attachment_image_src($item['id'], 'thumbnail', $settings);
            $image_url_full = wp_get_attachment_image_url($item['id'], 'full');
            $alt_img = Elementor\Control_Media::get_image_alt($item);
            $img_data[] = [
                'image_url' => $image_url,
                'image_url_full' => $image_url_full,
                'alt_img' => $alt_img,
            ];
        }

        $per_page = empty($settings['per_page']) ? 30 : $settings['per_page'];

        $this->add_render_attribute('wrapper', 'data-images', wp_json_encode($img_data));
        $this->add_render_attribute('wrapper', 'data-per_page', $per_page);
        ?>
        <div <?php $this->print_render_attribute_string('wrapper'); ?>>
            <div <?php $this->print_render_attribute_string('container'); ?>>
                <div <?php $this->print_render_attribute_string('inner'); ?>>
                    <?php 
                    foreach ($image_gallery as $index => $item) {
                        $image_url                = Group_Control_Image_Size::get_attachment_image_src($item['id'], 'thumbnail', $settings);
                        $image_url_full           = wp_get_attachment_image_url($item['id'], 'full');
                        $link_content_setting_key = $this->get_repeater_setting_key('wp_gallery', 'filter', $index);

                        if (Elementor\Plugin::$instance->editor->is_edit_mode()) {
                            $this->add_render_attribute($link_content_setting_key, [
                                'class' => 'elementor-clickable',
                            ]);
                        }
                        ?>
                        <div <?php $this->print_render_attribute_string('item'); ?>>
                            <div class="grid__item gallery_group_all">
                                <a <?php $this->print_render_attribute_string($link_content_setting_key); ?> href="<?php echo esc_url($image_url_full); ?>">
                                    <img class="omero-elementor-src-gallery" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(Elementor\Control_Media::get_image_alt($item)); ?>"/>
                                </a>
                            </div>
                        </div>
                        <?php 
                        if ($index + 1 == absint($per_page)) {
                            break;
                        }
                    } 
                    ?>
                </div>
                <?php
                if (!empty($img_data) && count($image_gallery) > $per_page) {
                    ?>
                    <div class="omero-elementor-gallery-loadmore">
                        <a class="loadmore-btn omero-button-effect" href="javascript:void(0)">
                            <span class="omero-button-inner">
                                <span class="omero-btn-content"><?php _e('Load more now', 'omero') ?></span>
                                <span class="elementor-button-icon">
                                    <span class="elementor-button-icon-inner"><i aria-hidden="true" class="omero-icon-sync"></i></span>
                                </span>
                            </span>
                        </a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }

    protected function get_controls_column($condition = false, $default = 4, $condition_column = false) {
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
                'condition' => [
                    'layout' => 'default'
                ]
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

$widgets_manager->register(new Omero_Elementor_Image_Gallery());
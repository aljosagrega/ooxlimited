<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Omero\Elementor\Omero_Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;
use Omero\Elementor\Omero_Base_Widgets;

class Omero_Elementor_Image_Switcher extends Omero_Base_Widgets {

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
        return 'omero-image-switcher';
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
        return esc_html__('Omero Image Switcher', 'omero');
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
        return 'eicon-thumbnails-down';
    }

    public function get_script_depends() {
        return [
            'omero-fancybox', 
            'omero-advance-slider-effect', 
            'omero-elementor-image-switcher'
        ];
    }

    public function get_style_depends() {
        return [
            'omero-fancybox',
            'omero-advance-slider-effect',
        ];
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
            'section_testimonial',
            [
                'label' => esc_html__('Image', 'omero'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'image_title',
            [
                'label'       => esc_html__('Title', 'omero'),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'Title',
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'image_link',
            [
                'label'       => esc_html__('Link to', 'omero'),
                'placeholder' => esc_html__('https://your-link.com', 'omero'),
                'type'        => Controls_Manager::URL,
                'default'     => [
                    'url' => '#',
                ],
            ]
        );

        $repeater->add_control(
            'image_link_source',
            [
                'label'      => esc_html__('Choose Image', 'omero'),
                'default'    => [
                    'url' => Elementor\Utils::get_placeholder_image_src(),
                ],
                'type'       => Controls_Manager::MEDIA,
                // 'show_label' => false,
            ]
        );

        $this->add_control(
            'image-carousel',
            [
                'label'       => esc_html__('Items', 'omero'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ image_title }}}',
                'default' => [
                    [
                        'image_title' => 'Indoor',
                    ],
                    [
                        'image_title' => 'Outdoor',
                    ],
                ]
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

        $this->add_control(
            'style',
            [
                'label'     => esc_html__('Block Style', 'omero'),
                'type'      => Controls_Manager::SELECT,
                'default'   => '1',
                'render_type' => 'template',
                'options'   => [
                    '1'  => esc_html__('Style 1', 'omero'),
                    '2'  => esc_html__('Style 2', 'omero'),
                ],
                'prefix_class' => 'omero-image-switcher-style-'
            ]
        );

        $this->add_control(
            'gl_effect',
            [
                'label'     => esc_html__('Enable GL Effect', 'omero'),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => 'no',
                'render_type' => 'template',
                'frontend_available' => true,
                'prefix_class' => 'omero-elementor-gl-effect-'
            ]
        );

        $this->add_control(
            'column',
            [
                'label'     => esc_html__('Columns', 'omero'),
                'type'      => Controls_Manager::HIDDEN,
                'default' => 1,
                'prefix_class'       => 'elementor-grid-',
                'selectors'          => [
                    '{{WRAPPER}}' => '--e-global-column-to-show: {{VALUE}}',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'navigation',
            [
                'label'     => esc_html__('Navigation', 'omero'),
                'type'      => Controls_Manager::HIDDEN,
                'default' => 'both',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'enable_carousel',
            [
                'label'     => esc_html__('Enabel Carousel', 'omero'),
                'type'      => Controls_Manager::HIDDEN,
                'default' => 'yes',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'single_column',
            [
                'label'     => esc_html__('Singel Column', 'omero'),
                'type'      => Controls_Manager::HIDDEN,
                'default' => 'yes',
                'frontend_available' => true,
            ]
        );

        $column = range(1, 10);
        $this->add_responsive_control(
            'column_navigation',
            [
                'label'              => esc_html__('Columns Navigation', 'omero'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 4,
                'options'            => [
                    '' => esc_html__('Default', 'omero'),
                ] + array_combine($column, $column),
                'frontend_available' => true,
                'render_type'        => 'template',
                'selectors'          => [
                ],
            ]
        );

        $this->add_responsive_control(
            'img_height',
            [
                'label'      => esc_html__('Image Height', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-wrapper-main-image' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_image_switcher_style',
            [
                'label' => esc_html__('Navigation style', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        ); 
        
        $this->add_responsive_control(
            'navigation_position',
            [
                'label'      => esc_html__('Navigation bottom', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.omero-image-switcher-style-1 .omero-image-switcher-navigation' => 'bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_padding',
            [
                'label'      => esc_html__('Navigation Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}}.omero-image-switcher-style-1 .omero-image-switcher-navigation' => 'Padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_section();

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
        if (empty($settings['image-carousel'])) {
            return;
        }

        $this->get_data_elementor_columns();

        $style = $settings['style'] ?? '1';

        $list_image = '';
        $list_title = '';

        foreach ($settings['image-carousel'] as $index => $item) {
            if (empty($item['image_title'])) {
                continue;
            }

            $title = $item['image_title'];

            $link_key = $this->get_repeater_setting_key('image_link', 'image-carousel', $index);
            $image_url = Group_Control_Image_Size::get_attachment_image_src($item['image_link_source']['id'], 'image', $settings);
            if (empty($image_url)) {
                $image_url = Elementor\Utils::get_placeholder_image_src();
            }

            $this->add_render_attribute($link_key, 'class', 'image-box');
            if (!empty($item['image_link']['url'])) {
                $this->add_link_attributes($link_key, $item['image_link']);
                $this->add_render_attribute($link_key, 'class', 'step-title-link');
            } else {
                $image_fullsize = wp_get_attachment_image_src($item['image_link_source']['id'], 'full');
                $image_url_fullsize = !empty($image_fullsize[0]) ? $image_fullsize[0] : $image_url;

                $this->add_render_attribute($link_key, 'href', $image_url_fullsize);
            }

            ob_start();
            ?>
            <div <?php $this->print_render_attribute_string('item'); ?>>
                <a <?php $this->print_render_attribute_string($link_key); ?>>
                    <img class="swiper-gl-image" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title) ?>">
                </a>
            </div>
            <?php
            $list_image .= ob_get_clean();

            $list_title .= sprintf(
                '<li class="image-switcher-title swiper-slide" data-goto="%d">
                    <span class="wrapper-title"><span class="dot"></span><span class="image-title">%s</span></span>
                </li>', 
                esc_attr($index),
                esc_html($title)
            );
        }
        if (empty($list_image)) {
            return;
        }
        
        $this->add_render_attribute('wrapper', 'class', 'omero-wrapper-main-image');
        ?>
        <div class="elementor-image-carousel-item-wrapper">
            <div class="omero-image-switcher-navigation">
                <div class="swiper swiper-navigation">
                    <ul class="omero-image-switcher-list-titles swiper-wrapper clear-list-style">
                        <?php printf('%s', $list_title); ?>
                    </ul>
                </div>
                <?php 
                // if ($style === '1') {
                    printf(
                        '<div class="image-switcher-navigation-swiper omero-swiper-wrapper">%s</div>',
                        $this->get_swiper_navigation(count($settings['image-carousel']))
                    );
                // }
                ?>
            </div>
            <div <?php $this->print_render_attribute_string('wrapper'); ?>>
                <div <?php $this->print_render_attribute_string('container'); ?>>
                    <div <?php $this->print_render_attribute_string('inner'); ?>>
                        <?php printf('%s', $list_image); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    protected function get_swiper_navigation($slides_count = 0, $print = 1) {
        $settings = $this->get_settings_for_display();
        ob_start();
        ?>
        <?php if (isset($slides_count) && $slides_count > 1) : ?>
            <div class="elementor-swiper-button elementor-swiper-button-prev elementor-swiper-button-prev-<?php echo esc_attr($this->get_id()) ?>">
                <?php $this->render_swiper_button('previous'); ?>
                <span class="elementor-screen-only"><?php echo esc_html__('Previous', 'omero'); ?></span>
            </div>
            <div class="elementor-swiper-button elementor-swiper-button-next elementor-swiper-button-next-<?php echo esc_attr($this->get_id()) ?>">
                <?php $this->render_swiper_button('next'); ?>
                <span class="elementor-screen-only"><?php echo esc_html__('Next', 'omero'); ?></span>
            </div>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

}

$widgets_manager->register(new Omero_Elementor_Image_Switcher());


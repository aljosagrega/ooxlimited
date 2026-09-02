<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;

class Omero_Elementor_Info_Canvas extends Elementor\Widget_Base {

    public function get_name() {
        return 'omero-info-canvas';
    }

    public function get_title() {
        return esc_html__('Omero Info Canvas', 'omero');
    }

    public function get_icon() {
        return 'eicon-nav-menu';
    }

    public function get_categories() {
        return ['omero-addons'];
    }

    public function get_info() {
        global $post;

        $options[''] = esc_html__('Select Info', 'omero');
        if (!omero_is_elementor_activated()) {
            return;
        }
        $args = array(
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            's'              => 'Info',
            'order'          => 'ASC',
        );

        $query1 = new WP_Query($args);
        while ($query1->have_posts()) {
            $query1->the_post();
            $options[$post->post_name] = $post->post_title;
        }

        wp_reset_postdata();
        return $options;
    }

    protected function register_controls() {
        $this->start_controls_section(
            'info_sesion_content',
            [
                'label' => esc_html__('Content', 'omero'),
            ]
        );

        $this->add_control(
            'info',
            [
                'label'   => esc_html__('Info Page', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'options' => $this->get_info(),
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label'   => esc_html__('Alignment', 'omero'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left'  => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'icon_info_style',
            [
                'label' => esc_html__('Icon', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('color_tabs');

        $this->start_controls_tab('colors_normal',
            [
                'label' => esc_html__('Normal', 'omero'),
            ]
        );

        $this->add_control(
            'info_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-canvas-info-wrapper .omero-info-button:before' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'info_bg_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-canvas-info-wrapper .omero-info-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'info_border_color',
            [
                'label'     => esc_html__('Border Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-canvas-info-wrapper .omero-info-button' => 'border-color: {{VALUE}};',
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
            '_menu_color_hover',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-canvas-info-wrapper .omero-info-button:hover:before' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            '_menu_bg_color_hover',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-canvas-info-wrapper .omero-info-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            '_menu_border_color_hover',
            [
                'label'     => esc_html__('Border Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-canvas-info-wrapper .omero-info-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'popup_info_style',
            [
                'label' => esc_html__('Popup Info', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'popup_width',
            [
                'label'          => esc_html__('Width', 'omero'),
                'type'           => Controls_Manager::SLIDER,
                'default'        => [
                    'unit' => 'px',
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
                    'body .omero-canvas-info' => '--e-global-info-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_close_color',
            [
                'label'     => esc_html__('Icon close', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    'body .omero-canvas-info .omero-canvas-info-close' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'icon_close_color_hover',
            [
                'label'     => esc_html__('Icon close hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    'body .omero-canvas-info .omero-canvas-info-close:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'popup_bg_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    'body .omero-canvas-info' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'size_icon',
            [
                'label'      => esc_html__('Size Icon', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-canvas-info-wrapper .omero-info-button:before' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

    }

    public function render_info() {
        $settings     = $this->get_settings_for_display();
        $slug         = $settings['info'];
        $queried_post = get_page_by_path($slug, OBJECT, 'elementor_library');
        ?>
        <div class="omero-canvas-info omero-canvas-info-<?php echo esc_attr($settings['align']) ?>">
            <a href="#" class="omero-canvas-info-close"><i class="omero-icon-times"></i></a>
            <?php if (isset($queried_post->ID)) {
                echo Elementor\Plugin::instance()->frontend->get_builder_content($queried_post->ID);
            } else {
                echo '<p>' . esc_html__('No Content', 'omero') . '</p>';
            }
            ?>
        </div>

        <div class="omero-info-overlay"></div>
        <?php
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $slug     = $settings['info'];
        add_action('wp_footer', array($this, 'render_info'));

        $this->add_render_attribute('wrapper', 'class', 'elementor-canvas-info-wrapper');

        ?>
        <div <?php $this->print_render_attribute_string('wrapper'); ?>>
            <a href="#" class="omero-info-button">
                <div class="omero-icon">
                    <span class="icon-1"></span>
                    <span class="icon-2"></span>
                    <span class="icon-3"></span>
                </div>
            </a>
        </div>
        <?php
    }
}

$widgets_manager->register(new Omero_Elementor_Info_Canvas());

<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;

class Omero_Elementor_Search extends Elementor\Widget_Base {
    public function get_name() {
        return 'omero-search';
    }

    public function get_title() {
        return esc_html__('Omero Search Form', 'omero');
    }

    public function get_icon() {
        return 'eicon-site-search';
    }

    public function get_categories() {
        return array('omero-addons');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'search-form-style',
            [
                'label' => esc_html__('Style', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'layout_style',
            [
                'label'   => esc_html__('Layout Style', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'layout-1' => esc_html__('Layout 1', 'omero'),
                    'layout-2' => esc_html__('Layout 2', 'omero'),
                    'layout-3' => esc_html__('Layout 3', 'omero'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->add_control(
            'form_input',
            [
                'label' => esc_html__('Input', 'omero'),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_responsive_control(
            'border_width',
            [
                'label'      => esc_html__('Border Width Input', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} form input[type=search]' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'border_radius_input',
            [
                'label'      => esc_html__('Border Radius Input', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .site-search form input[type=search]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding_input',
            [
                'label'      => esc_html__('Padding Input', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .site-search form input[type=search]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label'     => esc_html__('Border Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} form input[type=search]' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'border_color_focus',
            [
                'label'     => esc_html__('Border Color Focus', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} form input[type=search]:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} form input[type=search]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .button-search-popup .content' => 'color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
                    'text_color_hover',
                    [
                        'label'     => esc_html__('Text Hover', 'omero'),
                        'type'      => Controls_Manager::COLOR,
                        'default'   => '',
                        'selectors' => [
                            '{{WRAPPER}} .button-search-popup:hover .content' => 'color: {{VALUE}};',
                        ],
                    ]
                );

        $this->add_control(
            'text_color_placeholder',
            [
                'label'     => esc_html__('Text Color Placeholder', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} form input[type=search]::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'background_form',
            [
                'label'     => esc_html__('Background Form', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} form input[type=search]' => 'background: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'form_icon',
            [
                'label' => esc_html__('Icon', 'omero'),
                'type'  => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

         $this->add_control(
              'icon_color_form',
              [
                    'label'     => esc_html__('Color Icon', 'omero'),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '',
                    'selectors' => [
                        '{{WRAPPER}}.elementor-widget-omero-search .widget form button[type=submit]:before' => 'color: {{VALUE}};',
                        '{{WRAPPER}} .button-search-popup i' => 'color: {{VALUE}};',
                    ],
              ]
         );

          $this->add_control(
                 'icon_color_form_hover',
                 [
                     'label'     => esc_html__('Icon Hover', 'omero'),
                     'type'      => Controls_Manager::COLOR,
                     'default'   => '',
                     'selectors' => [
                     '{{WRAPPER}}.elementor-widget-omero-search .widget form button[type=submit]:hover:before' => 'color: {{VALUE}};',
                        '{{WRAPPER}} .button-search-popup:hover i' => 'color: {{VALUE}};',
                     ],
                 ]
          );

        $this->add_control(
            'icon_color_border',
            [
                'label'     => esc_html__('Icon Border', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-omero-search .site-header-search .button-search-popup.layout-3' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'layout_style' => 'layout-3',
                ],
            ]
        );

        $this->add_control(
            'icon_color_border_hover',
            [
                'label'     => esc_html__('Icon Border Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-omero-search .site-header-search .button-search-popup.layout-3:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'layout_style' => 'layout-3',
                ],
            ]
        );

        $this->add_control(
            'icon_bg_color',
            [
                'label'     => esc_html__('Icon Background', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-omero-search .site-header-search .button-search-popup.layout-3' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'layout_style' => 'layout-3',
                ],
            ]
        );

        $this->add_control(
            'icon_bg_color_hover',
            [
                'label'     => esc_html__('Icon Background Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}}.elementor-widget-omero-search .site-header-search .button-search-popup.layout-3:hover' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'layout_style' => 'layout-3',
                ],
            ]
        );

        $this->add_responsive_control(
            'border_width-icon',
            [
                'label'      => esc_html__('Border Width Icon', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-omero-search .site-header-search .button-search-popup.layout-3' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'layout_style' => 'layout-3',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ($settings['layout_style'] === 'layout-1'):
            if(omero_is_woocommerce_activated()){
                omero_product_search();
            }else{
                ?>
                <div class="site-search widget_search">
                    <?php get_search_form(); ?>
                </div>
                <?php
            }
        endif;

        if ($settings['layout_style'] === 'layout-2'):
            wp_enqueue_script('omero-search-popup');
            add_action('wp_footer', 'omero_header_search_popup', 1);
            ?>
            <div class="site-header-search">
                <a href="#" class="button-search-popup layout-2">
                    <i class="omero-icon-search"></i>
                    <span class="content"><?php echo esc_html__('Search', 'omero'); ?></span>
                </a>
            </div>
            <?php
        endif;

         if ($settings['layout_style'] === 'layout-3'):
            wp_enqueue_script('omero-search-popup');
            add_action('wp_footer', 'omero_header_search_popup', 1);
            ?>
            <div class="site-header-search">
                <a href="#" class="button-search-popup layout-3">
                    <i class="omero-icon-search"></i>
                </a>
            </div>
            <?php
        endif;
    }
}

$widgets_manager->register(new Omero_Elementor_Search());

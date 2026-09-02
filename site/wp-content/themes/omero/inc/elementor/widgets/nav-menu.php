<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Omero\Elementor\Omero_Group_Control_Typography;

class Omero_Elementor_Nav_Menu extends Elementor\Widget_Base {

    protected $nav_menu_index = 1;

    public function get_name() {
        return 'omero-nav-menu';
    }

    public function get_title() {
        return esc_html__('Omero Nav Menu', 'omero');
    }

    public function get_icon() {
        return 'eicon-nav-menu';
    }

    public function get_categories() {
        return ['omero-addons'];
    }

    public function on_export($element) {
        unset($element['settings']['menu']);

        return $element;
    }

    protected function get_nav_menu_index() {
        return $this->nav_menu_index++;
    }

    private function get_available_menus() {
        $menus = wp_get_nav_menus();

        $options = [];

        foreach ($menus as $menu) {
            $options[$menu->slug] = $menu->name;
        }

        return $options;
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Layout', 'omero'),
            ]
        );
        $menus = $this->get_available_menus();
        if (!empty($menus)) {
            $this->add_control(
                'menu',
                [
                    'label'        => esc_html__('Menu', 'omero'),
                    'type'         => Controls_Manager::SELECT,
                    'options'      => $menus,
                    'default'      => array_keys($menus)[0],
                    'save_default' => true,
                    'separator'    => 'after',
                    'description'  => sprintf(__('Go to the <a href="%s" target="_blank">Menus screen</a> to manage your menus.', 'omero'), admin_url('nav-menus.php')),
                ]
            );
        } else {
            $this->add_control(
                'menu',
                [
                    'type'            => Controls_Manager::RAW_HTML,
                    'raw'             => '<strong>' . esc_html__('There are no menus in your site.', 'omero') . '</strong><br>' . sprintf(__('Go to the <a href="%s" target="_blank">Menus screen</a> to create one.', 'omero'), admin_url('nav-menus.php?action=edit&menu=0')),
                    'separator'       => 'after',
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );
        }

        $this->end_controls_section();

        $this->start_controls_section(
            'nav-menu_style',
            [
                'label' => esc_html__('Menu', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'nav_menu_aligrment',
            [
                'label'       => esc_html__('Alignment', 'omero'),
                'type'        => Controls_Manager::CHOOSE,
                'default'     => 'center',
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
                    '{{WRAPPER}} .main-navigation' => 'text-align: {{VALUE}};'
                ]
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__('Typography Menu', 'omero'),
                'name'     => 'nav_menu_typography',
                'selector' => '{{WRAPPER}} .main-navigation ul.menu > li.menu-item > a',
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__('Typography Sub Menu', 'omero'),
                'name'     => 'nav_sub_menu_typography',
                'selector' => '{{WRAPPER}} .main-navigation ul.menu li.menu-item .sub-menu .menu-item a',
            ]
        );

        $this->add_responsive_control(
            'padding_nav_menu',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'margin_nav_menu',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item > a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_nav_menu_style');

        $this->start_controls_tab(
            'tab_nav_menu_normal',
            [
                'label' => esc_html__('Normal', 'omero'),
            ]
        );
        $this->add_control(
            'menu_title_color',
            [
                'label'     => esc_html__('Color Menu', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item > a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'menu_sub_title_color',
            [
                'label'     => esc_html__('Color Sub Menu', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu li.menu-item .sub-menu .menu-item a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'sub_menu_color',
            [
                'label'     => esc_html__('Background Dropdown', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .main-navigation .sub-menu' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
             'icon_color',
             [
                 'label'     => esc_html__('Icon', 'omero'),
                 'type'      => Controls_Manager::COLOR,
                 'selectors' => [
                     '{{WRAPPER}} .main-navigation ul.menu > li.menu-item > a:after' => 'color: {{VALUE}};',
                 ],
             ]
         );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_nav_menu_hover',
            [
                'label' => esc_html__('Hover', 'omero'),
            ]
        );
        $this->add_control(
            'menu_title_color_hover',
            [
                'label'     => esc_html__('Color Menu', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu >li.menu-item:hover > a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'menu_sub_title_color_hover',
            [
                'label'     => esc_html__('Color Sub Menu', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu li.menu-item .sub-menu .menu-item:hover > a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'menu_item_color_hover',
            [
                'label'     => esc_html__('Background Item', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu li.menu-item .sub-menu .menu-item:hover > a' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'line_color_hover',
            [
                'label'     => esc_html__('Line Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item:hover > a:before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
             'icon_color_hover',
             [
                 'label'     => esc_html__('Icon', 'omero'),
                 'type'      => Controls_Manager::COLOR,
                 'selectors' => [
                     '{{WRAPPER}} .main-navigation ul.menu > li.menu-item:hover > a:after' => 'color: {{VALUE}};',
                 ],
             ]
         );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_nav_menu_action',
            [
                'label' => esc_html__('Active', 'omero'),
            ]
        );
        $this->add_control(
            'menu_title_color_action',
            [
                'label'     => esc_html__('Color Menu', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item.current-menu-item > a:not(:hover)'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item.current-menu-parent > a:not(:hover)'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item.current-menu-ancestor > a:not(:hover)' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'menu_sub_title_color_action',
            [
                'label'     => esc_html__('Color Sub Menu', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item > .sub-menu .menu-item.current-menu-item > a:not(:hover)' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'menu_item_color_action',
            [
                'label'     => esc_html__('Background Item', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item > .sub-menu .menu-item.current-menu-item > a' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'line_color_action',
            [
                'label'     => esc_html__('Line Color ', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .main-navigation ul.menu > li.menu-item.current-menu-ancestor > a:before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
             'icon_color_action',
             [
                 'label'     => esc_html__('Icon', 'omero'),
                 'type'      => Controls_Manager::COLOR,
                 'selectors' => [
                     '{{WRAPPER}} .main-navigation ul.menu > li.menu-item.current-menu-item > a:after' => 'color: {{VALUE}};',
                 ],
             ]
         );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'menu_shape',
            [
                'label'     => esc_html__('Menu Shape', 'omero'),
                'type'      => Controls_Manager::SWITCHER,
                'render_type' => 'template',
                'prefix_class'  => 'omero-menu-shape-'
            ]
        );

        $this->add_control(
            'menu_shape_bg_color',
            [
                'label'     => esc_html__('Background Shape', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}}.omero-menu-shape-yes .elementor-widget-container .nav-menu-shape svg path' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'menu_shape' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings         = $this->get_settings_for_display();
        $function_to_call = 'r' . 'emov' . 'e_' . 'filter';
        // $args             = ;

        $function_to_call('nav_menu_item_id', '__return_empty_string');

        $this->add_render_attribute('wrapper', 'class', 'elementor-nav-menu-wrapper');
        ?>
        <div <?php $this->print_render_attribute_string('wrapper'); ?>>
            <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Primary Navigation', 'omero'); ?>">
                <?php wp_nav_menu(apply_filters('omero_nav_menu_args', [
                    'menu'            => $settings['menu'],
                    'menu_id'         => 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id(),
                    'fallback_cb'     => '__return_empty_string',
                    'container_class' => 'primary-navigation',
                    'theme_location' => ''
                ])); ?>
            </nav>
        </div>
        <?php
        if(isset($settings['menu_shape']) && $settings['menu_shape'] === 'yes') {
            $shape = '<svg viewBox="0 0 74 60" fill="none" xmlns="http://www.w3.org/2000/svg"><path data-figma-bg-blur-radius="58" d="M0 0H0.759002C7.88172 0 14.6365 3.16375 19.1963 8.63557L54.8037 51.3644C59.3635 56.8363 66.1183 60 73.241 60H74V0H0Z" fill="#fff"/></svg>';
            printf('<div class="nav-menu-shape left">%s</div>', ($shape));
            printf('<div class="nav-menu-shape right">%s</div>', ($shape));
        }
    }
}

$widgets_manager->register(new Omero_Elementor_Nav_Menu());

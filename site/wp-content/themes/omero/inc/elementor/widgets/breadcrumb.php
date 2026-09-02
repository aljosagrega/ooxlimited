<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Omero\Elementor\Omero_Group_Control_Typography;

class Omero_Elementor_Breadcrumb extends Elementor\Widget_Base {

    public function get_name() {
        return 'woocommerce-breadcrumb';
    }

    public function get_title() {
        return esc_html__('Omero Breadcrumbs', 'omero');
    }

    public function get_icon() {
        return 'eicon-product-breadcrumbs';
    }

    public function get_categories() {
        return ['omero-addons'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_title',
            [
                'label' => esc_html__('Content', 'omero'),
            ]
        );

        $this->add_control(
            'title_heading',
            [
                'label'     => esc_html__('Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                // 'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'title_type',
            [
                'label' => esc_html__( 'Show Title', 'omero' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'page_title',
                'options' => [
                    '' => esc_html__( 'Hidden', 'omero' ),
                    'page_title' => esc_html__( 'Page title', 'omero' ),
                    'custom' => esc_html__( 'Custom', 'omero' ),
                ],
                'render_type' => 'template'
            ]
        );

        $this->add_control(
            'title_custom',
            [
                'label'       => esc_html__('Title', 'omero'),
                'type'        => Controls_Manager::TEXTAREA,
                'dynamic'     => [
                    'active' => true,
                ],
                'placeholder' => esc_html__('Enter your title', 'omero'),
                'default'     => esc_html__('Add Your Heading Text Here', 'omero'),
                'condition' => [
                    'title_type' => 'custom'
                ]
            ]
        );

        $this->add_control(
            'subtitle_heading',
            [
                'label'     => esc_html__('Sub Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'sub_title',
            [
                'label'       => esc_html__('Sub Title', 'omero'),
                'type'        => Controls_Manager::TEXTAREA,
                'dynamic'     => [
                    'active' => true,
                ],
                'placeholder' => esc_html__('Enter your Sub title', 'omero'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_product_rating_style',
            [
                'label' => esc_html__('Style Breadcrumb', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'wc_style_warning',
            [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__('The style of this widget is often affected by your theme and plugins. If you experience any such issue, try to switch to a basic theme and deactivate related plugins.', 'omero'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-breadcrumb' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label'     => esc_html__('Icon Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-breadcrumb i' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'link_color',
            [
                'label'     => esc_html__('Link Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-breadcrumb > a:not(:hover)' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'link_color_hover',
            [
                'label'     => esc_html__('Link Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-breadcrumb > a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__('Typography Link', 'omero'),
                'name'     => 'text_link_typography',
                'selector' => '{{WRAPPER}} .woocommerce-breadcrumb a',
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__('Typography Text', 'omero'),
                'name'     => 'text_typography',
                'selector' => '{{WRAPPER}} .woocommerce-breadcrumb',
            ]
        );

        $this->add_responsive_control(
            'alignment',
            [
                'label'     => esc_html__('Alignment', 'omero'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
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
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-breadcrumb'    => 'text-align: {{VALUE}}',
                    '{{WRAPPER}} .omero-woocommerce-title' => 'text-align: {{VALUE}}',
                    '{{WRAPPER}} .omero-woocommerce-subtitle' => 'text-align: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'breadcrumb_size',
            [
                'label'   => esc_html__('HTML Tag', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'h1'   => 'H1',
                    'h2'   => 'H2',
                    'h3'   => 'H3',
                    'h4'   => 'H4',
                    'h5'   => 'H5',
                    'h6'   => 'H6',
                    'div'  => 'div',
                    'span' => 'span',
                    'p'    => 'p',
                ],
                'default' => 'div',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_product_rating_style_title',
            [
                'label' => esc_html__('Style Title', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color_title',
            [
                'label'     => esc_html__('Title Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-woocommerce-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'text_color_subtitle',
            [
                'label'     => esc_html__('Subtitle Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-woocommerce-subtitle' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__('Typography Title', 'omero'),
                'name'     => 'title_typography_title',
                'selector' => '{{WRAPPER}} .omero-woocommerce-title',
            ]
        );


        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__('Typography Subtitle', 'omero'),
                'name'     => 'text_typography_subtitle',
                'selector' => '{{WRAPPER}} .omero-woocommerce-subtitle',
            ]
        );

        $this->add_control(
            'display_title',
            [
                'label'        => esc_html__('Hidden Title', 'omero'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'prefix_class' => 'hidden-omero-title-'
            ]
        );

        $this->add_control(
            'display_title_single',
            [
                'label'        => esc_html__('Hidden Title Single', 'omero'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'prefix_class' => 'hidden-omero-title-single-'
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-woocommerce-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin_subtitle',
            [
                'label'      => esc_html__('Margin Subtitle', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-woocommerce-subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $tag = $settings['breadcrumb_size'] ? $settings['breadcrumb_size'] : 'div';

        if (class_exists('LXDB_Breadcrumb_Section')) {
            $filtered_breadcrumb = new LXDB_Breadcrumb_Section(false, '<i class="omero-icon-breadcrumb"></i>');
            $filtered_breadcrumb->set_strings([
                /* translators: %d: Page index by pagination */
                'paged' => esc_html__('Page %d', 'omero'),
                '404_error' => esc_html__('404 Page', 'omero'),
            ]);
            if ($filtered_breadcrumb) {
                $filtered_breadcrumb->print_breadcrump();
            }
        }

        if (!empty($settings['title_type'])) {
            $title = '';
            if ($settings['title_type'] == 'custom' && !empty($settings['title_custom'])) {
                $title = $settings['title_custom'];
            } elseif ($settings['title_type'] == 'page_title') {
                $page_id = get_queried_object_id();
                if(omero_is_woocommerce_activated() && is_shop()){
                    $page_id = wc_get_page_id('shop');
                }
                $title = (isset(get_queried_object()->term_id)) ? get_queried_object()->name : get_the_title($page_id);
                if (is_post_type_archive()) {
                    if(omero_is_woocommerce_activated() && is_shop()){
                        // Title shop page
                    } else {
                        $post_type = get_post_type();
                        $title = get_post_type_object( $post_type )->labels->name;
                    }
                } elseif (is_search()) {
                    $title = esc_html__('Search Results', 'omero');
                } elseif ( is_404() ) {
                    $title = esc_html__('404 Page', 'omero');
                }

            }

            if (!empty($title)) {
                printf('<%1s class="omero-woocommerce-title">%2s</%3s>', $tag, wp_kses_post($title), $tag);
            }
        }

        if (!empty($settings['sub_title'])) {
            printf('<div class="omero-woocommerce-subtitle">%s</div>', wp_kses_post($settings['sub_title']));
        }
    }

    public function render_plain_content() {
    }
}

$widgets_manager->register(new Omero_Elementor_Breadcrumb());

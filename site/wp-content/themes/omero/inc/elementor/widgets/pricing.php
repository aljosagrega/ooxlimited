<?php

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Widget_Base;
use Omero\Elementor\Omero_Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Omero_Pricing extends Elementor\Widget_Base {

    public function get_name() {
        return 'omero-pricing';
    }

    public function get_title() {
        return esc_html__('Omero Pricing', 'omero');
    }

    public function get_icon() {
        return 'eicon-price-table';
    }

    public function get_keywords() {
        return ['pricing', 'table', 'product', 'image', 'plan', 'button'];
    }

    public function get_script_depends() {
        return [
            'omero-elementor-pricing',
        ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_header',
            [
                'label' => esc_html__('Header', 'omero'),
            ]
        );

        $this->add_control(
            'pricing_image',
            [
                'label' => esc_html__('Choose Image', 'omero'),
                'default' => [
                    'url' => Elementor\Utils::get_placeholder_image_src(),
                ],
                'type' => Elementor\Controls_Manager::MEDIA,
                'show_label' => false,
            ]
        );

        $this->add_control(
            'sub_title',
            [
                'label'   => esc_html__('Subtitle', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__('Enter your Sub', 'omero'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'heading',
            [
                'label'   => esc_html__('Title', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__('Enter your title', 'omero'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'sub_heading',
            [
                'label'   => esc_html__('Description', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__('Enter your description', 'omero'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'heading_tag',
            [
                'label'   => esc_html__('Title HTML Tag', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ],
                'default' => 'h3',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_pricing',
            [
                'label' => esc_html__('Pricing', 'omero'),
            ]
        );

        $this->add_control(
            'currency_symbol',
            [
                'label'   => esc_html__('Currency Symbol', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    ''             => esc_html__('None', 'omero'),
                    'dollar'       => '&#36; ' . _x('Dollar', 'Currency', 'omero'),
                    'euro'         => '&#128; ' . _x('Euro', 'Currency', 'omero'),
                    'baht'         => '&#3647; ' . _x('Baht', 'Currency', 'omero'),
                    'franc'        => '&#8355; ' . _x('Franc', 'Currency', 'omero'),
                    'guilder'      => '&fnof; ' . _x('Guilder', 'Currency', 'omero'),
                    'krona'        => 'kr ' . _x('Krona', 'Currency', 'omero'),
                    'lira'         => '&#8356; ' . _x('Lira', 'Currency', 'omero'),
                    'peseta'       => '&#8359 ' . _x('Peseta', 'Currency', 'omero'),
                    'peso'         => '&#8369; ' . _x('Peso', 'Currency', 'omero'),
                    'pound'        => '&#163; ' . _x('Pound Sterling', 'Currency', 'omero'),
                    'real'         => 'R$ ' . _x('Real', 'Currency', 'omero'),
                    'ruble'        => '&#8381; ' . _x('Ruble', 'Currency', 'omero'),
                    'rupee'        => '&#8360; ' . _x('Rupee', 'Currency', 'omero'),
                    'indian_rupee' => '&#8377; ' . _x('Rupee (Indian)', 'Currency', 'omero'),
                    'shekel'       => '&#8362; ' . _x('Shekel', 'Currency', 'omero'),
                    'yen'          => '&#165; ' . _x('Yen/Yuan', 'Currency', 'omero'),
                    'won'          => '&#8361; ' . _x('Won', 'Currency', 'omero'),
                    'custom'       => esc_html__('Custom', 'omero'),
                ],
                'default' => 'dollar',
            ]
        );

        $this->add_control(
            'currency_symbol_custom',
            [
                'label'     => esc_html__('Custom Symbol', 'omero'),
                'type'      => Controls_Manager::TEXT,
                'condition' => [
                    'currency_symbol' => 'custom',
                ],
            ]
        );

        $this->add_control(
            'price',
            [
                'label'   => esc_html__('Price', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => '39.99',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'price_range',
            [
                'label'   => esc_html__('Price Range', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => '99.99',
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'currency_format',
            [
                'label'   => esc_html__('Currency Format', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    ''  => '1,234.56 (Default)',
                    ',' => '1.234,56',
                ],
            ]
        );

        $this->add_control(
            'period',
            [
                'label'   => esc_html__('Period', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__('Monthly', 'omero'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_features',
            [
                'label' => esc_html__('Features', 'omero'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'item_text',
            [
                'label'   => esc_html__('Text', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__('List Item', 'omero'),
            ]
        );

        $default_icon = [
            'value'   => 'far fa-check-circle',
            'library' => 'fa-regular',
        ];

        $repeater->add_control(
            'selected_item_icon',
            [
                'label'            => esc_html__('Icon', 'omero'),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'item_icon',
                'default'          => $default_icon,
            ]
        );

        $repeater->add_control(
            'item_icon_color',
            [
                'label'     => esc_html__('Icon Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} i'   => 'color: {{VALUE}}',
                    '{{WRAPPER}} {{CURRENT_ITEM}} svg' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $repeater->add_control(
            'item_text_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} span'   => 'color: {{VALUE}}',

                ],
            ]
        );

        $this->add_control(
            'features_list',
            [
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'item_text'          => esc_html__('List Item #1', 'omero'),
                        'selected_item_icon' => $default_icon,
                    ],
                    [
                        'item_text'          => esc_html__('List Item #2', 'omero'),
                        'selected_item_icon' => $default_icon,
                    ],
                    [
                        'item_text'          => esc_html__('List Item #3', 'omero'),
                        'selected_item_icon' => $default_icon,
                    ],
                ],
                'title_field' => '{{{ item_text }}}',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_footer',
            [
                'label' => esc_html__('Footer', 'omero'),
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label'   => esc_html__('Button Text', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__('Click Here', 'omero'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );


        $this->add_control(
            'button_icon',
            [
                'label' => esc_html__('Icon', 'omero'),
                'type'  => Controls_Manager::ICONS,
            ]
        );

        $this->add_control(
            'button_icon_align',
            [
                'label'     => esc_html__('Icon Position', 'omero'),
                'type'      => Controls_Manager::HIDDEN,
                'default'   => 'left',
            ]
        );

        $this->add_control(
            'link',
            [
                'label'       => esc_html__('Link', 'omero'),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'omero'),
                'default'     => [
                    'url' => '#',
                ],
                'dynamic'     => [
                    'active' => true,
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_wrapper_style',
            [
                'label' => esc_html__('Wrapper', 'omero'),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            ]
        );

        $this->add_responsive_control(
            'wrapper_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'wrapper_background_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'    => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table'   => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'wrapper_alignment',
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
                    '{{WRAPPER}} .elementor-price-table' => 'text-align: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'wrapper_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .elementor-price-table',
                'separator'   => 'before',

            ]
        );

        $this->add_control(
            'border_hover_color',
            [
                'label'     => esc_html__('Border Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table:hover'   => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'wrapper_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'omero' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_header_style',
            [
                'label'      => esc_html__('Header', 'omero'),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            ]
        );

        $this->add_control(
            'header_bg_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__header' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'header_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table__header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'header_border',
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .elementor-price-table__header',
                'separator'   => 'before',

            ]
        );

        $this->add_control(
            'heading_heading_style',
            [
                'label'     => esc_html__('Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'heading_typography',
                'selector' => '{{WRAPPER}} .elementor-price-table__heading',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__heading' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table .elementor-price-table__heading' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_sub_heading_style',
            [
                'label'     => esc_html__('Description', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'sub_heading_typography',
                'selector' => '{{WRAPPER}} .elementor-price-table__subheading',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );

        $this->add_control(
            'sub_heading_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__subheading' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'sub_title_style',
            [
                'label'     => esc_html__('Sub Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'sub_title_typography',
                'selector' => '{{WRAPPER}} .elementor-price-table__subtitle',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
            ]
        );

        $this->add_control(
            'sub_title_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__subtitle' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'sub_title_background_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__subtitle' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'sub_title_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table .elementor-price-table__subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sub_title_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table .elementor-price-table__subtitle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sub_title_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'omero' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__subtitle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_image_style',
            [
                'label' => esc_html__('Image', 'omero'),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            ]
        );

        $this->add_responsive_control(
            'image_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .pricing-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'omero' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .pricing-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_pricing_element_style',
            [
                'label'      => esc_html__('Pricing', 'omero'),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            ]
        );

        $this->add_responsive_control(
            'align_price',
            [
                'label'     => esc_html__('Alignment Price', 'omero'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'flex-start' => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center'     => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'flex-end'   => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'default'   => 'center',
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__price' => 'justify-content: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'pricing_element_bg_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__price' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'pricing_element_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table__price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'pricing_element_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table__price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'price_main_style',
            [
                'label'     => esc_html__('Price Main', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'price_main_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .price-main .before' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .price-main .price-value' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'price_main_typography',
                // Targeting also the .elementor-price-table class in order to get a higher specificity from the inline CSS.
                'selector' => '{{WRAPPER}} .price-main .before, {{WRAPPER}} .price-main .price-value',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
            ]
        );

        $this->add_control(
            'price_range_style',
            [
                'label'     => esc_html__('Price Range', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'price_range_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .price-range .after' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .price-range .price-value' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'price_range_typography',
                // Targeting also the .elementor-price-table class in order to get a higher specificity from the inline CSS.
                'selector' => '{{WRAPPER}} .price-range .after, {{WRAPPER}} .price-range .price-value',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
            ]
        );

        $this->add_control(
            'heading_period_style',
            [
                'label'     => esc_html__('Period', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'period!' => '',
                ],
            ]
        );

        $this->add_control(
            'period_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__period' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'period!' => '',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'      => 'period_typography',
                'selector'  => '{{WRAPPER}} .elementor-price-table__period',
                'global'    => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'condition' => [
                    'period!' => '',
                ],
            ]
        );

        $this->add_control(
            'period_position',
            [
                'label'       => esc_html__('Position', 'omero'),
                'type'        => Controls_Manager::SELECT,
                'label_block' => false,
                'options'     => [
                    'below'  => esc_html__('Below', 'omero'),
                    'beside' => esc_html__('Beside', 'omero'),
                ],
                'default'     => 'below',
                'condition'   => [
                    'period!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'period_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table__period.elementor-typo-excluded' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_features_list_style',
            [
                'label'      => esc_html__('Features', 'omero'),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            ]
        );

        $this->add_control(
            'features_list_bg_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__features-list' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'features_list_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-price-table__features-list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'features_list_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'global'    => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table__features-list' => '--e-price-table-features-list-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'features_list_typography',
                'selector' => '{{WRAPPER}} .elementor-price-table__features-list li',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );

       $this->add_responsive_control(
            'features_list_alignment',
            [
                'label'     => esc_html__('Alignment', 'omero'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'flex-start'   => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'  => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table .elementor-price-table__feature-inner' => 'justify-content: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

         $this->start_controls_section(
            'section_footer_list_style',
            [
                'label'      => esc_html__('Footer', 'omero'),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            ]
        );

         $this->start_controls_tabs('color_tabs');

        $this->start_controls_tab('colors_normal',
            [
                'label' => esc_html__('Normal', 'omero'),
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table .elementor-price-table__footer .elementor-price-table__button' => 'color: {{VALUE}}',
                ],
            ]
        );

         $this->add_control(
            'background_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table .elementor-price-table__footer .elementor-price-table__button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label'     => esc_html__('Border Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table .elementor-price-table__footer .elementor-price-table__button' => 'border-color: {{VALUE}}',
                ],
            ]
        );


        $this->add_control(
            'icon_color',
            [
                'label'     => esc_html__('Icon Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-price-table .elementor-price-table__footer .elementor-price-table__button i' => 'background-color: {{VALUE}}',
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
           'text_color_hover',
           [
               'label'     => esc_html__('Color', 'omero'),
               'type'      => Controls_Manager::COLOR,
               'selectors' => [
                   '{{WRAPPER}} .elementor-price-table .elementor-price-table__footer .elementor-price-table__button:hover' => 'color: {{VALUE}}',
               ],
           ]
       );

        $this->add_control(
           'background_color_hover',
           [
               'label'     => esc_html__('Background Color', 'omero'),
               'type'      => Controls_Manager::COLOR,
               'selectors' => [
                   '{{WRAPPER}} .elementor-price-table .elementor-price-table__footer .elementor-price-table__button:hover' => 'background-color: {{VALUE}}',
               ],
           ]
       );

       $this->add_control(
           'border_color_hover',
           [
               'label'     => esc_html__('Border Color', 'omero'),
               'type'      => Controls_Manager::COLOR,
               'selectors' => [
                   '{{WRAPPER}} .elementor-price-table .elementor-price-table__footer .elementor-price-table__button:hover' => 'border-color: {{VALUE}}',
               ],
           ]
       );


       $this->add_control(
           'icon_color_hover',
           [
               'label'     => esc_html__('Icon Background Color', 'omero'),
               'type'      => Controls_Manager::COLOR,
               'selectors' => [
                   '{{WRAPPER}} .elementor-price-table .elementor-price-table__footer .elementor-price-table__button:hover i' => 'background-color: {{VALUE}}',
               ],
           ]
       );

        $this->end_controls_tab();

        $this->end_controls_tabs();

         $this->add_responsive_control(
            'button_alignment',
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
                    '{{WRAPPER}} .elementor-price-table__footer' => 'text-align: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function render_currency_symbol($symbol, $location, bool $print = true) {
        $html = '';
        $currency_position = $this->get_settings('currency_position');
        $location_setting  = !empty($currency_position) ? $currency_position : 'before';

        if (!empty($symbol) && $location === $location_setting) {

            $class = 'elementor-price-table__currency';


            if ($location === 'after') {
                $class .= ' elementor-price-table__currency--after';
            }

            $html = '<span class="' . esc_attr($class) . '">' . esc_html($symbol) . '</span>';
        }

        if ($print) {
            printf('%s', $html);
        } else {
            return $html;
        }
    }

    private function get_currency_symbol($symbol_name) {
        $symbols = [
            'dollar'       => '&#36;',
            'euro'         => '&#128;',
            'franc'        => '&#8355;',
            'pound'        => '&#163;',
            'ruble'        => '&#8381;',
            'shekel'       => '&#8362;',
            'baht'         => '&#3647;',
            'yen'          => '&#165;',
            'won'          => '&#8361;',
            'guilder'      => '&fnof;',
            'peso'         => '&#8369;',
            'peseta'       => '&#8359',
            'lira'         => '&#8356;',
            'rupee'        => '&#8360;',
            'indian_rupee' => '&#8377;',
            'real'         => 'R$',
            'krona'        => 'kr',
        ];

        return isset($symbols[$symbol_name]) ? $symbols[$symbol_name] : '';
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $symbol   = '';
        $has_icon = !empty($settings['button_icon']);

        $pricing_layout = $settings['pricing_layout'] ?? '1';

        if ($has_icon) {
            $this->add_render_attribute('button-icon', 'class', $settings['button_icon']);
            $this->add_render_attribute('button-icon', 'aria-hidden', 'true');
        }

        if (empty($settings['button_icon']) && !Icons_Manager::is_migration_allowed()) {
            $settings['button_icon'] = 'fa fa-star';
        }

        $this->add_render_attribute('button_text', 'class', [
            'elementor-price-table__button',
            'omero-elementor-button',
            'omero-btn-has-icon',
        ]);

        $this->add_render_attribute('button_icon', 'class', ['elementor-button-icon button-icon']);

        if (!empty($settings['link']['url'])) {
            $this->add_link_attributes('button_text', $settings['link']);
        }

        if (!empty($settings['button_hover_animation'])) {
            $this->add_render_attribute('button_text', 'class', 'elementor-animation-' . $settings['button_hover_animation']);
        }

        if (!empty($settings['button_icon_align'])) {
            $this->add_render_attribute('button_icon', 'class', 'elementor-align-icon-' . $settings['button_icon_align']);
        }

        $this->add_render_attribute('sub_title', 'class', 'elementor-price-table__subtitle');
        $this->add_render_attribute('heading', 'class', 'elementor-price-table__heading');
        $this->add_render_attribute('sub_heading', 'class', 'elementor-price-table__subheading');
        $this->add_render_attribute('period', 'class', ['elementor-price-table__period', 'elementor-typo-excluded']);

        $this->add_inline_editing_attributes('sub_title', 'none');
        $this->add_inline_editing_attributes('heading', 'none');
        $this->add_inline_editing_attributes('sub_heading', 'none');
        $this->add_inline_editing_attributes('period', 'none');

        $heading_tag     = Utils::validate_html_tag($settings['heading_tag']);

        $migration_allowed = Icons_Manager::is_migration_allowed();
        $migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
        $is_new = ! isset( $settings['icon'] ) && Icons_Manager::is_migration_allowed();
        ?>

        <div class="elementor-price-table">
            <div class="elementor-price-table-deco">
                <?php if ( $settings['heading'] || $settings['sub_heading'] || $settings['sub_title'] ) : ?>
                <div class="elementor-price-table__header">
                    <?php if ( ! empty( $settings['pricing_image']['url'] ) ) : ?>
                        <div class="pricing-image">
                            <img src="<?php echo esc_url( $settings['pricing_image']['url'] ); ?>" alt="<?php esc_attr_e( 'Pricing Image', 'omero' ); ?>">
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($settings['sub_title'])) : ?>
                        <span <?php $this->print_render_attribute_string('sub_title'); ?>>
                              <?php $this->print_unescaped_setting('sub_title'); ?>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($settings['heading'])) : ?>
                        <<?php Utils::print_validated_html_tag($heading_tag); ?> <?php $this->print_render_attribute_string('heading'); ?>>
                        <?php $this->print_unescaped_setting('heading'); ?>
                        </<?php Utils::print_validated_html_tag($heading_tag); ?>>
                    <?php endif; ?>

                    <?php if (!empty($settings['sub_heading'])) : ?>
                        <span <?php $this->print_render_attribute_string('sub_heading'); ?>>
                              <?php $this->print_unescaped_setting('sub_heading'); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($pricing_layout == '1') $this->render_table_price($settings); ?>
                </div>
                <?php endif; ?>

            <?php if (!empty($settings['features_list'])) : ?>
                <div class="elementor-price-table__features">
                    <?php
                    if(!empty($settings['feature_title'])) {
                        printf('<h4 class="price-table__features_title">%s</h4>', esc_html($settings['feature_title']));
                    }
                    ?>
                    <ul class="elementor-price-table__features-list">
                        <?php
                        foreach ($settings['features_list'] as $index => $item) :
                            $repeater_setting_key = $this->get_repeater_setting_key('item_text', 'features_list', $index);
                            $this->add_inline_editing_attributes($repeater_setting_key);

                            $migrated = isset($item['__fa4_migrated']['selected_item_icon']);
                            // add old default
                            if (!isset($item['item_icon']) && !$migration_allowed) {
                                $item['item_icon'] = 'fa fa-check-circle';
                            }
                            $is_new = !isset($item['item_icon']) && $migration_allowed;
                            $class_item = 'elementor-repeater-item-' . $item['_id'];
                            ?>
                            <li class="<?php echo esc_attr($class_item) ?>">
                                <div class="elementor-price-table__feature-inner">
                                    <?php if (!empty($item['item_icon']) || !empty($item['selected_item_icon'])) :
                                        if ($is_new || $migrated) :
                                            Icons_Manager::render_icon($item['selected_item_icon'], ['aria-hidden' => 'true']);
                                        else : ?>
                                            <i class="<?php echo esc_attr($item['item_icon']); ?>" aria-hidden="true"></i>
                                        <?php
                                        endif;
                                    endif; ?>
                                    <?php if (!empty($item['item_text'])) : ?>
                                        <span <?php $this->print_render_attribute_string($repeater_setting_key); ?>>
                                                    <?php $this->print_unescaped_setting('item_text', 'features_list', $index); ?>
                                                </span>
                                    <?php
                                    else :
                                        echo '&nbsp;';
                                    endif;
                                    ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if($pricing_layout == '1') $this->render_button($settings); ?>
        </div>
        </div>

        <?php
    }

    protected function render_button($settings) {
        if (!empty($settings['button_text'])) { ?>
            <div class="elementor-price-table__footer">
                <?php if (!empty($settings['button_text'])) : ?>
                    <a <?php $this->print_render_attribute_string('button_text'); ?>>
                        <span class="elementor-button-content-wrapper">
                            <span class="elementor-button-text"><?php $this->print_unescaped_setting('button_text'); ?></span>
                            <?php if (!empty($settings['button_icon']['value'])) : ?>
                                <i <?php $this->print_render_attribute_string('button-icon'); ?>></i>
                            <?php endif; ?>
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        <?php }
    }

    protected function render_table_price($settings) {
        if (!empty($settings['currency_symbol'])) {
            $symbol = ('custom' !== $settings['currency_symbol'])
                ? $this->get_currency_symbol($settings['currency_symbol'])
                : $settings['currency_symbol_custom'];
        }

        $currency_format = empty($settings['currency_format']) ? '.' : $settings['currency_format'];
        $price   = explode($currency_format, $settings['price']);
        $intpart = $price[0];

        $has_range = !empty($settings['price_range']);
        if ($has_range) {
            $price_range   = explode($currency_format, $settings['price_range']);
            $intpart_range = $price_range[0];
        }

        $period_position = $settings['period_position'];
        $period_element  = '<span ' . $this->get_render_attribute_string('period') . '>' . $settings['period'] . '</span>';
        ?>
        <div class="elementor-price-table__price">
            <div class="elementor-price-table__integer-part">

                <div class="price-main">
                    <span class="elementor-price-table__currency before"><?php echo esc_html($symbol); ?><span class="price-value"><?php echo esc_html($intpart); ?></span></span>
                </div>

                <?php if ($has_range) : ?>
                    <div class="price-range">
                        <span class="elementor-price-table__currency after"><?php echo esc_html($symbol); ?><span class="price-value"><?php echo esc_html($intpart_range); ?></span></span>
                    </div>
                <?php endif; ?>

            </div>


            <?php if (!empty($settings['period']) && 'below' === $period_position) : ?>
                <?php printf('%s', $period_element); ?>
            <?php endif; ?>

            <?php if (!empty($settings['period']) && 'beside' === $period_position) : ?>
                <div class="elementor-price-table__after-price">
                    <?php printf('%s', $period_element); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
$widgets_manager->register(new Omero_Pricing());

<?php

namespace Elementor;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor button widget.
 *
 * Elementor widget that displays a button with the ability to control every
 * aspect of the button design.
 *
 * @since 1.0.0
 */
class Omero_Widget_Button extends Widget_Base
{

    /**
     * Get widget name.
     *
     * Retrieve button widget name.
     *
     * @return string Widget name.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_name()
    {
        return 'omero-button';
    }

    /**
     * Get widget title.
     *
     * Retrieve button widget title.
     *
     * @return string Widget title.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_title()
    {
        return esc_html__('Omero Button', 'omero');
    }

    /**
     * Get widget icon.
     *
     * Retrieve button widget icon.
     *
     * @return string Widget icon.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_icon()
    {
        return 'eicon-button';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the button widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * @return array Widget categories.
     * @since 2.0.0
     * @access public
     *
     */

    public function get_categories()
    {
        return ['basic'];
    }

    /**
     * Get button sizes.
     *
     * Retrieve an array of button sizes for the button widget.
     *
     * @return array An array containing button sizes.
     * @since 3.4.0
     * @access public
     * @static
     *
     */
    public static function get_button_sizes()
    {
        return [
            'xs' => esc_html__('Extra Small', 'omero'),
            'sm' => esc_html__('Small', 'omero'),
            'md' => esc_html__('Medium', 'omero'),
            'lg' => esc_html__('Large', 'omero'),
            'xl' => esc_html__('Extra Large', 'omero'),
        ];
    }

    public function get_script_depends()
    {
        return [
            'omero-elementor-button',
            'magnific-popup',
        ];
    }

    public function get_style_depends()
    {
        return ['magnific-popup'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_button',
            [
                'label' => esc_html__('Button', 'omero'),
            ]
        );

        $args = [];
        $default_args = [
            'section_condition' => [],
            'button_default_text' => esc_html__('Click here', 'omero'),
            'text_control_label' => esc_html__('Text', 'omero'),
            'alignment_control_prefix_class' => 'elementor%s-align-',
            'alignment_default' => '',
            'icon_exclude_inline_options' => [],
        ];
        $args = wp_parse_args($args, $default_args);

        $this->add_control(
            'button_type',
            [
                'label' => esc_html__('Type', 'omero'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => esc_html__('Default', 'omero'),
                    'outline' => esc_html__('Outline', 'omero'),
                    'link' => esc_html__('Link', 'omero'),

                ],
                'render_type' => 'template',
                'prefix_class' => 'elementor-button-type-',
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'text',
            [
                'label' => $args['text_control_label'],
                'type' => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => $args['button_default_text'],
                'placeholder' => $args['button_default_text'],
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => esc_html__('Link', 'omero'),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__('https://your-link.com', 'omero'),
                'default' => [
                    'url' => '#',
                ],
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'popup_button',
            [
                'type'    => Controls_Manager::SWITCHER,
                'label'       => esc_html('Popup Button', 'omero'),
                'prefix_class' => 'omero-button-popup-'
            ]
        );

        $this->add_control(
            'popup_type',
            [
                'label' => esc_html__('Popup Type', 'omero'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => esc_html__('Default', 'omero'),
                    'image' => esc_html__('URL Image', 'omero'),
                    'iframe' => esc_html__('URL Iframe', 'omero'),
                    'inline' => esc_html__('Target Content', 'omero'),
                ],
                'frontend_available' => true,
                'condition' => [
                    'popup_button' => 'yes'
                ]
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => esc_html__('Alignment', 'omero'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__('Justified', 'omero'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'prefix_class' => $args['alignment_control_prefix_class'],
                'default' => $args['alignment_default'],
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'size',
            [
                'label' => esc_html__('Size', 'omero'),
                'type' => Controls_Manager::SELECT,
                'default' => 'md',
                'options' => self::get_button_sizes(),
                'style_transfer' => true,
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label' => esc_html__('Icon', 'omero'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
                'condition' => $args['section_condition'],
                'icon_exclude_inline_options' => $args['icon_exclude_inline_options'],
            ]
        );

        $this->add_control(
            'icon_align',
            [
                'label' => esc_html__('Icon Position', 'omero'),
                'type' => Controls_Manager::CHOOSE,
                'default' => is_rtl() ? 'row-reverse' : 'row',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors_dictionary' => [
                    'left' => is_rtl() ? 'row-reverse' : 'row',
                    'right' => is_rtl() ? 'row' : 'row-reverse',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button-content-wrapper' => 'flex-direction: {{VALUE}};',
                ],
                'condition' => array_merge($args['section_condition'], ['selected_icon[value]!' => '']),
                'prefix_class' => 'icon-omero-',
            ]
        );

        $this->add_control(
            'icon_indent',
            [
                'label' => esc_html__('Icon Spacing', 'omero'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.icon-omero-left .elementor-button .hover-text' => 'margin-left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.icon-omero-right .elementor-button .hover-text' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'icon_width',
            [
                'label' => esc_html__('Size Icon', 'omero'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'view',
            [
                'label' => esc_html__('View', 'omero'),
                'type' => Controls_Manager::HIDDEN,
                'default' => 'traditional',
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'button_css_id',
            [
                'label' => esc_html__('Button ID', 'omero'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => '',
                'title' => esc_html__('Add your custom id WITHOUT the Pound key. e.g: my-id', 'omero'),
                /* translators: %1$s Code open tag, %2$s: Code close tag. */
                'description' => esc_html__('Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows <code>A-z 0-9</code> & underscore chars without spaces.', 'omero'),
                'separator' => 'before',
                'condition' => $args['section_condition'],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Button', 'omero'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $default_args = [
            'section_condition' => [],
        ];

        $args = wp_parse_args($args, $default_args);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .elementor-button',
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .elementor-button',
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_responsive_control(
            'border_width',
            [
                'label' => esc_html__('Border Width', 'omero'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 5,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .omero-path-wrapper.omero-path-border .path-border' => 'stroke-width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'button_type' => 'outline'
                ],
            ]
        );

        $this->start_controls_tabs('tabs_button_style', [
            'condition' => $args['section_condition'],
        ]);

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__('Normal', 'omero'),
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__('Text Color', 'omero'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button .hover-text span' => 'color: {{VALUE}};',
                    '{{WRAPPER}}.elementor-button-type-link .elementor-button:after' => 'background-color: {{VALUE}};',
                ],
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label' => esc_html__('Border Color', 'omero'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button .path-border' => 'stroke: {{VALUE}};',
                ],
                'condition' => [
                    'button_type' => 'outline'
                ],
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => esc_html__('Background Color', 'omero'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__('Hover', 'omero'),
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'hover_color',
            [
                'label' => esc_html__('Text Color', 'omero'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button .hover-text:before, {{WRAPPER}} .elementor-button:focus .hover-text' => 'color: {{VALUE}};',
                    '{{WRAPPER}}.elementor-button-type-link .elementor-button:hover:after' => 'background-color: {{VALUE}};',
                ],
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'border_color_hover',
            [
                'label' => esc_html__('Border Color', 'omero'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover .path-border' => 'stroke: {{VALUE}};',
                ],
                'condition' => [
                    'button_type' => 'outline'
                ],
            ]
        );

        $this->add_control(
            'background_color_hover',
            [
                'label' => esc_html__('Background Color', 'omero'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}' => '--bg-hover: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'text_padding',
            [
                'label' => esc_html__('Padding', 'omero'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => $args['section_condition'],
            ]
        );

        $this->add_control(
            'button_radius',
            [
                'label' => esc_html__('Border Radius', 'omero'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'border-radius: {{SIZE}}{{UNIT}}; --path-radius: {{SIZE}}',
                ],
                'condition' => $args['section_condition'],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render button widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render()
    {
        $this->render_button();
    }

    /**
     * Render button widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @param \Elementor\Widget_Base|null $instance
     *
     * @since  3.4.0
     * @access protected
     */
    protected function render_button(Widget_Base $instance = null)
    {
        if (empty($instance)) {
            $instance = $this;
        }

        $settings = $instance->get_settings_for_display();

        $instance->add_render_attribute('wrapper', 'class', 'elementor-button-wrapper');

        if (isset($settings['button_type']) && $settings['button_type'] === 'outline') {
            $instance->add_render_attribute('button', 'class', 'omero-path-border');
        }

        if (!empty($settings['link']['url'])) {
            $instance->add_link_attributes('button', $settings['link']);
            $instance->add_render_attribute('button', 'class', 'elementor-button-link');
        }

        $instance->add_render_attribute('button', 'class', 'omero-path-wrapper');
        $instance->add_render_attribute('button', 'class', 'elementor-button');
        $instance->add_render_attribute('button', 'role', 'button');

        if (!empty($settings['button_css_id'])) {
            $instance->add_render_attribute('button', 'id', $settings['button_css_id']);
        }

        if (!empty($settings['size'])) {
            $instance->add_render_attribute('button', 'class', 'elementor-size-' . $settings['size']);
        }

        if (!empty($settings['hover_animation'])) {
            $instance->add_render_attribute('button', 'class', 'elementor-animation-' . $settings['hover_animation']);
        }
        ?>
        <div <?php $instance->print_render_attribute_string('wrapper'); ?>>
            <a <?php $instance->print_render_attribute_string('button'); ?>>
                <?php $this->render_text($instance); ?>
            </a>
        </div>
        <?php
    }

    /**
     * Render button text.
     *
     * Render button widget text.
     *
     * @param \Elementor\Widget_Base|null $instance
     *
     * @since  3.4.0
     * @access protected
     */
    protected function render_text(Widget_Base $instance = null)
    {
        // The default instance should be `$this` (a Button widget), unless the Trait is being used from outside of a widget (e.g. `Skin_Base`) which should pass an `$instance`.
        if (empty($instance)) {
            $instance = $this;
        }

        $settings = $instance->get_settings_for_display();

        $migrated = isset($settings['__fa4_migrated']['selected_icon']);
        $is_new = empty($settings['icon']) && Icons_Manager::is_migration_allowed();

        if (!$is_new && empty($settings['icon_align'])) {
            // @todo: remove when deprecated
            // added as bc in 2.6
            //old default
            $settings['icon_align'] = $instance->get_settings('icon_align');
        }

        $instance->add_render_attribute([
            'content-wrapper' => [
                'class' => 'elementor-button-content-wrapper',
            ],
            'icon-align' => [
                'class' => [
                    'elementor-button-icon',
                    'elementor-align-icon-' . $settings['icon_align'],
                ],
            ],

            'text' => [
                'class' => 'hover-text',
            ],
        ]);

        ?>
        <span <?php $instance->print_render_attribute_string('content-wrapper'); ?>>
           <?php if (!empty($settings['icon']) || !empty($settings['selected_icon']['value'])) : ?>
               <span <?php $instance->print_render_attribute_string('icon-align'); ?>>
				<?php if ($is_new || $migrated) :
                    Icons_Manager::render_icon($settings['selected_icon'], ['aria-hidden' => 'true']);
                else : ?>
                    <i class="<?php echo esc_attr($settings['icon']); ?>" aria-hidden="true"></i>
                <?php endif; ?>
			</span>
           <?php endif; ?>
			<span <?php $instance->print_render_attribute_string('text'); ?> data-text="<?php $this->print_unescaped_setting('text'); ?>">
                <span><?php $this->print_unescaped_setting('text'); ?></span>
            </span>
		</span>
        <?php
    }
}

$widgets_manager->register(new Omero_Widget_Button());

<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Group_Control_Typography;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Modules\Shapes\Module as Shapes_Module;
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
class Omero_Elementor_Text_Scrolling extends Elementor\Widget_Base {

    public function get_categories() {
        return array('portfolio-addons');
    }

    /**
     * Get widget name.
     *
     * Retrieve tabs widget name.
     *
     * @return string Widget name.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_name() {
        return 'portfolio-text-scrolling';
    }

    /**
     * Get widget title.
     *
     * Retrieve tabs widget title.
     *
     * @return string Widget title.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_title() {
        return esc_html__('Omero Text Scrolling', 'omero');
    }

    /**
     * Get widget icon.
     *
     * Retrieve tabs widget icon.
     *
     * @return string Widget icon.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_icon() {
        return 'eicon-post-slider';
    }

    /**
	 * Get style dependencies.
	 *
	 * Retrieve the list of style dependencies the widget requires.
	 *
	 * @access public
	 *
	 * @return array Widget style dependencies.
	 */
	public function get_style_depends(): array {
		return [ 'widget-text-path' ];
	}

    /**
     * Register tabs widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {

        $this->start_controls_section(
            'section_scrolling',
            [
                'label' => esc_html__('Items', 'omero'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'scrolling_title',
            [
                'label'       => esc_html__('Scrolling name', 'omero'),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__('Scrolling Name', 'omero'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'scrolling_title_color',
            [
                'label'     => esc_html__('Color Title', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}}.scrolling-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $repeater->add_control(
            'selected_icon',
            [
                'label' => esc_html__( 'Icon', 'omero' ),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'scrolling',
            [
                'label'       => esc_html__('Items', 'omero'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ scrolling_title }}}',
            ]
        );


        $this->add_control(
            'heading_settings',
            [
                'label'     => esc_html__('Settings', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'scrolling_align',
            [
                'label'     => esc_html__('Alignment', 'omero'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'flex-start' => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'   => 'center',
                'selectors' => [
                    '{{WRAPPER}} .elementor-scrolling-wrapper .elementor-scrolling-item-inner' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_spacing',
            [
                'label'      => esc_html__('Spacing', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', 'em'],
                'selectors'  => [
//                     '{{WRAPPER}} .elementor-scrolling-wrapper .elementor-scrolling-inner' => 'margin-left: calc(-{{SIZE}}{{UNIT}}/2); margin-right: calc(-{{SIZE}}{{UNIT}}/2);',
'{{WRAPPER}} .elementor-scrolling-wrapper .elementor-scrolling-item'  => 'margin-left: calc({{SIZE}}{{UNIT}}/2); margin-right: calc({{SIZE}}{{UNIT}}/2);',
                ],
            ]
        );

        $this->add_responsive_control(
            'duration',
            [
                'label'     => esc_html__('Scrolling duration', 'omero'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 10,
                'selectors' => [
                    '{{WRAPPER}} .elementor-scrolling-inner' => 'animation-duration: {{VALUE}}s',
                ],
            ]
        );

        $this->add_control(
            'scroll_direction',
            [
                'label'     => esc_html__('Direction', 'omero'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'vertical' => [
                        'title' => esc_html__('Vertical', 'omero'),
                        'icon'  => 'eicon-navigation-vertical',
                    ],
                    'horizontal'     => [
                        'title' => esc_html__('Horizontal', 'omero'),
                        'icon'  => 'eicon-navigation-horizontal',
                    ],
                ],
                'default'   => 'horizontal',
                'prefix_class' => 'omero-textscroll-'
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_scrolling_item',
            [
                'label' => esc_html__('Item', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'scrolling_item',
                'selector' => '{{WRAPPER}} .elementor-scrolling-item-inner',
            ]
        );
        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-scrolling-item-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();

        // Title.
        $this->start_controls_section(
            'section_style_scrolling_title',
            [
                'label' => esc_html__('Title', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_text_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .scrolling-title'  => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_text_color_hover',
            [
                'label'     => esc_html__('Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .scrolling-title:hover' => 'color: {{VALUE}};',

                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .scrolling-title',
            ]
        );

        $this->add_control(
            'scrolling_title_gradient_style',
            [
                'label'     => esc_html__( 'Title Color Gradient', 'omero' ),
                'type'      => Controls_Manager::SWITCHER,
                'default' => '',
                'prefix_class'	=> 'omero-scrolling-gradient-style-'
            ]
        );

        $this->end_controls_section();

        // Icon.
        $this->start_controls_section(
            'section_style_scrolling_icon',
            [
                'label' => esc_html__('Icon', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label'          => esc_html__('Icon size', 'omero'),
                'type'           => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 30,
                        'max' => 500,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'      => [
                    '{{WRAPPER}}.elementor-widget-portfolio-text-scrolling .elementor-text-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_scrolling_space',
            [
                'label'     => esc_html__('Icon Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-text-icon' => 'color: {{VALUE}};',

                ],
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'icon_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-text-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render tabs widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        if (!empty($settings['scrolling']) && is_array($settings['scrolling'])) {

            $this->add_render_attribute('wrapper', 'class', 'elementor-scrolling-wrapper');


            $this->add_render_attribute('item', 'class', 'elementor-scrolling-item');

            if (!empty($settings['icon_border_style']) && $settings['icon_border_style'] == 'yes') {
                if (!empty($settings['icon_textpath'])) {
                    $icon_textpath = $settings['icon_textpath'];

                    $path_url = Shapes_Module::get_path_url( 'circle' );
                    // Remove the HTTP protocol to prevent Mixed Content error.
                    $path_url = preg_replace( '/^https?:/i', '', $path_url );

                    // Add Text Path attributes.
                    $this->add_render_attribute( 'text_path', [
                        'class' => 'e-text-path',
                        'data-text' => htmlentities( esc_attr( $icon_textpath) ),
                        'data-url' => esc_url( $path_url ),
                        // 'data-link-url' => esc_url( $settings['link']['url'] ?? '' ),
                    ] );
                }
            }
            ?>
            <div class="elementor-scrolling" aria-hidden="true" inert>
                <div <?php $this->print_render_attribute_string('wrapper'); ?>>
                    <?php
                    for ($i = 0; $i <= 3; $i++) {
                        ?>
                        <div class="elementor-scrolling-inner" aria-hidden="true" inert>
                            <?php foreach ($settings['scrolling'] as $item) :
                                $has_icon = ! empty( $item['selected_icon']['value'] );

                                if ( ! isset( $item['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
                                    // add old default
                                    $item['icon'] = 'fa fa-star';
                                }

                                $this->remove_render_attribute('i');
                                if ( ! empty( $item['icon'] ) ) {
                                    $this->add_render_attribute( 'i', 'class', $item['icon'] );
                                    $this->add_render_attribute( 'i', 'aria-hidden', 'true' );
                                }

                                $migrated = isset( $item['__fa4_migrated']['selected_icon'] );
                                $is_new = ! isset( $item['icon'] ) && Icons_Manager::is_migration_allowed();
                                ?>
                                <div <?php $this->print_render_attribute_string('item'); ?> aria-hidden="true" inert >
                                    <div class="elementor-scrolling-item-inner">
                                        <span class="elementor-text-icon">
                                        <?php
                                        if ( $is_new || $migrated ) {
                                            Icons_Manager::render_icon( $item['selected_icon'], [ 'aria-hidden' => 'true' ] );
                                        } elseif ( ! empty( $item['icon'] ) ) {
                                            ?><i <?php $this->print_render_attribute_string( 'i' ); ?>></i><?php
                                        }
                                        ?>

                                    </span>
                                    <?php if ( $has_icon ) : ?>
                                    <?php endif; ?>
                                        <?php if ($item['scrolling_title']) { ?>
                                            <div class="scrolling-title elementor-repeater-item-<?php echo esc_attr($item['_id']); ?>" data-text="<?php echo esc_attr($item['scrolling_title']); ?>"></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <style type="text/css">
                    /* Text Scrolling: render title via ::before so text lives in CSS, not HTML */
                    .elementor-widget-portfolio-text-scrolling .scrolling-title::before {
                        content: attr(data-text);
                    }

                    .elementor-widget-portfolio-text-scrolling.omero-scrolling-gradient-style-yes .scrolling-title {
                        background: none;
                        -webkit-text-fill-color: unset;
                        opacity: 1;
                    }

                    .elementor-widget-portfolio-text-scrolling.omero-scrolling-gradient-style-yes .scrolling-title::before {
                        background: linear-gradient(180deg, #1D1C1D 0%, rgba(131, 124, 131, 0.24) 93.67%);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                        opacity: 0.14;
                    }
                </style>
            </div>
            <?php
        }
    }
}

$widgets_manager->register(new Omero_Elementor_Text_Scrolling());
<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!post_type_exists('game')) {
    return;
}

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Omero\Elementor\Omero_Base_Widgets;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Omero\Elementor\Omero_Group_Control_Typography;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Omero_Elementor_Widget_Game_Meta extends \Elementor\Widget_Base {


    public function get_categories() {
        return array('omero-addons');
    }

    /**
     * Get widget name.
     *
     * Retrieve tabs widget name.
     *
     * @return string Widget name.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_name() {
        return 'omero-game-meta';
    }

    /**
     * Get widget title.
     *
     * Retrieve tabs widget title.
     *
     * @return string Widget title.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_title() {
        return esc_html__('Omero Game Meta', 'omero');
    }

    /**
     * Get widget icon.
     *
     * Retrieve tabs widget icon.
     *
     * @return string Widget icon.
     * @since  1.0.0
     * @access public
     *
     */
    public function get_icon() {
        return 'eicon-archive';
    }

    /**
     * Register tabs widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function register_controls() {
        //Section Query
        $this->start_controls_section(
            'section_setting',
            [
                'label' => esc_html__('Settings', 'omero'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'game_show',
            [
                'label'        => esc_html__('Game Show', 'omero'),
                'type'         => Controls_Manager::SELECT,
                'default'      => 'single',
                'options'      => [
                    'single' => __('Single/Loop Game', 'omero'),
                    'select' => __('Selected Game', 'omero'),
                ],
            ]
        );

        $this->add_control(
            'choose_game',
            [
                'label'     => __('Game', 'omero'),
                'type'      => 'game',
                'multiple'    => false,
                'condition' => [
                    'game_show' => 'select'
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'meta_type',
            [
                'type'    => Controls_Manager::HIDDEN,
                'default' => '',
            ]
        );
        $repeater->add_control(
            'meta_show',
            [
                'type'    => Controls_Manager::SWITCHER,
                'label'       => esc_html('Show meta', 'omero'),
                'default' => 'yes',
            ]
        );
        $repeater->add_control(
            'meta_title',
            [
                'label'       => esc_html__('Meta title', 'omero'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [
                    'active' => true,
                ],
                'default'     => '',
                'placeholder' => esc_html__('Type something...', 'omero'),
            ]
        );
        $repeater->add_control(
            'display_type',
            [
                'label'       => esc_html__('Display Type', 'omero'),
                'type'        => Controls_Manager::SELECT,
                'options'     => [
                    'text' => 'Text',
                    'icon' => 'Icon',
                ],
                'default'     => 'text',
                'condition' => [
                    'meta_type' => 'platform',
                ]
            ]
        );

        $this->add_control(
            'meta_list',
            [
                'label'       => esc_html__('Meta Show', 'omero'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ meta_type.charAt(0).toUpperCase() + meta_type.slice(1) }}}',
                'min_items' => 6,
                'max_items' => 6,
                'default' => [
					[
						'meta_type' => 'genre',
						'meta_title' => esc_html__( 'Genres', 'omero' ),
					],
					[
						'meta_type' => 'platform',
						'meta_title' => esc_html__( 'Platforms', 'omero' ),
					],
					[
						'meta_type' => 'website',
						'meta_title' => esc_html__( 'Website', 'omero' ),
					],
					[
						'meta_type' => 'date',
						'meta_title' => esc_html__( 'Release date', 'omero' ),
					],
					[
						'meta_type' => 'age_rating',
						'meta_title' => esc_html__( 'Age rating', 'omero' ),
					],
					[
						'meta_type' => 'number_of_players',
						'meta_title' => esc_html__( 'Number of players', 'omero' ),
					],
				],
            ]
        );

        $this->add_control(
            'hide_empty',
            [
                'type'    => Controls_Manager::SWITCHER,
                'label'       => esc_html('Hide Empty', 'omero'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_game_meta_style',
            [
                'label' => esc_html__('Content', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'meta_title',
            [
                'label'     => esc_html__('Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'meta_title_typography',
                'selector' => '{{WRAPPER}} .game-meta-title',
            ]
        );

        $this->add_control(
            'meta_title_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .game-meta-title'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'meta_title_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .game-meta-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'meta_info',
            [
                'label'     => esc_html__('Info', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'meta_info_typography',
                'selector' => '{{WRAPPER}} .game-meta-content .meta-data',
            ]
        );

        $this->add_control(
            'meta_info_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .game-meta-content .meta-data'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .game-list-terms.game_platform .term-logo svg path'   => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->get_controls_column();
    }

    /**
     * Render tabs widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $this->handle_setting($settings);
    }

    private function handle_setting($settings) {
        $game_show = empty($settings['game_show']) ? 'single' : $settings['game_show'];
        if ($game_show == 'single') {
            if (is_singular('game') || get_post_type() == 'game') {
                $game_id = get_the_ID();
            }
        } else {
            if (!empty($settings['choose_game'])) {
                $game_id = absint($settings['choose_game']);
            }
        }
        $game_id = $game_id ?? omero_get_default_game();
        $hide_empty = $settings['hide_empty'] ?? 'no';
        $meta_html = '';
        if (!empty($settings['meta_list'])) {
            foreach ($settings['meta_list'] as $field ) {
                if (empty($field['meta_show']) || $field['meta_show'] !== 'yes') {
                    continue;
                }
                
                $type = $field['meta_type'] ?? '';
                $title = !empty($field['meta_title']) ? $field['meta_title'] : '';
                $content = '';

                if (in_array($type, ['genre', 'platform'])) {
                    if ($type == 'platform' && isset($field['display_type']) && $field['display_type'] == 'icon') {
                        ob_start();
                        omero_game_list_terms('game_platform', $game_id, false);
                        $content = ob_get_clean();
                    } else {
                        $terms = get_the_terms($game_id, 'game_'.$type);
                        if (!empty($terms) && !is_wp_error($terms)) {    
                            $term_names = wp_list_pluck($terms, 'name');
                            $content = implode(', ', $term_names);
                        }
                    }
                } else {
                    $meta_value = get_post_meta( $game_id, '_game_'.$type, true );
                    if (!empty($meta_value)) {
                        $content = $meta_value;
                    }
                }

                if (!empty($content)) {
                    $meta_html .= self::get_meta_html($title, $content);
                }
                elseif ($hide_empty !== 'yes') {
                    $meta_html .= self::get_meta_html($title, 'N/A');
                }

            }
        }

        if (!empty($meta_html)) {
            ?>
            <div class="elementor-widget-inner">
                <div class="omero-wrapper">
                    <div class="omero-con-inner elementor-grid">
                        <?php printf('%s', $meta_html) ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    protected static function get_meta_html($title = '', $content = '') {
        $html = '<div class="game-meta-item"><div class="game-meta-inner"><div class="game-meta-content">';
        if (!empty($title)) {
            $html .= sprintf('<div class="game-meta-title">%s</div>', wp_kses_post($title));
        }
        $html .= sprintf('<div class="meta-data">%s</div>', $content);
        $html .= '</div></div></div>';
        
        return $html;
    }

    protected function get_controls_column($condition = false) {
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
                'default'            => 2,
                'options'            => [
                    '' => esc_html__('Default', 'omero'),
                ] + $column,
                'frontend_available' => true,
                'render_type'        => 'template',
                'prefix_class'       => 'omero-list-template elementor-grid%s-',
                'selectors'          => [
                    '{{WRAPPER}}' => '--e-global-column-to-show: {{VALUE}}',
                ],
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

$widgets_manager->register(new Omero_Elementor_Widget_Game_Meta());

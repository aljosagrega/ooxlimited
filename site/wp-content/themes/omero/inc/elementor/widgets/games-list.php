<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!post_type_exists('game')) {
    return;
}

use Omero\Elementor\Omero_Base_Widgets;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Text_Shadow;
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
class Omero_Elementor_Widget_Game extends Omero_Base_Widgets {


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
        return 'omero-games-list';
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
        return esc_html__('Omero Games List', 'omero');
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
        return 'eicon-shape';
    }

    public function get_script_depends() {
        return [
            'omero-elementor-games-list',
            'omero-scrolltrigger',
            'omero-gsap',
        ];
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
            'limit',
            [
                'label'   => esc_html__('Posts Per Page', 'omero'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
            ]
        );

        $this->add_control(
            'paginate',
            [
                'label'   => esc_html__('Paginate', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'       => esc_html__('None', 'omero'),
                    'pagination' => esc_html__('Pagination', 'omero'),
                    'loadmore' => esc_html__('Load More', 'omero'),
                ],
                'condition'          => [
                    'enable_carousel!' => 'yes'
                ],
            ]
        );

        $this->add_responsive_control(
            'paginate_margin',
            [
                'label'      => esc_html__('Paginate Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .pagination'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'paginate!' => 'none',
                    'enable_carousel!' => 'yes'
                ]
            ]
        );

        $this->add_responsive_control(
            'paginate_align',
            [
                'label'        => esc_html__('Paginate Align', 'omero'),
                'type'         => Controls_Manager::CHOOSE,
                'options'      => [
                    'left'    => [
                        'title' => esc_html__('Left', 'omero'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'omero'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'omero'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .omero-loadmore' => 'text-align: {{value}}',
                    '{{WRAPPER}} .pagination ul.page-numbers' => 'justify-content: {{value}}',
                ],
                'condition' => [
                    'paginate!' => 'none',
                    'enable_carousel!' => 'yes'
                ],
                'separator' => 'after'
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => esc_html__('Order By', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date'       => esc_html__('Date', 'omero'),
                    'id'         => esc_html__('Game ID', 'omero'),
                    'menu_order' => esc_html__('Menu Order', 'omero'),
                    'title'      => esc_html__('Game Title', 'omero'),
                    'rand'       => esc_html__('Random', 'omero'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => esc_html__('Order', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => [
                    'asc'  => esc_html__('ASC', 'omero'),
                    'desc' => esc_html__('DESC', 'omero'),
                ],
            ]
        );

        $this->add_group_control(
            Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'      => 'image_thumbnail',
                'default'   => 'medium_large',
                'exclude' => ['custom']
            ]
        );

        $this->add_control(
            'style',
            [
                'label'     => esc_html__('Block Style', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'normal',
                'options' => [
                    'normal' => esc_html__('Style Normal', 'omero'),
                    'overlay' => esc_html__('Style Overlay', 'omero'),
                ],
            ]
        );

        $this->add_control(
            'hide_platform',
            [
                'type'    => Controls_Manager::SWITCHER,
                'label'       => esc_html('Hide Platform', 'omero'),
                'prefix_class' => 'elementor-omero-hide-platform-',
                'condition' => [
                    'style' => 'overlay',
                ]
            ]
        );

        $this->add_control(
            'bottom_shape',
            [
                'type'    => Controls_Manager::SWITCHER,
                'label'       => esc_html('Bottom Shape', 'omero'),
                'prefix_class' => 'elementor-omero-game-shape-',
                'condition' => [
                    'style' => 'overlay',
                    'column' => '1'
                ]
            ]
        );

        $this->add_control(
            'show_filter',
            [
                'type'    => Controls_Manager::SWITCHER,
                'label'       => esc_html('Show Filter Form', 'omero'),
                'prefix_class' => 'elementor-omero-show-filter-',
                'render_type' => 'template',
                'condition' => [
                    'enable_carousel!' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'taxonomies_filter',
            [
                'label'   => esc_html__('Choose Taxonomies Filter', 'omero'),
                'type'    => Controls_Manager::SELECT2,
                // 'default' => 'none',
                'options' => self::get_taxonomy_options(),
                'multiple' => true,
                'label_block' => true,
                'condition'          => [
                    'enable_carousel!' => 'yes',
                    'show_filter' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'scroll_sticky',
            [
                'label'   => esc_html__('Scroll Sticky', 'omero'),
                'default' => 'yes',
                'type'    => Controls_Manager::SWITCHER,
                'condition' => [
                    'style' => 'overlay',
                    'column' => '1',
                    'enable_carousel!' => 'yes',
                ],
                'prefix_class' => 'omero-scroll-sticky-'
            ]
        );

        $this->add_control(
            'scroll_offset',
            [
                'label'      => esc_html__('Sticky Offset', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'size_units' => ['px'],
                'condition' => [
                    'style' => 'overlay',
                    'column' => '1',
                    'enable_carousel!' => 'yes',
                    'scroll_sticky' => 'yes',
                ],
                'render_type' => 'template'
            ]
        );
        
        $this->end_controls_section();

        $this->start_controls_section(
            'section_wrapper_game_style',
            [
                'label' => esc_html__('Wrapper', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'style' => 'normal',
                ]
            ]
        );

        $this->add_control(
            'wrapper_background_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-game-block'   => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'style' => 'normal',
                ]
            ]
        );

        $this->add_responsive_control(
            'wrapper_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-game-block' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'style' => 'normal',
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_image_game_style',
            [
                'label' => esc_html__('Image', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label'      => esc_html__('Image Height', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                ],
                'size_units' => ['px', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .post-thumbnail .thumbnail-image' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'img_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-game.omero-list-wrapper .post-thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .omero-game.omero-list-wrapper .post-thumbnail img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .omero-game.omero-list-wrapper .post-thumbnail a:before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_game_style',
            [
                'label' => esc_html__('Content', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'box_caption_head',
            [
                'label'     => esc_html__('Box Caption', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'condition' => [
                    'style' => 'normal',
                ]
            ]
        );

        $this->add_responsive_control(
            'caption_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .game-caption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'style' => 'normal',
                ]
            ]
        );

        $this->add_responsive_control(
            'caption_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .game-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'style' => 'normal',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(), [
                'name'      => 'caption_border',
                'selector'  => '{{WRAPPER}} .game-caption',
                'separator' => 'after',
                'condition' => [
                    'style' => 'normal',
                ]
            ]
        );

        $this->add_control(
            'box_content_head',
            [
                'label'     => esc_html__('Box Content', 'omero'),
                'type'      => Controls_Manager::HEADING,
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label'      => esc_html__('Content Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .game-content-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .omero-game.omero-list-wrapper .game-content-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'box_exerpt',
            [
                'label'     => esc_html__('Game Exerpt', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );


        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'exerpt_typography',
                'selector' => '{{WRAPPER}} .object-loop-exerpt',
            ]
        );

        $this->add_control(
            'exerpt_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'global'    => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .object-loop-exerpt'   => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'box_title_head',
            [
                'label'     => esc_html__('Game Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'title_width',
            [
                'label'      => esc_html__('Title Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .object-loop-title' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );


        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .object-loop-title, {{WRAPPER}} .object-loop-title a',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .object-loop-title a'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label'     => esc_html__('Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .object-loop-title a:hover'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .object-loop-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_logo_game_style',
            [
                'label' => esc_html__('Logo', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'logo_width',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .game-studio-logo img' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'logo_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .game-studio-logo' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        $this->get_controls_column(false, 2);
        
        $this->get_control_carousel([
            'show_filter!' => 'yes',
            'paginate' => 'none',
        ]);
    }

    protected static function get_taxonomy_options() {
        $taxonomies = get_object_taxonomies( 'game', 'objects' );
        if (empty($taxonomies)) {
            return [];
        }

        $options = [];
        foreach ( $taxonomies as $key => $taxonomy ) {
            if ( $taxonomy->public ) {
                $options[$key] = $taxonomy->label;
            }
        }

        return $options;
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

        $class = '';
        $atts  = [
            'limit'           => $settings['limit'],
            'columns'         => $settings['enable_carousel'] === 'yes' ? 1 : $settings['column'],
            'orderby'         => $settings['orderby'],
            'order'           => $settings['order'],
            'image_size'      => $settings['image_thumbnail_size'] ?? 'large',
            'show_index'      => 0,
        ];

        if (!empty($settings['show_index'])) {
            $atts['show_index'] = $settings['show_index'] === 'yes';
        }

        $class         .= ' elementor-game elementor-widget-render';

        if (isset($settings['style']) && $settings['style'] !== '') {
            $atts['style'] = $settings['style'];
        }

        // Carousel
        if ($settings['enable_carousel'] === 'yes') {
            $atts['enable_carousel']   = 'yes';
            $atts['carousel_settings'] = $this->get_swiper_navigation_for_game();
            $class                     = ' omero-swiper-wrapper swiper';
        }
        if ($settings['paginate'] !== 'none' && $settings['enable_carousel'] !== 'yes') {
            $atts['paginate'] = true;
            $atts['paginate_type'] = empty($settings['paginate']) ? 'pagination' : $settings['paginate'];
        }
        $atts['class'] = $class;

        if (isset($settings['show_filter']) && $settings['show_filter'] === 'yes') {
            if (!empty($settings['taxonomies_filter'])) {
                $this->render_form_filter($settings['taxonomies_filter']);
            }
        }

        $this->get_query_var_term($atts);

        $offset_sticky = isset($settings['scroll_offset']['size']) ? $settings['scroll_offset']['size'] : 40;
        if (isset($settings['scroll_sticky']) && $settings['scroll_sticky'] === 'yes') {
            printf(
                '<span id="scroll-offset-%s" data-sticky-offset="%d" class="scroll-offset-data d-none"></span>',
                esc_attr($this->get_id()),
                absint($offset_sticky)
            );
        }

        echo (new Omero_Posttype('game', $atts))->get_content(); // WPCS: XSS ok
    }

    protected function get_query_var_term(&$atts) {
        $taxonomy_options = self::get_taxonomy_options();
        $terms_query = [];
        foreach ($taxonomy_options as $taxonomy => $label) {
            if (!empty($_GET[$taxonomy])) {
                $terms_query[$taxonomy] = [
                    'terms' => sanitize_text_field($_GET[$taxonomy]),
                    'operator' => 'IN'
                ];
            }
        }
        
        if (empty($terms_query)) {
            return;
        }

        $atts['taxs_query'] = [
            'terms_query' => $terms_query
        ];
    }

    protected function render_form_filter($taxonomies) {
        $taxonomy_options = self::get_taxonomy_options();
        $list_field = '';
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => true
            ]);

            if (empty($terms)) {
                continue;
            }
            if (!isset($taxonomy_options[$taxonomy])) {
                continue;
            }

            $label_tax = $taxonomy_options[$taxonomy];

            $options = sprintf(
                '<option value="">%s %s</option>', 
                esc_html__('All', 'omero'),
                esc_html($label_tax),
            );
            foreach ($terms as $term) {
                $term_slug = $term->slug;
                $term_name = $term->name;
                $options .= sprintf(
                    '<option value="%s">%s</option>',
                    esc_attr($term_slug),
                    esc_attr($term_name),
                );
            }
            $list_field .= sprintf(
                '<div class="field-wrapper">
                    <div class="field-inner">
                        <label class="field-label">%s</label>
                        <select class="fitler-field" name="%s">%s</select>
                    </div>
                </div>',
                sprintf(__('Filter By %s', 'omero'), ucfirst(esc_html($label_tax))),
                esc_attr($taxonomy),
                $options
            );
        }

        if (empty($list_field)) {
            return;
        }
        ?>
        <form class="filter-form" id="filter-form-<?php echo esc_attr($this->get_id()) ?>">
            <?php printf('%s', $list_field); ?>
            <div class="wrapper-button-submit">
                <button type="button" id="submit-filter-<?php echo esc_attr($this->get_id()) ?>" class="submit-filter omero-path-wrapper btn-slip-effect">
                    <span class="elementor-button-content-wrapper">
                        <span class="hover-text" data-text="<?php echo esc_attr('Filter', 'omero') ?>">
                            <span><?php echo esc_html('Filter', 'omero') ?></span>
                        </span>
                        <i aria-hidden="true" class="omero-icon-arrow-right1"></i>
                    </span>
                </button>
            </div>
        </form>
        <?php
    }

    protected function get_swiper_navigation_for_game() {
        $settings = $this->get_settings_for_display();
        if ($settings['enable_carousel'] != 'yes') {
            return;
        }
        $settings_navigation = '';
        $show_dots           = (in_array($settings['navigation'], ['dots', 'both']));
        $show_arrows         = (in_array($settings['navigation'], ['arrows', 'both']));


        if ($show_dots) {
            $settings_navigation .= '<div class="swiper-pagination swiper-pagination-' . $this->get_id() . '"></div>';
        }
        if ($show_arrows && $settings['custom_navigation'] != 'yes') {
            $settings_navigation .= '<div class="elementor-swiper-button elementor-swiper-button-prev elementor-swiper-button-prev-' . $this->get_id() . '">';
            $settings_navigation .= $this->render_swiper_button('previous', true);
            $settings_navigation .= '<span class="elementor-screen-only">' . esc_html__('Previous', 'omero') . '</span>';
            $settings_navigation .= '</div>';
            $settings_navigation .= '<div class="elementor-swiper-button elementor-swiper-button-next elementor-swiper-button-next-' . $this->get_id() . '">';
            $settings_navigation .= $this->render_swiper_button('next', true);
            $settings_navigation .= '<span class="elementor-screen-only">' . esc_html__('Next', 'omero') . '</span>';
            $settings_navigation .= '</div>';
        }
        return $settings_navigation;
    }
}

$widgets_manager->register(new Omero_Elementor_Widget_Game());

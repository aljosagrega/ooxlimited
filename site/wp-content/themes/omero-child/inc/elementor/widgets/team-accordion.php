<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!post_type_exists('team')) {
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
use Elementor\Group_Control_Text_Stroke;
use Omero\Elementor\Omero_Group_Control_Typography;
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
class Omero_Elementor_Widget_Team_Accordion extends Omero_Base_Widgets {

    private $image_size = 'large';
    
    private $layout = '1';


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
        return 'omero-teams-accordion';
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
        return esc_html__('Omero Teams Accordion', 'omero');
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
        return 'eicon-accordion';
    }


    public function get_script_depends() {
        return [
            'omero-elementor-team-accordion',
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
            'show_option',
            [
                'label'   => esc_html__('Show Team', 'omero'),
                'type'    => Controls_Manager::HIDDEN,
                'default' => 'select',
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'selected_icon',
            [
                'label' => esc_html__('Icon', 'omero'),
                'type'  => Controls_Manager::HIDDEN,
            ]
        );

        $repeater->add_control(
            'choose_team',
            [
                'label'     => __('Team', 'omero'),
                'type'      => 'team',
                'multiple'    => false,
                'label_block' => true,
                // 'separator' => 'before'
            ]
        );

        $this->add_control(
            'teams_list',
            [
                'label'       => esc_html__('Teams', 'omero'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default' => [
                    [
                        'selected_icon' => '',
                    ],
                ],
                'condition' => [
                    'show_option' => 'select'
                ],
                'separator' => 'after'
            ]
        );

        $this->add_control(
            'advanced',
            [
                'label' => esc_html__('Advanced', 'omero'),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => esc_html__('Order By', 'omero'),
                'type'    => Controls_Manager::HIDDEN,
                'default' => 'post__in',
                
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
                'default'   => 'large',
                'exclude' => ['custom']
            ]
        );

        $this->add_control(
            'layout',
            [
                'label'   => esc_html__('Layout', 'omero'),
                'type'    => Controls_Manager::HIDDEN,
                'default' => '1',
                'render_type' => 'template',
                'prefix_class' => 'omero-team-accordion-layout-'
            ]
        );

        $this->end_controls_section();


        //Section Title
        $this->start_controls_section(
            'section_team_title',
            [
                'label' => esc_html__('Side Left', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'image_team',
            [
                'label'     => esc_html__('Image', 'omero'),
                'type'      => Controls_Manager::HEADING,
                //'separator' => 'before'
            ]
        );

        $this->add_control(
            'image_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-list-wrapper li.team .team-block' => 'border-radius: {{SIZE}}{{UNIT}}; --path-radius: {{SIZE}}',
                ],
            ]
        );

        $this->end_controls_section();


        //Section Query
        $this->start_controls_section(
            'section_team_style',
            [
                'label' => esc_html__('Side Right', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'box_title_list',
            [
                'label'     => esc_html__('Team List', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'list_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-team-item-titles'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding_list',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-team-item-titles'    => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'list_border_color',
            [
                'label'     => esc_html__('Border color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-team-side-titles li.omero-team-item-titles' => 'border-color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'box_title_head',
            [
                'label'     => esc_html__('Team Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .omero-team-item-titles .team-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .omero-team-item-titles .team-title'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-team-item-titles .team-title'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'team_index',
            [
               'label'     => esc_html__('Team Index', 'omero'),
               'type'      => Controls_Manager::HEADING,
               'separator' => 'before'
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'index_typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .team-index-item',
            ]
        );

        $this->add_control(
            'team_index_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-index-item'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'team_job',
            [
               'label'     => esc_html__('Team Position', 'omero'),
               'type'      => Controls_Manager::HEADING,
               'separator' => 'before'
            ]
        );

        $this->add_group_control(
            Omero_Group_Control_Typography::get_type(),
            [
                'name'     => 'job_typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .team-position',
            ]
        );

        $this->add_control(
            'team_job_color',
            [
                'label'     => esc_html__('Text Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-position'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'job_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-team-item-titles .team-position'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'team_socials',
            [
               'label'     => esc_html__('Icon Socials', 'omero'),
               'type'      => Controls_Manager::HEADING,
               'separator' => 'before'
            ]
        );

        $this->add_control(
            'team_icon_color',
            [
                'label'     => esc_html__('Icon Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ol.team_socials li a i'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'team_icon_color_hover',
            [
                'label'     => esc_html__('Icon Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ol.team_socials li a:hover i'   => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'team_icon_color_border',
            [
                'label'     => esc_html__('Border Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ol.team_socials li a .path-border'   => 'stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'team_icon_color_border_hover',
            [
                'label'     => esc_html__('Border Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ol.team_socials li a:hover .path-border'   => 'stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'team_icon_color_bg',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ol.team_socials li a'   => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'team_icon_color_bg_hover',
            [
                'label'     => esc_html__('Background Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ol.team_socials li a:hover'   => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();
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
        if (!empty($settings['image_thumbnail_size'])) {
            $this->image_size = $settings['image_thumbnail_size'];
        }
        if (!empty($settings['layout'])) {
            $this->layout = $settings['layout'];
        }

        $side_title = '';
        $side_image = '';
        $box_excerpt = '';
        if (!empty($settings['teams_list'])) {
            $i = 1;
            foreach ($settings['teams_list'] as $item) {
                if (empty($item['choose_team'])) {
                    continue;
                }
                $_id = $item['_id'];
                $id = absint($item['choose_team']);

                global $post;
                $post = get_post($id);
                setup_postdata($post);
                
                $side_title .= $this->get_render_side_titles($i, $_id);
                $side_image .= $this->get_render_side_images($i, $_id);

                wp_reset_postdata();
                $i++;
            }
        }

        if (empty($side_title)) {
            ?><pre><?php _e('Please choose the team!', 'omero'); ?></pre><?php
            return;
        }

        $this->add_render_attribute('team-wrapper', 'class', 'omero-team-wrapper');
        $this->add_render_attribute('team-wrapper-titles', 'class', 'omero-team-side-titles');
        ?>
        <div <?php $this->print_render_attribute_string('team-wrapper-titles'); ?>>
            <ul class="omero-team-list-titles">
                <?php printf('%s', $side_title); ?>
            </ul>
        </div>
        <div <?php $this->print_render_attribute_string('team-wrapper'); ?>>
            <div class="omero-team columns-1 elementor-team elementor-team-style-accordion">
                <div class="omero-con">
                    <ul class="omero-team omero-list-wrapper clear-list-style elementor-grid">
                        <?php printf('%s', $side_image); ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_render_side_titles($index, $_id) {
        ob_start();
        $item_class = 'omero-team-item-titles';
        if ($index === 1) {
            $item_class .= ' show';
        }
        ?>
        <li class="<?php echo esc_attr($item_class); ?>" data-id="<?php echo esc_attr($_id) ?>">
            <div class="team-item-bottom">
                <div class="team-content-left">
                    <?php omero_team_loop_index($index); ?>
                    <a class="more-link team-button" href="<?php the_permalink() ?>" title="<?php the_title() ?>">
                        <span class="team-title"><?php the_title() ?></span>
                    </a>
                    <?php
                        omero_team_position();
                    ?>
                </div>
                 <?php
                    omero_team_socials();
                 ?>
            </div>
        </li>
        <?php
        return ob_get_clean();
    }

    private function get_render_side_images($index, $_id) {
        ob_start();
        $class = 'omero-item team team-style-accordion elementor-repeater-item-'.$_id;
        if ($index === 1) {
            $class .= ' actived';
        }
        ?>
        <li class="<?php echo esc_attr($class); ?>">
            <div class="team-block omero-path-wrapper only-top-left">
                <?php
                omero_team_thumbnail($this->image_size);
                ?>
            </div>
        </li>
        <?php
        return ob_get_clean();
    }
}

$widgets_manager->register(new Omero_Elementor_Widget_Team_Accordion());

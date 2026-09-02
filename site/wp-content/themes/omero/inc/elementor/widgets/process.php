<?php
//namespace Elementor;
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Group_Control_Background;
use Elementor\Controls_Manager;
 use Elementor\Group_Control_Border;
 use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
 use Elementor\Group_Control_Text_Stroke;
 use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Omero\Elementor\Omero_Base_Widgets;

class Omero_Elementor_Process extends Omero_Base_Widgets {

    public function get_name() {
        return 'omero-process';
    }

    public function get_title() {
        return __('Omero Process', 'omero');
    }

    public function get_categories() {
        return array('omero-addons');
    }

    public function get_icon() {
        return 'eicon-editor-list-ol';
    }

    public function get_script_depends() {
        return [
            'omero-elementor-process'
        ];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_general',
            [
                'label' => __('General', 'omero'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'years',
            [
                'label'       => __('Years', 'omero'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('1990', 'omero'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label'       => __('Title', 'omero'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('Process Title', 'omero'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'content',

            [
                'label'      => __('Content', 'omero'),
                'type'       => Controls_Manager::WYSIWYG,
                'default'    => __('Process Content', 'omero'),
                'show_label' => false,
            ]
        );

        $repeater->add_control(
            'image_link_source',
            [
                'label'      => esc_html__('Choose Image', 'omero'),
                'default'    => [
                    'url' => Elementor\Utils::get_placeholder_image_src(),
                ],
                'type'       => Controls_Manager::MEDIA,
                'show_label' => false,
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label'       => __('Link', 'omero'),
                'type'        => Controls_Manager::URL,
                'dynamic'     => [
                    'active' => true,
                ],
                'placeholder' => __('https://your-link.com', 'omero'),
                'default'     => [
                    'url' => '#',
                ],
            ]
        );

        $this->add_group_control(
            Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'      => 'image',
                'default'   => 'full',
                'separator' => 'none',
            ]
        );

        $this->add_control(
            'process_list',
            [
                'label'       => __('Process Items', 'omero'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'title'   => __('Process #1', 'omero'),
                        'content' => __('If you remember the very first time you have met with the person you love or your friend, it would be nice to let the person know that you still remember that very moment.', 'omero'),
                        'link'    => '#'
                    ],
                    [
                        'title'   => __('Process #2', 'omero'),
                        'content' => __('If you remember the very first time you have met with the person you love or your friend, it would be nice to let the person know that you still remember that very moment.', 'omero'),
                        'link'    => '#'
                    ],
                    [
                        'title'   => __('Process #3', 'omero'),
                        'content' => __('If you remember the very first time you have met with the person you love or your friend, it would be nice to let the person know that you still remember that very moment.', 'omero'),
                        'link'    => '#'
                    ],
                ],
                'title_field' => '{{{ title }}}',

            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_item_style',
            [
                'label' => __('Item', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]

        );

        $this->add_control(
            'item_background_color',
            [
                'label'     => esc_html__('Background Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .process-content-wap'   => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .process-content-wap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(), [
                'name'      => 'item_border',
                'selector'  => '{{WRAPPER}} .process-content-wap',
            ]
        );

        $this->add_control(
            'process_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .process-content-wap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_img_style',
            [
                'label' => __('Image', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]

        );

        $this->add_responsive_control(
            'process_width',
            [
                'label'      => esc_html__('Width', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-process-image' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'process_height',
            [
                'label'      => esc_html__('Height', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min' => 200,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-process-image' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'process_image_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-process-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'process_image_margin',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .omero-process-image'     => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'content_style',
            [
                'label' => __('Content', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'number',
            [
                'label'     => __('Number', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'number_color',
            [
                'label'     => __('Number Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .process-years' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'years_typography',
                'selector' => '{{WRAPPER}} .process-years',
            ]
        );

        $this->add_control(
            'title',
            [
                'label'     => __('Title', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => __('Title Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .process-title a' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'title_color_hover',
            [
                'label'     => __('Title Color Hover', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .process-inner-content-wap .process-title a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} h5.process-title a',
            ]
        );

        $this->add_responsive_control(
            'title_margin_item',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .process-title'  => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'content_heading',
            [
                'label'     => __('Content', 'omero'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label'     => __('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'content_typography',
                'selector' => '{{WRAPPER}} .content, {{WRAPPER}} .elementor-process-layout-2 .elementor-process-item  .omero-inner-process.activate .process-inner-content-wap .content',
            ]
        );

        $this->add_responsive_control(
            'content_margin_item',
            [
                'label'      => esc_html__('Margin', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .content'  => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->get_controls_column();
        // Carousel options
        $this->get_control_carousel();

    }

    protected function render() {

        $settings = $this->get_settings_for_display();

        $count_process = 0;
        if (is_array($settings['process_list'])) {
            $count_process = count($settings['process_list']);
        }

        $layout = (!empty($settings['process_style'])) ? $settings['process_style'] : '1';


        if ($count_process > 0) {
            $this->add_render_attribute('wrapper', 'class', 'elementor-omero-process-wrapper');
            $this->add_render_attribute('item', 'class', 'elementor-process-item');
            $this->get_data_elementor_columns();

            $image_list = [];
            $migration_allowed = Icons_Manager::is_migration_allowed();
            ?>
            <div <?php $this->print_render_attribute_string('wrapper'); ?>>
                <div <?php $this->print_render_attribute_string('container'); ?>>
                    <div <?php $this->print_render_attribute_string('inner'); ?>>
                        <?php foreach ($settings['process_list'] as $index => $item) : 
                            $link_key = 'link_' . $index;
                            if (!empty($item['link']['url'])) {
                                $this->add_render_attribute($link_key, 'href', $item['link']['url']);
                                if ($item['link']['is_external']) {
                                    $this->add_render_attribute($link_key, 'target', '_blank');
                                }
    
                                if ($item['link']['nofollow']) {
                                    $this->add_render_attribute($link_key, 'rel', 'nofollow');
                                }
                            }

                            $pad_index = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                            $str_index = sprintf(__('%s step', 'omero'), $pad_index);

                            $image_url = Group_Control_Image_Size::get_attachment_image_src($item['image_link_source']['id'], 'image', $settings);
                            if (!$image_url && isset($attachment['url'])) {
                                $image_url = Elementor\Utils::get_placeholder_image_src();
                            }
                            $image_list[] = $image_url;

                            $item_key = 'process_'.$index;
                            
                            $this->add_render_attribute($item_key, 'class', 'omero-inner-process');
                            $this->add_render_attribute($item_key, 'data-index', $index);
                            if ($index == 0) {
                                $this->add_render_attribute($item_key, 'class', 'activate');
                            }

                            $migrated = isset($item['__fa4_migrated']['selected_item_icon']);
                            // add old default
                            if (!isset($item['item_icon']) && !$migration_allowed) {
                                $item['item_icon'] = 'fa fa-check-circle';
                            }
                            $is_new = !isset($item['item_icon']) && $migration_allowed;

                            ?>
                            <div <?php $this->print_render_attribute_string('item'); ?>>
                                <div <?php $this->print_render_attribute_string($item_key); ?>>
                                    <div class="process-content-wap">
                                        <div class="omero-process-image">
                                            <img class="image img-omero-process" src="<?php echo esc_url($image_url); ?>" alt="image">
                                        </div>
                                        <div class="process-inner-content-wap">
                                            <?php if (!empty($item['years'])) : ?>
                                                <div class="process-years"><?php echo esc_html($item['years']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['title'])) : ?>
                                                <h5 class="process-title">
                                                    <a <?php $this->print_render_attribute_string($link_key); ?>>
                                                        <?php echo esc_html($item['title']); ?>
                                                    </a>
                                                </h5>
                                            <?php endif; ?>
                                            <?php if (!empty($item['content'])) : ?>
                                                <div class="content">
                                                    <?php printf('%s', $this->parse_text_editor($item['content'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $this->get_swiper_navigation($count_process); ?>
                
            </div>
            <?php
        }
    }
}

$widgets_manager->register(new Omero_Elementor_Process());
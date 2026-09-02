<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!omero_is_contactform_activated()) {
    return;
}

use Elementor\Controls_Manager;


class Omero_Elementor_ContactForm extends Elementor\Widget_Base {

    public function get_name() {
        return 'omero-contactform';
    }

    public function get_title() {
        return esc_html__('Omero Contact Form', 'omero');
    }

    public function get_categories() {
        return array('omero-addons');
    }

    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'contactform7',
            [
                'label' => esc_html__('General', 'omero'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );
        $cf7               = get_posts('post_type="wpcf7_contact_form"&numberposts=-1');
        $contact_forms[''] = esc_html__('Please select form', 'omero');
        if ($cf7) {
            foreach ($cf7 as $cform) {
                $hash = get_post_meta( $cform->ID, '_hash', true );
                if ($hash) {
                    $contact_forms[$hash] = $cform->post_title;
                }
            }
        } else {
            $contact_forms[0] = esc_html__('No contact forms found', 'omero');
        }

        $this->add_control(
            'cf_id',
            [
                'label'   => esc_html__('Select contact form', 'omero'),
                'type'    => Controls_Manager::SELECT,
                'options' => $contact_forms,
                'default' => ''
            ]
        );

        $this->add_control(
            'form_name',
            [
                'label'   => esc_html__('Form name', 'omero'),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__('Contact form', 'omero'),
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label'        => esc_html__('Alignment', 'omero'),
                'type'         => Controls_Manager::CHOOSE,
                'options'      => [
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
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form'  => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'contactform7_style',
            [
                'label' => esc_html__('Form', 'omero'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_background_color',
            [
                'label'     => esc_html__('Background color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} textarea,
                     {{WRAPPER}} select:not([size]):not([multiple]),
                     {{WRAPPER}} input:not(input[type=radio], input[type=checkbox])' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'form_border_color',
            [
                'label'     => esc_html__('Border color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} textarea,
                    {{WRAPPER}} select:not([size]):not([multiple]),
                    {{WRAPPER}} input:not(input[type=radio], input[type=checkbox])' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'form_radius',
            [
                'label'      => esc_html__('Border Radius', 'omero'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}}.elementor-widget-omero-contactform.effect-form-yes .elementor-widget-container' => 'border-radius: {{SIZE}}{{UNIT}}; --path-radius: {{SIZE}}',
                ],
            ]
        );


        $this->add_control(
            'form_label',
            [
                'label' => esc_html__('Label', 'omero'),
                'type'  => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form .wpcf7-checkbox label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wpcf7-form label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'form_exerpt',
            [
                'label' => esc_html__('Exerpt', 'omero'),
                'type'  => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'exerpt_color',
            [
                'label'     => esc_html__('Color', 'omero'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpcf7-form .form-2 .form-exerpt p' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'exerpt_padding',
            [
                'label'      => esc_html__('Padding', 'omero'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .wpcf7-form .form-2 .form-exerpt p'     => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'effect_form',
            [
                'label' => esc_html__('Effter', 'omero'),
                'type'         => Controls_Manager::SWITCHER,
                'prefix_class' => 'effect-form-',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        if (!$settings['cf_id'] || empty($settings['cf_id'])) {
            return;
        }


        $form = wpcf7_get_contact_form_by_hash($settings['cf_id']);

        if (!$form) return;
        $id = $form->id();
        
        $args['id']    = $id;
        $args['title'] = $settings['form_name'];

        echo omero_do_shortcode('contact-form-7', $args);
    }
}

$widgets_manager->register(new Omero_Elementor_ContactForm());

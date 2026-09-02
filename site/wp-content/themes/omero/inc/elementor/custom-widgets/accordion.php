<?php
//Accordion
use Elementor\Modules\NestedAccordion\Widgets\Nested_Accordion;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;

class Omero_Widget_Nested_Accordion extends Nested_Accordion
{
    public function has_widget_inner_wrapper(): bool {
        return true;
    }
}
$widgets_manager->register(new Omero_Widget_Nested_Accordion());

add_action('elementor/element/nested-accordion/section_accordion_style/before_section_end', function ($element, $args) {

    $element->update_control(
        'accordion_border_radius',
        [
            'label' => esc_html__( 'Border Radius', 'omero' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
            'selectors' => [
                '{{WRAPPER}} .e-n-accordion-item ' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $element->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
            'name' => 'box_shadow',
            'separator' => 'before',
            'selector' => '{{WRAPPER}} .e-n-accordion-item',
        ]
    );

    $states = ['normal', 'hover', 'active'];
    foreach ($states as $state) {
        $selector = '{{WRAPPER}} > .elementor-widget-container > .e-n-accordion > .e-n-accordion-item';
        switch ( $state ) {
            case 'hover':
                $selector .= ':hover';
                break;
            case 'active':
                $selector = '{{WRAPPER}} > .elementor-widget-container > .e-n-accordion > .e-n-accordion-item[open]';
                break;
        }
        $element->update_control(
            'accordion_border_'.$state.'_border',
            [
                'selectors' => [
                    $selector => 'border-style: {{VALUE}};',
                ],
            ]
        );
        $element->update_responsive_control(
            'accordion_border_'.$state.'_width',
            [
                'selectors' => [
                    $selector => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $element->update_control(
            'accordion_border_'.$state.'_color',
            [
                'selectors' => [
                    $selector => 'border-color: {{VALUE}};',
                ],
            ]
        );
    }

    $element->add_responsive_control(
        'acc_item_margin',
        [
            'label' => esc_html__( 'Margin', 'omero' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
            'selectors' => [
                '{{WRAPPER}} > .elementor-widget-container > .e-n-accordion > .e-n-accordion-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ],
        [
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'accordion_padding',
            ]
        ]
    );
}, 10, 2);



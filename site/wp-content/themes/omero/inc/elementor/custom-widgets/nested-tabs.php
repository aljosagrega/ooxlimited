<?php

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Modules\NestedElements\Base\Widget_Nested_Base;
use Elementor\Modules\NestedElements\Controls\Control_Nested_Repeater;
use Elementor\Plugin;
use Elementor\Repeater;
use Elementor\Modules\NestedTabs\Widgets\NestedTabs;

class Omero_Elementor_NestedTabs extends NestedTabs {
    protected function maybe_render_tab_icons_html( $item_settings ): void {
		$item_data = $item_settings['item'];
		$icon_type = $item_data['icon_type'] ?? 'icon';

		if ($icon_type === 'image') {
			if (empty($item_data['tab_image']) || empty($item_data['tab_image']['id'])) {
				return;
			}
            $item_data['tab_image_size'] = 'thumbnail';
			if (!empty($item_data['tab_image']['size'])) {
				$item_data['tab_image_size'] = $item_data['tab_image']['size'];
			}
			?>
			<span <?php $this->print_render_attribute_string( 'tab-icon' ); ?>>
				<span class="tab-icon-inner icon-image">
					<?php \Elementor\Group_Control_Image_Size::print_attachment_image_html($item_data, 'tab_image'); ?>
				</span>
			</span>
			<?php

		} elseif ($icon_type === 'number') {
			if ( empty( $item_settings['tab_count'] ) ) {
				return;
			}
			?>
			<span <?php $this->print_render_attribute_string( 'tab-icon' ); ?>>
				<span class="tab-icon-inner icon-number">
					<?php echo esc_html(str_pad($item_settings['tab_count'], 2, '0', STR_PAD_LEFT)); ?>
				</span>
			</span>
			<?php
		} else {
			$icon_settings = $item_settings['item']['tab_icon'];
			if ( empty( $icon_settings['value'] ) ) {
				return;
			}
			$active_icon_settings = $this->is_active_icon_exist( $item_settings['item'] )
				? $item_settings['item']['tab_icon_active']
				: $icon_settings;
			?>
			<span <?php $this->print_render_attribute_string( 'tab-icon' ); ?>>
				<span class="tab-icon-inner">
					<?php Icons_Manager::render_icon( $icon_settings, [ 'aria-hidden' => 'true' ] ); ?>
					<?php Icons_Manager::render_icon( $active_icon_settings, [ 'aria-hidden' => 'true' ] ); ?>
				</span>
			</span>
			<?php
		}
	}

	protected function content_template_single_repeater_item() {
		?>
		<#
		const tabIndex = view.collection.length,
			elementUid = view.getIDInt().toString(),
			item = data,
			hoverAnimationSetting = view?.container?.settings?.attributes?.hover_animation;
			hoverAnimationClass = hoverAnimationSetting
				? `elementor-animation-${ hoverAnimationSetting }`
				: '';
		#>
		<?php $this->content_template_single_item( '{{ tabIndex }}', '{{ item }}', '{{ elementUid }}', '{{ hoverAnimationClass }}' );
	}

	protected function content_template() {
		?>
		<# const elementUid = view.getIDInt().toString(); #>
		<div class="e-n-tabs" data-widget-number="{{ elementUid }}" aria-label="<?php echo esc_attr(esc_html__( 'Tabs. Open items with Enter or Space, close with Escape and navigate using the Arrow keys.', 'omero' )); ?>">
			<# if ( settings['tabs'] ) { #>
			<div class="e-n-tabs-heading" role="tablist">
				<# _.each( settings['tabs'], function( item, index ) {
					const tabIndex = index,
						hoverAnimationSetting = settings['hover_animation'],
						hoverAnimationClass = hoverAnimationSetting
							? `elementor-animation-${ hoverAnimationSetting }`
							: '';
				#>
				<?php $this->content_template_single_item( '{{ tabIndex }}', '{{ item }}', '{{ elementUid }}', '{{ hoverAnimationClass }}' ); ?>
				<# } ); #>
			</div>
			<div class="e-n-tabs-content"></div>
			<# } #>
		</div>
		<?php
	}

	private function content_template_single_item( $tab_index, $item, $element_uid, $hover_animation_class ) {
		?>
		<#
		const tabCount = tabIndex + 1,
			tabTitleId = 'e-n-tab-title-' + elementUid + tabCount,
			tabId = item.element_id
				? item.element_id
				: tabTitleId,
			tabUid = elementUid + tabCount,
			tabIcon = elementor.helpers.renderIcon( view, item.tab_icon, { 'aria-hidden': true }, 'i' , 'object' ),
			activeTabIcon = item.tab_icon_active.value
				? elementor.helpers.renderIcon( view, item.tab_icon_active, { 'aria-hidden': true }, 'i' , 'object' )
				: tabIcon,
			escapedHoverAnimationClass = _.escape( hoverAnimationClass ),
			iconType = item.icon_type || 'icon';

		view.addRenderAttribute( 'tab-title', {
			'id': tabId,
			'data-tab-title-id': tabTitleId,
			'class': [ 'e-n-tab-title',escapedHoverAnimationClass ],
			'data-tab-index': tabCount,
			'role': 'tab',
			'aria-selected': 1 === tabCount ? 'true' : 'false',
			'tabindex': 1 === tabCount ? '0' : '-1',
			'aria-controls': 'e-n-tab-content-' + tabUid,
			'style': '--n-tabs-title-order: ' + tabCount + ';',
		}, null, true );

		view.addRenderAttribute( 'tab-title-text', {
			'class': [ 'e-n-tab-title-text' ],
			'data-binding-type': 'repeater-item',
			'data-binding-repeater-name': 'tabs',
			'data-binding-setting': [ 'tab_title', 'element_id' ],
			'data-binding-index': tabCount,
			'data-binding-config': JSON.stringify({
				'element_id': {
					attr: 'id',
					selector: 'button',
					editType: 'attribute',
				},
				'tab_title': {
					editType: 'text',
				},
			}),
		}, null, true );

		view.addRenderAttribute( 'tab-icon', {
			'class': [ 'e-n-tab-icon' ],
			'data-binding-type': 'repeater-item',
			'data-binding-repeater-name': 'tabs',
			'data-binding-index': tabCount,
		}, null, true );
		#>

		<button {{{ view.getRenderAttributeString( 'tab-title' ) }}}>
			<# if ( iconType == 'image' ) { #>
				<# if ( !! item.tab_image.url ) { 
					var image = {
						id: item.tab_image.id,
						url: item.tab_image.url,
						size: item.tab_image.size || 'thumbnail',
						model: view.getEditModel()
					};

					var image_url = elementor.imagesManager.getImageUrl( image );
					var imageHtml = '<img src="' + _.escape( image_url ) + '" class="attachment-' + image.size + ' size-' + image.size + '" />';
					#>
					<span {{{ view.getRenderAttributeString( 'tab-icon' ) }}}><span class="tab-icon-inner icon-image">{{{ imageHtml }}}</span></span>
				<# } #>
			<# } else if ( iconType == 'number' ) { #>
				<span {{{ view.getRenderAttributeString( 'tab-icon' ) }}}><span class="tab-icon-inner icon-number">{{{ tabCount.toString().padStart(2, '0') }}}</span></span>
			<# } else { #>
				<# if ( !! item.tab_icon.value ) { #>
					<span {{{ view.getRenderAttributeString( 'tab-icon' ) }}}><span class="tab-icon-inner">{{{ tabIcon.value }}}{{{ activeTabIcon.value }}}</span></span>
				<# } #>
			<# } #>

			<span {{{ view.getRenderAttributeString( 'tab-title-text' ) }}}>{{{ item.tab_title }}}</span>
		</button>
		<?php
	}

    /**
	 * @param $item
	 * @return bool
	 */
	private function is_active_icon_exist( $item ) {
		return array_key_exists( 'tab_icon_active', $item ) && ! empty( $item['tab_icon_active'] ) && ! empty( $item['tab_icon_active']['value'] );
	}

	public function is_reload_preview_required() {
        return true;
    }
}
$widgets_manager->register(new Omero_Elementor_NestedTabs());

add_action('elementor/element/nested-tabs/section_tabs_style/before_section_end', function ($element, $args) {

	$element->update_control(
        'tabs_title_background_color_hover_color',
        [
            'global' => [
				'default' => Global_Colors::COLOR_SECONDARY,
			],
        ]
    );
	$element->update_control(
        'tabs_title_background_color_active_color',
        [
            'global' => [
				'default' => Global_Colors::COLOR_SECONDARY,
			],
        ]
    );

	$element->add_responsive_control(
		'tabs_opacity_normal',
		[
			'label' => esc_html__( 'Opacity', 'omero' ),
			'type' => Controls_Manager::NUMBER,
			'label_block' => false,
			'range' => [
				'px' => [
					'max' => 1,
					'min' => 0.10,
					'step' => 0.01,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .e-n-tab-title' => 'opacity: {{SIZE}};',
			],
		],
		[
            'position' => [
                'type' => 'control',
                'at' => 'before',
                'of' => 'tabs_title_background_color_background',
            ]
        ]
	);

	$element->add_responsive_control(
		'tabs_opacity_hover',
		[
			'label' => esc_html__( 'Opacity', 'omero' ),
			'type' => Controls_Manager::NUMBER,
			'label_block' => false,
			'range' => [
				'px' => [
					'max' => 1,
					'min' => 0.10,
					'step' => 0.01,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .e-n-tab-title:hover' => 'opacity: {{SIZE}};',
			],
		],
		[
            'position' => [
                'type' => 'control',
                'at' => 'before',
                'of' => 'tabs_title_background_color_hover_background',
            ]
        ]
	);

	$element->add_responsive_control(
		'tabs_opacity_active',
		[
			'label' => esc_html__( 'Opacity', 'omero' ),
			'type' => Controls_Manager::NUMBER,
			'label_block' => false,
			'range' => [
				'px' => [
					'max' => 1,
					'min' => 0.10,
					'step' => 0.01,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .e-n-tab-title[aria-selected="true"]' => 'opacity: {{SIZE}};',
			],
		],
		[
            'position' => [
                'type' => 'control',
                'at' => 'before',
                'of' => 'tabs_title_background_color_active_background',
            ]
        ]
	);

}, 10, 2);

add_action('elementor/element/nested-tabs/icon_section_style/before_section_end', function ($element, $args) {

	$element->update_responsive_control( 'icon_size', [
		'selectors' => [
			'{{WRAPPER}}' => '--n-tabs-icon-size: {{SIZE}}{{UNIT}}',
			'{{WRAPPER}} .tab-icon-inner.icon-image' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			'{{WRAPPER}} .tab-icon-inner.icon-number' => 'font-size: {{SIZE}}{{UNIT}};',
		],
	] );

	$element->add_responsive_control(
        'icon_border_radius',
        [
            'label' => esc_html__( 'Border Radius', 'omero' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'selectors' => [
                '{{WRAPPER}} .tab-icon-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ],
		[
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'icon_size',
            ]
        ]
    );

	$element->add_control( 'bg_icon_color', 
		[
			'label' => esc_html__( 'Background Color', 'omero' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .tab-icon-inner' => 'background-color: {{VALUE}};',
			],
		],
		[
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'icon_color',
            ]
        ]
	);

	$element->add_control( 'bg_icon_color_hover', 
		[
			'label' => esc_html__( 'Background Color', 'omero' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .e-n-tab-title:hover .tab-icon-inner' => 'background-color: {{VALUE}};',
			],
		],
		[
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'icon_color_hover',
            ]
        ]
	);
	
	$element->add_control( 'bg_icon_color_actived', 
		[
			'label' => esc_html__( 'Background Color', 'omero' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .e-n-tab-title[aria-selected="true"] .tab-icon-inner' => 'background-color: {{VALUE}};',
			],
		],
		[
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'icon_color_active',
            ]
        ]
	);
	
}, 10, 2);

add_action('elementor/element/nested-tabs/section_tabs/before_section_end', function ($element, $args) {
    
	$repeater = new Repeater();

	$repeater->add_control( 'tab_title', [
		'label' => esc_html__( 'Title', 'omero' ),
		'type' => Controls_Manager::TEXT,
		'default' => esc_html__( 'Tab Title', 'omero' ),
		'placeholder' => esc_html__( 'Tab Title', 'omero' ),
		'label_block' => true,
		'dynamic' => [
			'active' => true,
		],
	] );

	$repeater->add_control(
		'icon_type',
		[
			'label'       => esc_html__('Icon Type', 'omero'),
			'type'        => Controls_Manager::SELECT,
			'label_block' => false,
			'options'     => [
				'icon'  => esc_html__('Icon', 'omero'),
				'image' => esc_html__('Image', 'omero'),
				'number' => esc_html__('Number', 'omero'),
			],
			'default'     => 'icon',
			// 'frontend_available' => true,
			'render_type' => 'template',
		]
	);

	$repeater->add_control(
		'tab_icon',
		[
			'label' => esc_html__( 'Icon', 'omero' ),
			'type' => Controls_Manager::ICONS,
			'fa4compatibility' => 'icon',
			'skin' => 'inline',
			'label_block' => false,
			'condition'   => [
				'icon_type' => 'icon',
			],
		]
	);

	$repeater->add_control(
		'tab_icon_active',
		[
			'label' => esc_html__( 'Active Icon', 'omero' ),
			'type' => Controls_Manager::ICONS,
			'fa4compatibility' => 'icon',
			'skin' => 'inline',
			'label_block' => false,
			'condition' => [
				'tab_icon[value]!' => '',
				'icon_type' => 'icon',
			],
		]
	);

	$repeater->add_control(
		'tab_image',
		[
			'label'      => esc_html__('Image', 'omero'),
			'type'       => Controls_Manager::MEDIA,
			'has_sizes' => true,
			'ai' => [
				'active' => false,
			],
			'condition'   => [
				'icon_type' => 'image',
			],
			// 'show_label' => false,
		]
	);

	$repeater->add_control(
		'element_id',
		[
			'label' => esc_html__( 'CSS ID', 'omero' ),
			'type' => Controls_Manager::TEXT,
			'default' => '',
			'ai' => [
				'active' => false,
			],
			'dynamic' => [
				'active' => true,
			],
			'title' => esc_html__( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'omero' ),
			'style_transfer' => false,
			'classes' => 'elementor-control-direction-ltr',
		]
	);

	$element->update_control( 'tabs', [
		'label' => esc_html__( 'Tabs Items', 'omero' ),
		'type' => Control_Nested_Repeater::CONTROL_TYPE,
		'fields' => $repeater->get_controls(),
		'default' => [
			[
				'tab_title' => esc_html__( 'Tab #1', 'omero' ),
			],
			[
				'tab_title' => esc_html__( 'Tab #2', 'omero' ),
			],
			[
				'tab_title' => esc_html__( 'Tab #3', 'omero' ),
			],
		],
		'title_field' => '{{{ tab_title }}}',
		'button_text' => esc_html__( 'Add Tab', 'omero' ),
	] );

}, 10, 2);
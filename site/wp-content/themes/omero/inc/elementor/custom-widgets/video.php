<?php
namespace Elementor;

add_action('elementor/element/video/section_image_overlay/before_section_end', function ($element, $args) {

    $element->update_control(
        'section_image_overlay',
        [
            'label' => __('Video Actions', 'omero'),
        ]
    );

    $element->update_control(
        'show_play_icon',
        [
            'condition' => [],
            'default' => '',
        ]
    );

    $element->update_control(
        'play_icon',
        [
            'condition' => [
                'show_play_icon!' => '',
            ],
        ]
    );

    $element->update_control(
        'lightbox',
        [
            'condition' => [],
            'conditions' => [
                'relation' => 'or',
                'terms' => [
                    [
                        'name' => 'show_image_overlay',
                        'operator' => '===',
                        'value' => 'yes',
                    ],
                    [
                        'name' => 'show_play_icon',
                        'operator' => '===',
                        'value' => 'yes',
                    ],
                ],
            ],
            'render_type' => 'template',
            'prefix_class' => 'elementor-video-lightbox-'
        ]
    );

    

}, 20, 2);

add_action('elementor/element/video/section_image_overlay_style/before_section_end', function ($element, $args) {

    $element->update_control(
        'section_image_overlay_style',
        [
            'label' => __('Video Actions Style', 'omero'),
            'condition' => [],
        ]
    );

    $element->update_control(
        'play_icon_title',
        [
            'condition' => [],
        ]
    );

    $element->update_control(
        'play_icon_color',
        [
            'condition' => [],
        ]
    );

    $element->update_control(
        'play_icon_size',
        [
            'condition' => [],
        ]
    );

    $element->add_responsive_control(
        'icon_padding',
        [
            'label' => esc_html__( 'Icon Padding', 'omero' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'selectors' => [
                '{{WRAPPER}}  .elementor-custom-embed-play' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ],
        [
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'play_icon_size',
            ]
        ]
    );

    $element->add_responsive_control(
        'icon_radius',
        [
            'label' => esc_html__( 'Icon Radius', 'omero' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'selectors' => [
                '{{WRAPPER}}  .elementor-custom-embed-play' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ],
        [
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'icon_padding',
            ]
        ]
    );

    $element->add_control(
        'play_icon_color_hover',
        [
            'label' => esc_html__( 'Color Hover', 'omero' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elementor-custom-embed-play:hover i' => 'color: {{VALUE}}',
                '{{WRAPPER}} .elementor-custom-embed-play:hover svg' => 'fill: {{VALUE}}',
            ],
        ],
        [
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'play_icon_color',
            ]
        ]
    );

    $element->add_control(
        'play_icon_bg_color',
        [
            'label' => esc_html__( 'Background Color', 'omero' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elementor-custom-embed-play' => 'background-color: {{VALUE}}',
            ],
        ],
        [
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'play_icon_color_hover',
            ]
        ]
    );

    $element->add_control(
        'play_icon_bg_hover_color',
        [
            'label' => esc_html__( 'Background Color Hover', 'omero' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .elementor-custom-embed-play:hover' => 'background-color: {{VALUE}}',
            ],
        ],
        [
            'position' => [
                'type' => 'control',
                'at' => 'after',
                'of' => 'play_icon_bg_color',
            ]
        ]
    );


}, 20, 2);

add_action('elementor/element/video/section_lightbox_style/before_section_end', function ($element, $args) {

    $element->update_control(
        'section_lightbox_style',
        [
            'condition' => [
                'lightbox' => 'yes'
            ],
        ]
    );


}, 20, 2);

add_action('elementor/element/video/section_video_style/before_section_end', function ($element, $args) {

    $element->update_control(
        'aspect_ratio',
        [
            'options' => [
                '169' => '16:9',
                '219' => '21:9',
                '43' => '4:3',
                '32' => '3:2',
                '11' => '1:1',
                'initial' => 'Initial',
            ],
            'prefix_class' => 'elementor-video-aspect-ratio-'
        ],
    );

}, 10, 2);


class Omero_Widget_Video extends Widget_Video
{
    protected function has_image_overlay()
    {
        $settings = $this->get_settings_for_display();

        return 'yes' === $settings['show_play_icon'] || 'yes' === $settings['show_image_overlay'];
    }

    protected function render() {
		$settings = $this->get_settings_for_display();

		$video_url = $settings[ $settings['video_type'] . '_url' ];

		if ( 'hosted' === $settings['video_type'] ) {
			$video_url = $this->get_hosted_video_url();
		} else {
			if ( 'videopress' === $settings['video_type'] ) {
				$video_url = $this->get_videopress_video_url();
			}

			$embed_params = $this->get_embed_params();
			$embed_options = $this->get_embed_options();
		}

		if ( empty( $video_url ) ) {
			return;
		}

		if ( 'youtube' === $settings['video_type'] ) {
			$video_html = '<div class="elementor-video"></div>';
		}

		if ( 'hosted' === $settings['video_type'] ) {
			$this->add_render_attribute( 'video-wrapper', 'class', 'e-hosted-video' );

			ob_start();

			$this->render_hosted_video();

			$video_html = ob_get_clean();
		} else {
			$is_static_render_mode = Plugin::$instance->frontend->is_static_render_mode();
			$post_id = get_queried_object_id();

			if ( $is_static_render_mode ) {
				$video_html = Embed::get_embed_thumbnail_html( $video_url, $post_id );
				// YouTube API requires a different markup which was set above.
			} elseif ( 'youtube' !== $settings['video_type'] ) {
				$video_html = Embed::get_embed_html( $video_url, $embed_params, $embed_options );
			}
		}

		if ( empty( $video_html ) ) {
			echo esc_url( $video_url );

			return;
		}

		$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-wrapper' );

		$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-open-' . ( $settings['lightbox'] ? 'lightbox' : 'inline' ) );
		?>
		<div <?php $this->print_render_attribute_string( 'video-wrapper' ); ?>>
			<?php
			if ( ! $settings['lightbox'] ) {
				Utils::print_unescaped_internal_string( $video_html ); // XSS ok.
			}

			if ( $this->has_image_overlay() ) {
				$this->add_render_attribute( 'image-overlay', 'class', 'elementor-custom-embed-image-overlay' );

				if ( $settings['lightbox'] ) {
					if ( 'hosted' === $settings['video_type'] ) {
						$lightbox_url = $video_url;
					} else {
						$lightbox_url = Embed::get_embed_url( $video_url, $embed_params, $embed_options );
					}

					$lightbox_options = [
						'type' => 'video',
						'videoType' => $settings['video_type'],
						'url' => $lightbox_url,
						'autoplay' => $settings['autoplay'],
						'modalOptions' => [
							'id' => 'elementor-lightbox-' . $this->get_id(),
							'entranceAnimation' => $settings['lightbox_content_animation'],
							'entranceAnimation_tablet' => $settings['lightbox_content_animation_tablet'],
							'entranceAnimation_mobile' => $settings['lightbox_content_animation_mobile'],
							'videoAspectRatio' => $settings['aspect_ratio'] ?? '169',
						],
					];

					if ( 'hosted' === $settings['video_type'] ) {
						$lightbox_options['videoParams'] = $this->get_hosted_params();
					}

					$this->add_render_attribute( 'image-overlay', [
						'data-elementor-open-lightbox' => 'yes',
						'data-elementor-lightbox' => wp_json_encode( $lightbox_options ),
						'data-e-action-hash' => Plugin::instance()->frontend->create_action_hash( 'lightbox', $lightbox_options ),
					] );

					if ( Plugin::$instance->editor->is_edit_mode() ) {
						$this->add_render_attribute( 'image-overlay', [
							'class' => 'elementor-clickable',
						] );
					}
				} else {
					// When there is an image URL but no ID, it means the overlay image is the placeholder. In this case, get the placeholder URL.
					if ( !empty( $settings['image_overlay']['id'] ) ) {
						$image_url = Group_Control_Image_Size::get_attachment_image_src( $settings['image_overlay']['id'], 'image_overlay', $settings );
					} elseif ( !empty( $settings['image_overlay']['url'] ) ) {
						$image_url = esc_url($settings['image_overlay']['url']);
                    }
                    if (isset($image_url)) {
                        $this->add_render_attribute( 'image-overlay', 'style', 'background-image: url(' . $image_url . ');' );
                    }
				}

                // \var_dump($settings['image_overlay'] );
				?>
				<div <?php $this->print_render_attribute_string( 'image-overlay' ); ?>>
					<?php if ( $settings['lightbox'] && !empty( $settings['image_overlay']['id'] ) ) : ?>
						<?php Group_Control_Image_Size::print_attachment_image_html( $settings, 'image_overlay' ); ?>
					<?php endif; ?>
					<?php if ( 'yes' === $settings['show_play_icon'] ) : ?>
						<div class="elementor-custom-embed-play" role="button" aria-label="<?php $this->print_a11y_text( $settings['image_overlay'] ); ?>" tabindex="0">
							<?php
							if ( empty( $settings['play_icon']['value'] ) ) {
								$settings['play_icon'] = [
									'library' => 'eicons',
									'value' => 'eicon-play',
								];
							}
							Icons_Manager::render_icon( $settings['play_icon'], [ 'aria-hidden' => 'true' ] );
							?>
						</div>
					<?php endif; ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}

    /**
	 * @since 2.1.0
	 * @access private
	 */
	private function get_embed_options() {
		$settings = $this->get_settings_for_display();

		$embed_options = [];

		if ( 'youtube' === $settings['video_type'] ) {
			$embed_options['privacy'] = $settings['yt_privacy'];
		} elseif ( 'vimeo' === $settings['video_type'] ) {
			$embed_options['start'] = $settings['start'];
		}

		$embed_options['lazy_load'] = ! empty( $settings['lazy_load'] );

		return $embed_options;
	}

	/**
	 * @since 2.1.0
	 * @access private
	 */
	private function get_hosted_params() {
		$settings = $this->get_settings_for_display();

		$video_params = [];

		foreach ( [ 'autoplay', 'loop', 'controls' ] as $option_name ) {
			if ( $settings[ $option_name ] ) {
				$video_params[ $option_name ] = '';
			}
		}

		if ( $settings['preload'] ) {
			$video_params['preload'] = $settings['preload'];
		}

		if ( $settings['mute'] ) {
			$video_params['muted'] = 'muted';
		}

		if ( $settings['play_on_mobile'] ) {
			$video_params['playsinline'] = '';
		}

		if ( ! $settings['download_button'] ) {
			$video_params['controlsList'] = 'nodownload';
		}

		if ( $settings['poster']['url'] ) {
			$video_params['poster'] = $settings['poster']['url'];
		}

		return $video_params;
	}

	/**
	 *
	 * @return string
	 * @since 2.1.0
	 * @access private
	 */
	private function get_hosted_video_url() {
		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['insert_url'] ) ) {
			$video_url = $settings['external_url']['url'];
		} else {
			$video_url = $settings['hosted_url']['url'];
		}

		if ( empty( $video_url ) ) {
			return '';
		}

		if ( $settings['start'] || $settings['end'] ) {
			$video_url .= '#t=';
		}

		if ( $settings['start'] ) {
			$video_url .= $settings['start'];
		}

		if ( $settings['end'] ) {
			$video_url .= ',' . $settings['end'];
		}

		return $video_url;
	}

	/**
	 * Get the VideoPress video URL from the current selected settings.
	 *
	 * @return string
	 */
	private function get_videopress_video_url() {
		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['insert_url'] ) ) {
			return $settings['videopress_url'];
		}

		return $settings['hosted_url']['url'];
	}

	/**
	 * Get the params dictionary for VideoPress videos.
	 *
	 * @return array
	 */
	private function get_params_dictionary_for_videopress() {
		return [
			'controls',
			'autoplay' => 'autoPlay',
			'mute' => 'muted',
			'loop',
			'play_on_mobile' => 'playsinline',
		];
	}

	/**
	 *
	 * @since 2.1.0
	 * @access private
	 */
	private function render_hosted_video() {
		$video_url = $this->get_hosted_video_url();
		if ( empty( $video_url ) ) {
			return;
		}

		$video_params = $this->get_hosted_params();
		/* Sometimes the video url is base64, therefore we use `esc_attr` in `src`. */
		?>
		<video class="elementor-video" src="<?php echo esc_attr( $video_url ); ?>" <?php Utils::print_html_attributes( $video_params ); ?>></video>
		<?php
	}
}

$widgets_manager->register(new Omero_Widget_Video());

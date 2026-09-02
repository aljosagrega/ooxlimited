<?php

/**
 * Sets up the omero_object_loop global from the passed args or from the main query.
 *
 * @since 3.3.0
 * @param array $args Args to pass into the global.
 */
function omero_setup_object_loop( $args = array() ) {
	$default_args = array(
		'loop'         => 0,
		'columns'      => 4,
		'name'         => '',
		'is_shortcode' => false,
		'is_paginated' => true,
		'is_search'    => false,
		'is_filtered'  => false,
		'total'        => 0,
		'total_pages'  => 0,
		'per_page'     => 0,
		'current_page' => 1,
	);

	$default_args = array_merge(
		$default_args,
		array(
			'is_search'    => $GLOBALS['wp_query']->is_search(),
			'total'        => $GLOBALS['wp_query']->found_posts,
			'total_pages'  => $GLOBALS['wp_query']->max_num_pages,
			'per_page'     => $GLOBALS['wp_query']->get( 'posts_per_page' ),
			'current_page' => max( 1, $GLOBALS['wp_query']->get( 'paged', 1 ) ),
		)
	);

	// Merge any existing values.
	if ( isset( $GLOBALS['omero_object_loop'] ) ) {
		$default_args = array_merge( $default_args, $GLOBALS['omero_object_loop'] );
	}

	$GLOBALS['omero_object_loop'] = wp_parse_args( $args, $default_args );

    // echo '<pre>'; print_r($GLOBALS['omero_object_loop']); echo '</pre>';
}

/**
 * Resets the omero_object_loop global.
 *
 * @since 3.3.0
 */
function omero_reset_object_loop() {
	unset( $GLOBALS['omero_object_loop'] );
}

/**
 * Gets a property from the omero_object_loop global.
 *
 * @since 3.3.0
 * @param string $prop Prop to get.
 * @param string $default Default if the prop does not exist.
 * @return mixed
 */
function omero_get_object_loop_prop( $prop, $default = '' ) {
	omero_setup_object_loop(); // Ensure shop loop is setup.

	return isset( $GLOBALS['omero_object_loop'], $GLOBALS['omero_object_loop'][ $prop ] ) ? $GLOBALS['omero_object_loop'][ $prop ] : $default;
}

/**
 * Sets a property in the omero_object_loop global.
 *
 * @since 3.3.0
 * @param string $prop Prop to set.
 * @param string $value Value to set.
 */
function omero_set_object_loop_prop( $prop, $value = '' ) {
	if ( ! isset( $GLOBALS['omero_object_loop'] ) ) {
		omero_setup_object_loop();
	}
	$GLOBALS['omero_object_loop'][ $prop ] = $value;
}


if ( ! function_exists( 'omero_object_loop_start' ) ) {

	/**
	 * Output the start of a object loop. By default this is a UL.
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function omero_object_loop_start( $echo = true ) {
		ob_start();

		omero_set_object_loop_prop( 'loop', 0 );

		// wc_get_template( 'loop/loop-start.php' );
        get_template_part( 'template-parts/loop/loop', 'start' );

		$loop_start = apply_filters( 'omero_object_loop_start', ob_get_clean() );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf('%s', $loop_start);
		} else {
			return $loop_start;
		}
	}
}

if ( ! function_exists( 'omero_object_loop_end' ) ) {

	/**
	 * Output the end of a object loop. By default this is a UL.
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function omero_object_loop_end( $echo = true ) {
		ob_start();

		// wc_get_template( 'loop/loop-end.php' );
        get_template_part( 'template-parts/loop/loop', 'end' );

		$loop_end = apply_filters( 'omero_object_loop_end', ob_get_clean() );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf('%s', $loop_end);
		} else {
			return $loop_end;
		}
	}
}

/**
 * Resets the omero_object_loop global.
 *
 * @since 3.3.0
 */
function omero_object_reset_loop() {
	unset( $GLOBALS['omero_object_loop'] );
}

if ( ! function_exists( 'omero_get_thumbnail_object_url' ) ) {
	/**
	 * Output the end of a object loop. By default this is a UL.
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function omero_get_thumbnail_object_url() {
		$id = get_the_ID();
		$size = 'large';
		if (has_post_thumbnail( $id ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), $size );
			return $image[0];
		}

		// use first attached image
		$images = get_children( 'post_type=attachment&post_mime_type=image&post_parent=' . $id );
		if (!empty($images)) {
			$image = reset($images);
			$image_data = wp_get_attachment_image_src( $id, $size );
			return $image_data[0];
		}

		// use no preview fallback
		return omero_get_placeholder_image();
	}
}

if ( ! function_exists( 'omero_related_object_exclude_current_object' ) ) {
	/**
	 * Filter exclude current object in related objects
	 *
	 * @return void
	 */
	function omero_related_object_exclude_current_object($args) {
		$cur_object = get_the_ID();
		$args['post__not_in'] = [$cur_object];

		return $args;
	}
}

if (!function_exists('omero_object_header')) {
    /**
     * Display the post header with a link to the single post
     *
     * @since 1.0.0
     */
    function omero_object_header() {
        ?>
        <header class="entry-header">
			<?php the_title('<h1 class="alpha entry-title">', '</h1>'); ?>
        </header><!-- .entry-header -->
        <?php
    }
}

if (!function_exists('omero_object_thumbnail')) {
    /**
     * Display object thumbnail
     *
     */
    function omero_object_thumbnail($size = 'post-thumbnail') {
        if (has_post_thumbnail()) {
			?>
            <figure class="post-thumbnail object-image">
				<a href="<?php the_permalink() ?>" title="<?php the_title() ?>">
					<?php the_post_thumbnail(!is_singular() ? $size : 'full'); ?>
				</a>
			</figure>
			<?php
        }
    }
}

if (!function_exists('omero_object_tags')) {
    /**
     * Display the post taxonomies
     *
     * @since 2.4.0
     */
    function omero_object_tags() {
        /* translators: used between list items, there is a space after the comma */

        /* translators: used between list items, there is a space after the comma */
        $tags_list = get_the_tag_list('', '  ');
        ?>
        <aside class="entry-taxonomy">
            <?php if ($tags_list) : ?>
                <div class="tags-links">
                    <span class="screen-reader-text"><?php echo esc_html(_n('Tag:', 'Tags:', count(get_the_tags()), 'omero')); ?></span>
                    <?php printf('%s', $tags_list); ?>
                </div>
            <?php endif; ?>
        </aside>
        <?php
    }
}

if (!function_exists('omero_object_date_with_format')) {
    /**
     * Display the post meta 
     *
     * @since 1.0.1
     */
    function omero_object_date_with_format($format = '') {
        $object_id = get_the_ID();
		$object_time_start = get_post_meta($object_id, 'object_time_start', true);
		$d = get_the_date('d');
		$m = get_the_date('M');
		$y = get_the_date('Y');
		if(!empty($object_time_start)) {
			$d = wp_date('d', $object_time_start);
			$m = wp_date('M', $object_time_start);
			$y = wp_date('Y', $object_time_start);
		}
        ?>
        <div class="posted-on">
            <a href="<?php the_permalink() ?>">
                <span class="posted-on-day"><?php echo esc_html($d) ?></span><span class="posted-on-month"><?php echo esc_html($m) ?></span><span class="posted-on-year"><?php echo esc_html($y) ?></span>
            </a>
        </div>
        <?php
    }
}

if (!function_exists('omero_object_loop_title')) {
    function omero_object_loop_title() {
        ?>
        <h3 class="object-loop-title">
			<a href="<?php the_permalink() ?>"><?php the_title() ?></a>
		</h3>
        <?php
    }
}

if (!function_exists('omero_object_loop_excerpt')) {
    function omero_object_loop_excerpt() {
        ?>
        <div class="object-loop-exerpt"><?php the_excerpt(); ?></div>
        <?php
    }
}

if (!function_exists('omero_object_loop_author')) {
    function omero_object_loop_author() {
		$field = get_post_meta( get_the_ID(), '_created_by', 1 );
		if (!empty($field)) {
			?>
			<div class="object-loop-author"><?php echo esc_html($field); ?></div>
			<?php
		}
    }
}

if (!function_exists('omero_object_loop_date')) {
    function omero_object_loop_date() {
		$field = get_post_meta( get_the_ID(), '_inauguration_date', 1 );
		if (!empty($field)) {
			?>
			<div class="object-loop-date"><?php echo esc_html($field); ?></div>
			<?php
		}
    }
}

if (!function_exists('omero_object_loop_button')) {
    function omero_object_loop_button($text = '') {
		$text = !empty($text) ? $text : __('View More', 'omero');
        ?>
        <div class="omero-button-effect">
            <a class="button-effect" href="<?php the_permalink() ?>" title="<?php the_title() ?>">
                <span class="omero-button-content-wrapper">
                    <span class="omero-button-text"><?php echo esc_html($text) ?></span>
                    <span class="omero-button-icon right"><i class="omero-icon-arrow-right1"></i></span>
                </span>
            </a>
        </div>
        <?php
    }
}

if (!function_exists('omero_object_loop_button_icon')) {
    function omero_object_loop_button_icon() {
        ?>
        <div class="omero-button-icon">
            <a class="btn-icon-link" href="<?php the_permalink() ?>">
				<span class="button-icon-inner"><i class="omero-icon-arrow-right"></i></span>
            </a>
        </div>
        <?php
    }
}

if (!function_exists('omero_object_title_text')) {
    function omero_object_title_text($title_text) {
		if (!empty($title_text)) {
			?>
			<div class="object-title-text"><span><?php echo esc_html($title_text) ?></span></div>
			<?php
		}
    }
}

if (!function_exists('omero_get_default_object')) {
    function omero_get_default_object() {
        $args = [
            'numberposts' => 1,
            'post_type'   => 'object',
            'fields' => 'ids',
            'orderby' => 'date',
            'order' => 'ASC'
        ];
        $post_id = get_posts($args);
        if(!empty($post_id) && isset($post_id[0])){
            return $post_id[0];
        }else{
            return false;
        }

    }
}


if (!function_exists('omero_object_meta')) {
    /**
     * Display the post meta
     *
     * @since 1.0.0
     */
    function omero_object_meta($atts = array()) {
        global $post;
        if ('post' !== get_post_type()) {
            return;
        }

        extract(
            shortcode_atts(
                array(
                    'show_date'    => true,
                    'show_author'  => true,
                ),
                $atts
            )
        );

		?>
		<div class="object-meta">
			<?php 
			if ($show_author) {
				omero_object_loop_author();
			}
			if ($show_date) {
				omero_object_loop_date();
			}
			?>
		</div>
		<?php
	}
}

if (!function_exists('omero_object_socials')) {
    function omero_object_socials() {
		$post_id = get_the_ID();
		$socials = get_post_meta($post_id, '_team_socials_group', true);
		if (!empty($socials) && !empty($socials[0])) {
            $social = $socials[0];
            ?>
            <ol class="object_socials_list">
                <?php if (!empty($social['facebook'])) : ?>
                    <li>
                        <a class="omero-icon-socical" href="<?php echo esc_url($social['facebook']); ?>" target="_blank"><i class="omero-icon-facebook-f"></i></a>
                    </li>
                <?php endif; ?>
                <?php if (!empty($social['instagram'])) : ?>
                    <li>
                        <a class="omero-icon-socical" href="<?php echo esc_url($social['instagram']); ?>" target="_blank"><i class="omero-icon-instagram"></i></a>
                    </li>
                <?php endif; ?>
                <?php if (!empty($social['linkedin'])) : ?>
                    <li>
                        <a class="omero-icon-socical" href="<?php echo esc_url($social['linkedin']); ?>" target="_blank"><i class="omero-icon-linkedin-in"></i></a>
                    </li>
                <?php endif; ?>
                <?php if (!empty($social['email'])) : ?>
                    <li>
                        <a class="omero-icon-socical" href="mailto:<?php echo esc_attr($social['email']); ?>"><i class="omero-icon-envelope"></i></a>
                    </li>
                <?php endif; ?>
                <?php do_action('omero_object_more_socials', $post_id); ?>
            </ol>
        <?php }
    }
}

if (!function_exists('omero_loop_get_object_thumbnail_url')) {
    /**
     * Get loop thumbnail Url
     *
     */
    function omero_loop_get_object_thumbnail_url($override_image = null, $image_size = '') {
		if (empty($override_image)) {
			if (has_post_thumbnail()) {
				if (empty($image_size)) {
					$image_size = omero_get_object_loop_prop('image_size', 'large');
				}
				$image_url = get_the_post_thumbnail_url( get_the_ID(), $image_size ); 
			} else {
				$image_url = omero_get_placeholder_image();
			}
		} else {
			$image_url = $override_image;
		}
		
		return $image_url;
    }
}

if (!function_exists('omero_loop_object_thumbnail')) {
    /**
     * Display loop thumbnail
     *
     */
    function omero_loop_object_thumbnail($override_image = null, $link_wrap = true, $image_size = '') {
		$image_url = omero_loop_get_object_thumbnail_url($override_image, $image_size);
		?>
		<figure class="post-thumbnail object-thumbnail-wrapper">
			<?php if ($link_wrap) { ?><a href="<?php the_permalink() ?>" title="<?php the_title() ?>"><?php } ?>
				<img class="thumbnail-image object-thumbnail" src="<?php echo esc_url($image_url) ?>" alt="<?php the_title() ?>">
			<?php if ($link_wrap) { ?></a><?php } ?>
		</figure>
		<?php
    }
}

if (!function_exists('omero_object_loop_index')) {
    function omero_object_loop_index($index, $before = '', $after = '', $print = true) {
        if (is_int($index)) {
            $index = str_pad($index, 2, '0', STR_PAD_LEFT);
        }
		if (!$print) {
			ob_start();
		}
		?>
        <div class="object-index-item"><span><?php printf('%1$s%2$s%3$s', $before, esc_html($index), $after ); ?></span></div>
        <?php
		if (!$print) {
			return ob_get_clean();
		}
    }
}

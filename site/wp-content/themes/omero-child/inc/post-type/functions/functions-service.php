<?php
if (!function_exists('omero_service_extra_informations')) {
    function omero_service_extra_informations()
    {
        $extra_info = get_post_meta(get_the_ID(), '_service_extra_info', true);
        if (!empty($extra_info)) {
            printf('<div class="omero_extra_informations">%s</div>', wp_kses_post($extra_info));
        }
    }
}

if (!function_exists('omero_service_bottom_block_template')) {
    function omero_service_bottom_block_template()
    {
        if (function_exists('omero_render_elementor_block')) {
            $bottom_block = get_post_meta(get_the_ID(), '_service_bottom_block', true);
            if (empty($bottom_block)) {
                $bottom_block = omero_get_theme_option('service_bottom_block', '');
                if (empty($bottom_block)) {
                    return;
                }
            }

            omero_render_elementor_block($bottom_block);
        }
    }
}

if (!function_exists('omero_service_loop_button')) {
    function omero_service_loop_button($text = '') {
        $text = !empty($text) ? $text : __('Read more', 'omero');
        ?>
        <a class="more-link service-button" href="<?php the_permalink() ?>" title="<?php the_title() ?>">
            <span class="elementor-button-content-wrapper">
                <span class="button-text" data-text="<?php echo esc_attr($text) ?>"><?php echo esc_html($text) ?></span>
                <img src="https://ooxlimited.com/wp-content/uploads/2026/03/arrowr.png"/>
            </span>
        </a>
        <?php
    }
}

if (!function_exists('omero_service_loop_icon')) {
    function omero_service_loop_icon($icon = '') {
		if (!empty($icon)) {
			?>
			<div class="service_icon">
				<?php printf('%s', $icon); ?>
			</div>
			<?php
		}
    }
}

if (!function_exists('omero_service_loop_excerpt')) {
    function omero_service_loop_excerpt() {
        ?>
        <div class="service-loop-exerpt"><?php the_excerpt(); ?></div>
        <?php
    }
}

if (!function_exists('omero_service_loop_index')) {
    function omero_service_loop_index($index, $before = '', $after = '') {
        if (is_int($index)) {
            $index = str_pad($index, 2, '0', STR_PAD_LEFT);
        }
		?>
        <div class="service-index-item"><span><?php printf('%1$s%2$s%3$s', $before, esc_html($index), $after ); ?></span></div>
        <?php
    }
}

if (!function_exists('omero_service_loop_title')) {
    function omero_service_loop_title($icon = '') {
        ?>
        <h3 class="service-loop-title <?php if (!empty($icon)) echo 'has-icon'; ?>">
			<a href="<?php the_permalink() ?>"><?php the_title() ?></a>
            <?php omero_service_loop_icon($icon) ?>
		</h3>
        <?php
    }
}

if (!function_exists('omero_service_includes')) {
    function omero_service_includes(array $includes) {
        if (empty($includes)) {
            return;
        }

        echo '<ul class="service-include-features">';
        foreach ($includes as $i => $item) {
            if (empty($item)) {
                continue;
            }

            printf('<li class="item-feature"><span class="include-index">%s.</span>%s</li>',
                str_pad(($i+1), 2, '0', STR_PAD_LEFT),
                $item
            );
        }
        echo '</ul>';
    }
}

if (!function_exists('omero_service_thumbnail')) {
    /**
     * Display service thumbnail
     *
     */
    function omero_service_thumbnail($size = 'post-thumbnail') {
        ?>
        <figure class="post-thumbnail service-image" data-cursor-text="<?php esc_attr_e('View', 'omero') ?>">
            <a href="<?php the_permalink() ?>" title="<?php the_title() ?>">
            <?php 
            if (has_post_thumbnail()) {
                the_post_thumbnail(!is_singular('service') ? $size : 'full'); 
            } else {
                omero_print_placeholder_image(['class' => '']);
            }
            ?>
            </a>
        </figure>
        <?php
    }
}
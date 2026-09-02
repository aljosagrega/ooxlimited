<?php
if (!function_exists('omero_get_default_game')) {
    function omero_get_default_game()
    {
        $args = [
            'numberposts' => 1,
            'post_type'   => 'game',
            'fields' => 'ids',
            'orderby' => 'date',
            //'order' => 'ASC'
        ];
        $post_id = get_posts($args);
        if (!empty($post_id) && isset($post_id[0])) {
            return $post_id[0];
        } else {
            return false;
        }
    }
}

if (!function_exists('omero_game_loop_button')) {
    function omero_game_loop_button($text = '') {
        $text = !empty($text) ? $text : __('Details', 'omero');
        ?>
        <a class="more-link game-button btn-slip-effect" href="<?php the_permalink() ?>" title="<?php the_title() ?>">
            <span class="elementor-button-content-wrapper">
                <span class="button-text hover-text" data-text="<?php echo esc_attr($text) ?>"><?php echo esc_html($text) ?></span>
                <i class="omero-icon-arrow-right1"></i>
            </span>
        </a>
        <?php
    }
}

if (!function_exists('omero_get_game_archive_page')) {
    /**
     * Get game archive page id
     *
     * @return int
     */
    function omero_get_game_archive_page()
    {
        $archive_page = omero_get_theme_option('game_archive_page', '');
        if (empty($archive_page)) {
            return 0;
        }
        $archive_page = omero_get_page_by_slug($archive_page);
        if (empty($archive_page)) {
            return 0;
        }

        return is_object($archive_page) ? absint($archive_page->ID) : absint($archive_page);
    }
}

if (!function_exists('omero_is_game_archive_page')) {
    /**
     * Check current page is Game Archive page or not
     *
     * @return bool
     */
    function omero_is_game_archive_page($game_archive_page = 0)
    {
        $archive_page = $game_archive_page ? $game_archive_page : omero_get_game_archive_page();
        return $archive_page && $archive_page == get_queried_object_id();
    }
}


if (!function_exists('omero_render_game_archive_list')) {
    /**
     * Render game archive List
     *
     */
    function omero_render_game_archive_list()
    {
        global $wp_query;

        $atts = [
            'style' => 'normal',
            'paginate' => 1,
            'paginate_type' => 'pagination',
            'is_shortcode' => false,
            'class' => ' omero-game-grid-style',
            'is_flex_box' => 'yes',
        ];

        $data_content = apply_filters('omero_render_game_archive_list_data_content', [
            'classes_item' => [
                'column-12',
                'column-tablet-6',
            ]
        ]);

        echo (new Omero_Posttype('game', apply_filters('omero_render_game_archive_list_atts', $atts), $wp_query))->get_content($data_content); // WPCS: XSS ok
    }
}



if (!function_exists('omero_header_archive_game_content')) {
    /**
     * Display content on Header of the archive game
     *
     * @return void
     */
    function omero_header_archive_game_content()
    {
        if (omero_get_theme_option('show_archive_game_header_content', '') === 'yes') {
            if (is_tax(['game_genre', 'game_platform'])) {
                the_archive_description('<div class="omero_archive_game_content taxonomy-description">', '</div>');
            } elseif (omero_is_game_archive_page()) {
                $content = get_the_content(null, false, get_queried_object_id());
                $content = apply_filters('the_content', $content);
                $content = str_replace(']]>', ']]&gt;', $content);
                printf('<div class="omero_archive_game_content">%s</div>', $content);
            } elseif (is_post_type_archive('game')) {
            }
        }
    }
}

if (!function_exists('omero_game_logo_studio')) {
    /**
     * Display Game Studio's Logo
     *
     * @return void
     */
    function omero_game_logo_studio($post_id = 0)
    {
        $post_id = empty($post_id) ? get_the_ID() : absint($post_id);
        $logo_id = get_post_meta($post_id, '_logo_id', true);
        if (empty($logo_id)) {
            $logo_url = get_post_meta($post_id, '_logo', true);
            if (empty($logo_url)) {
                return;
            }
        }
        $logo_url = wp_get_attachment_image_url( $logo_id, 'medium' );
        if (empty($logo_url)) {
            return;
        }

        printf(
            '<div class="game-studio-logo"><img class="logo" src="%s" alt="%s"/></div>',
            esc_url($logo_url),
            esc_attr(get_the_title($post_id).' Logo'),
        );
    }
}

if (!function_exists('omero_game_list_terms')) {
    function omero_game_list_terms($taxonomy, $post_id = 0, $show_more = true) {
        $post_id = empty($post_id) ? get_the_ID() : absint( $post_id );
        $term_list = omero_get_the_term_list_link($post_id, $taxonomy, $show_more);
        if (!empty($term_list)) {
            printf('<div class="game-list-terms %s">%s</div>', $taxonomy, $term_list);
        }
    }
}

if (!function_exists('omero_get_the_term_list_link')) {
    function omero_get_the_term_list_link( $post_id, $taxonomy, $show_more = true ) {
        
        $terms = get_the_terms( $post_id, $taxonomy );

        if ( is_wp_error( $terms ) ) {
            return $terms;
        }
        if ( empty( $terms ) ) {
            return false;
        }

        $links = '';
        $count = count($terms);
        $i = 1;
        foreach ( $terms as $term ) {
            $more = 0;
            if ($taxonomy == 'game_platform' && $count > 3 && $show_more) {
                if ($i > 3) {
                    break;
                }
                if ($i == 3) {
                    $more = $count-2;
                }
            }

            $func = 'omero_get_'.$taxonomy.'_term_link';
            if (function_exists($func)) {
                $links .= $func( $term, $more );
            }
            $i++;
        }

        return $links;
    }
}

if (!function_exists('omero_get_game_genre_term_link')) {
    function omero_get_game_genre_term_link( $term ) {
        $link = get_term_link( $term, $term->taxonomy );
        if ( is_wp_error( $link ) ) {
            return '';
        }
        
        $style = '';
        $color = get_term_meta( $term->term_id, '_genre_color', true );
        if (!empty($color)) {
            $style = sprintf('style="--genre-color: %s"', esc_attr($color));
        }
        return '<a class="term-link omero-path-wrapper" '.$style.' href="' . esc_url( $link ) . '" rel="tag"><span class="term-name">' . $term->name . '</span></a>';
    }
}

if (!function_exists('omero_get_game_platform_term_link')) {
    function omero_get_game_platform_term_link( $term, int $more = 0 ) {
        $link = get_term_link( $term, $term->taxonomy );
        if ( is_wp_error( $link ) ) {
            return '';
        }
        
        $logo_id = get_term_meta($term->term_id, '_logo_id', true);
        $logo_url = '';
        $icon = '';

        if (!empty($logo_id)) {
            $logo_url = wp_get_attachment_image_url( $logo_id, 'thumbnail' );
            $mime_type = get_post_mime_type( $logo_id );
            if ( strpos( $mime_type, 'svg' ) !== false ) {
                $svg_path = get_attached_file( $logo_id );
                if ( file_exists( $svg_path ) ) {
                    $icon = file_get_contents( $svg_path );
                }
            }
        } else {
            $logo_url = get_term_meta($term->term_id, '_logo', true);
        }

        if (empty($logo_url) && empty($icon)) {
            return;
        }

        if ($more > 0) {
            $icon = sprintf('<span class="showmore-icon">+%d</span>', $more);
        } elseif (empty($icon)) {
            $icon = sprintf('<img class="logo" width="30" height="30" src="%s" />', esc_url($logo_url));
        }

        return sprintf(
            '<a class="term-link omero-path-wrapper omero-path-border" href="%s" title="%s" rel="tag"><span class="term-logo">%s</span></a>',
            esc_url($link),
            esc_attr($term->name),
            $icon
        );
    }
}

if (!function_exists('omero_add_logo_meta_for_game_platform_terms')) {
    /**
     * Add logo attachment ID to term meta based on slug => logo name mapping
     *
     * @param array $logos
     * @param string $taxonomy
     */
    function omero_add_logo_meta_for_game_platform_terms( array $logos, $taxonomy = 'game_platform' ) {
        foreach ( $logos as $term_slug => $logo_name ) {

            $term = get_term_by( 'slug', $term_slug, $taxonomy );
            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }

            $args = [
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                's'              => $logo_name,
                'meta_query'     => [
                    [
                        'key'     => '_wp_attached_file',
                        'value'   => '.svg',
                        'compare' => 'LIKE',
                    ],
                ],
            ];

            $query = new WP_Query( $args );

            if ( empty( $query->posts ) ) {
                continue;
            }

            $attachment_id = (int) $query->posts[0];
            
            $mime = get_post_mime_type( $attachment_id );
            if ( $mime !== 'image/svg+xml' ) {
                continue;
            }

            update_term_meta( $term->term_id, '_logo_id', $attachment_id );
            
            $logo_url = wp_get_attachment_url( $attachment_id );
            if ( !empty($logo_url) ) {
                update_term_meta( $term->term_id, '_logo', $logo_url );
            }
        }
    }
}

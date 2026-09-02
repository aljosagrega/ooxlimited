<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly    

if (!function_exists('lxdb_is_bcn_nav_activated')) {
    function lxdb_is_bcn_nav_activated() {
        return function_exists('bcn_display') ? true : false;
    }
}

if (!function_exists('lxdb_is_cmb2_activated')) {
    function lxdb_is_cmb2_activated() {
        return defined('CMB2_LOADED') ? true : false;
    }
}

if (!function_exists('lxdb_is_revslider_activated')) {
    function lxdb_is_revslider_activated() {
        return class_exists('RevSliderBase');
    }
}

if (!function_exists('lxdb_is_wpml_activated')) {
    function lxdb_is_wpml_activated() {
        return class_exists('SitePress') ? true : false;
    }
}

if (!function_exists('lxdb_is_woocommerce_activated')) {
    /**
     * Query WooCommerce activation
     */
    function lxdb_is_woocommerce_activated() {
        return class_exists('WooCommerce') ? true : false;
    }
}


if (!function_exists('lxdb_is_wcmp_activated')) {
    /**
     * Query WooCommerce activation
     */
    function lxdb_is_wcmp_activated() {
        return class_exists('WCMp') ? true : false;
    }
}

if (!function_exists('lxdb_is_elementor_activated')) {
    function lxdb_is_elementor_activated() {
        return defined('ELEMENTOR_VERSION') ? true : false;
    }
}

if (!function_exists('lxdb_is_elementor_pro_activated')) {
    function lxdb_is_elementor_pro_activated() {
        return function_exists('elementor_pro_load_plugin') ? true : false;
    }
}

if (!function_exists('lxdb_is_redux_activated')) {
    function lxdb_is_redux_activated() {
        return class_exists('Redux') ? true : false;
    }
}

if (!function_exists('lxdb_is_contactform_activated')) {
    function lxdb_is_contactform_activated() {
        return class_exists('WPCF7');
    }
}

if (!function_exists('lxdb_is_mailchimp_activated')) {
    include_once ABSPATH . 'wp'.'-admin/includes/plugin.php';
    function lxdb_is_mailchimp_activated() {
        return is_plugin_active( 'mailchimp-for-wp/mailchimp-for-wp.php' );
    }
}

if (lxdb_is_mailchimp_activated() && !function_exists('_mc4wp_load_plugin')) {
    function _mc4wp_load_plugin() {
        return 'Add Mailchimp Removed Function';
    }
}

if (!function_exists('lxdb_is_wishlist_activated')) {
    function lxdb_is_wishlist_activated($type = '') {
        return function_exists( 'woosw_init' );
    }
}

if (!function_exists('lxdb_is_autoptimize_activated')) {
    function lxdb_is_autoptimize_activated() {
        return class_exists( 'autoptimizeBase' );
    }
}

if (!function_exists('lexus_get_theme_option')) {
    function lexus_get_theme_option($option_name, $default = false) {

        if ($option = get_option('lexus_options_' . $option_name)) {
            $default = $option;
        }

        return $default;
    }
}

if (!function_exists('lexus_post_meta')) {
    /**
     * Display the post meta
     *
     * @since 1.0.0
     */
    function lexus_post_meta($atts = array()) {
        global $post;
        if ('post' !== get_post_type()) {
            return;
        }

        extract(
            shortcode_atts(
                array(
                    'show_cat'     => true,
                    'show_date'    => true,
                    'show_author'  => true,
                    'show_comment' => true,
                ),
                $atts
            )
        );

        $author = '';
        // Author.
        if ($show_author == 1) {
            $author_id = $post->post_author;
            $author    = sprintf(
                '<div class="post-author">%1$s<span>' . esc_html__('By', 'themelexus-debug') . '<a href="%2$s" class="url fn" rel="author">%3$s</a></span></div>',
                get_avatar( get_the_author_meta( 'ID', $author_id ), $size = '30', $default = '', $alt = '', $args = array( 'class' => 'wt-author-img' ) ),
                esc_url(get_author_posts_url(get_the_author_meta('ID'))),
                esc_html(get_the_author_meta('display_name', $author_id))
            );
        }

        $categories_list = get_the_category_list('<span class="dot">,</span>');
        $categories      = '';
        if ($show_cat && $categories_list) {
            // Make sure there's more than one category before displaying.
            $categories = '<div class="categories-link"> <span class="screen-reader-text">' . esc_html__('Categories', 'themelexus-debug') . '</span>' . $categories_list . '</div>';
        }

        $posted_on = '';
//         Posted on.
        if ($show_date) {
            $posted_on = '<div class="posted-on">' . sprintf('<a href="%1$s" rel="bookmark">%2$s</a>', esc_url(get_permalink()), get_the_date()) . '</div>';
        }

        echo wp_kses(
            sprintf('%3$s  %1$s %2$s', $categories, $author, $posted_on), array(
                'div'  => array(
                    'class' => array(),
                ),
                'span' => array(
                    'class' => array(),
                ),
                'a'    => array(
                    'href'  => array(),
                    'rel'   => array(),
                    'class' => array(),
                ),
                'time' => array(
                    'datetime' => array(),
                    'class'    => array(),
                )
            )
        );

        if ($show_comment) { ?>
            <div class="meta-reply">
                <?php
                comments_popup_link(esc_html__('0 comments', 'themelexus-debug'), esc_html__('1 comment', 'themelexus-debug'), esc_html__('% comments', 'themelexus-debug'));
                ?>
            </div>
            <?php
        }

    }
}

if (!function_exists('lexus_post_sharing')) {
    /**
     * Display the post taxonomies
     *
     * @since 2.4.0
     */
    function lexus_post_sharing(array $socials) {
        if (empty($socials)) {
            return;
        }
        ?>
        <div class="pbr-social-share">
            <span class="social-share-header"><?php _e('Share:', 'themelexus-debug') ?></span>
            <?php foreach ($socials as $key => $item) { 
                if (empty($item['url']) || empty($item['icon'])) {
                    continue;
                }
                $name = sprintf(__('Share on %s', 'themelexus-debug'), ucfirst($key));
                ?>
                <a class="bo-social-<?php echo esc_attr($key) ?>" href="<?php echo esc_url($item['url']) ?>" target="_blank" title="<?php echo esc_attr($name) ?>">
                    <?php echo wp_kses_post($item['icon']) ?>
                </a>
            <?php } ?>
        </div>
        <?php
    }
}


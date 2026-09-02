<?php

/**
 * WC_Breadcrumb class.
 *
 * @package WooCommerce\Classes
 * @version 2.3.0
 */

defined('ABSPATH') || exit;

function lxdb_fix_undefined_core_wc($element)
{
    if ($element->get_name() != 'woocommerce-breadcrumb') {
        return;
    }

    if (!lxdb_is_woocommerce_activated()) {
        /**
         * Override deactivate woocommerce
         *
         * @since 2.4.0
         */
        if (!function_exists('wc_get_template')) {
            function wc_get_template($template = '')
            {
                if ($template != 'global/breadcrumb.php') {
                    return '';
                }

                if (class_exists('LXDB_Breadcrumb_Section')) {
                    $filtered_breadcrumb = new LXDB_Breadcrumb_Section(false, '<i class="icon-element eicon-chevron-right"></i>');
                    $filtered_breadcrumb->set_strings([
                        /* translators: %d: Page index by pagination */
                        'paged' => esc_html__('Page %d', 'themelexus-debug'),
                        '404_error' => esc_html__('404 Page', 'themelexus-debug'),
                    ]);
                    if ($filtered_breadcrumb) {
                        $filtered_breadcrumb->print_breadcrump();
                    }
                }
            }
        }

        if (!class_exists('WC_Breadcrumb')) {

            /**
             * Breadcrumb class.
             */
            class WC_Breadcrumb
            {
                /**
                 * Breadcrumb trail.
                 *
                 * @var array
                 */
                protected $crumbs = array();

                /**
                 * Add a crumb so we don't get lost.
                 *
                 * @param string $name Name.
                 * @param string $link Link.
                 */
                public function add_crumb($name, $link = '')
                {
                    $this->crumbs[] = array(
                        wp_strip_all_tags($name),
                        $link,
                    );
                }

                /**
                 * Generate breadcrumb trail.
                 *
                 * @return array of breadcrumbs
                 */
                public function generate()
                {
                    $page_id = get_queried_object_id();

                    $breadcrumbs = [];

                    $title = (isset(get_queried_object()->term_id)) ? get_queried_object()->name : get_the_title($page_id);
                    if (is_post_type_archive()) {
                        $post_type = get_post_type();
                        $title = get_post_type_object($post_type)->labels->name;
                    }

                    if (!empty($title)) {
                        $breadcrumbs[] = [$title];
                    }

                    return $breadcrumbs;
                }
            }
        }
    }
}

add_action('elementor/widget/before_render_content', 'lxdb_fix_undefined_core_wc');
add_action('elementor/widget/after_render_content', function () {
    remove_action('elementor/widget/before_render_content', 'lxdb_fix_undefined_core_wc');
});

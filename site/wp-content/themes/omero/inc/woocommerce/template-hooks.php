<?php
/**
 * =================================================
 * Hook omero_header_archive_game
 * =================================================
 */

/**
 * =================================================
 * Hook omero_archive_games_list
 * =================================================
 */

/**
 * =================================================
 * Hook omero_single_elementor_builder
 * =================================================
 */

/**
 * =================================================
 * Hook omero_page
 * =================================================
 */

/**
 * =================================================
 * Hook omero_single_post_top
 * =================================================
 */

/**
 * =================================================
 * Hook omero_single_post
 * =================================================
 */

/**
 * =================================================
 * Hook omero_single_post_bottom
 * =================================================
 */

/**
 * =================================================
 * Hook omero_loop_post
 * =================================================
 */

/**
 * =================================================
 * Hook omero_after_container
 * =================================================
 */

/**
 * =================================================
 * Hook omero_before_footer
 * =================================================
 */

/**
 * =================================================
 * Hook omero_footer
 * =================================================
 */

/**
 * =================================================
 * Hook omero_after_footer
 * =================================================
 */
add_action('omero_after_footer', 'omero_sticky_single_add_to_cart', 999);

/**
 * =================================================
 * Hook wp_footer
 * =================================================
 */
add_action('wp_footer', 'omero_render_woocommerce_shop_canvas', 1);

/**
 * =================================================
 * Hook wp_head
 * =================================================
 */

/**
 * =================================================
 * Hook omero_before_header
 * =================================================
 */

/**
 * =================================================
 * Hook omero_before_content
 * =================================================
 */

/**
 * =================================================
 * Hook omero_before_container
 * =================================================
 */

/**
 * =================================================
 * Hook omero_content_top
 * =================================================
 */
add_action('omero_content_top', 'omero_shop_messages', 10);

/**
 * =================================================
 * Hook omero_post_content_before
 * =================================================
 */

/**
 * =================================================
 * Hook omero_post_content_after
 * =================================================
 */

/**
 * =================================================
 * Hook omero_sidebar
 * =================================================
 */

/**
 * =================================================
 * Hook omero_loop_before
 * =================================================
 */

/**
 * =================================================
 * Hook omero_loop_after
 * =================================================
 */

/**
 * =================================================
 * Hook omero_page_after
 * =================================================
 */

/**
 * =================================================
 * Hook omero_single_team_after
 * =================================================
 */

/**
 * =================================================
 * Hook omero_team_thumbnail
 * =================================================
 */

/**
 * =================================================
 * Hook omero_team_single_infomations
 * =================================================
 */

/**
 * =================================================
 * Hook omero_woocommerce_list_item_title
 * =================================================
 */
add_action('omero_woocommerce_list_item_title', 'omero_product_label', 5);
add_action('omero_woocommerce_list_item_title', 'omero_woocommerce_product_list_image', 10);

/**
 * =================================================
 * Hook omero_woocommerce_list_item_content
 * =================================================
 */
add_action('omero_woocommerce_list_item_content', 'woocommerce_template_loop_product_title', 10);
add_action('omero_woocommerce_list_item_content', 'omero_woocommerce_get_product_description', 15);
add_action('omero_woocommerce_list_item_content', 'woocommerce_template_loop_rating', 15);
add_action('omero_woocommerce_list_item_content', 'woocommerce_template_loop_price', 20);
add_action('omero_woocommerce_list_item_content', 'omero_stock_label', 25);

/**
 * =================================================
 * Hook omero_woocommerce_before_shop_loop_item
 * =================================================
 */

/**
 * =================================================
 * Hook omero_woocommerce_before_shop_loop_item_image
 * =================================================
 */
add_action('omero_woocommerce_before_shop_loop_item_image', 'omero_product_label', 10);
add_action('omero_woocommerce_before_shop_loop_item_image', 'woocommerce_template_loop_product_thumbnail', 15);

/**
 * =================================================
 * Hook omero_woocommerce_after_shop_loop_item_image
 * =================================================
 */
add_action('omero_woocommerce_after_shop_loop_item_image', 'omero_woocommerce_product_loop_action_start', 20);
add_action('omero_woocommerce_after_shop_loop_item_image', 'omero_compare_button', 20);
add_action('omero_woocommerce_after_shop_loop_item_image', 'omero_quickview_button', 20);
add_action('omero_woocommerce_after_shop_loop_item_image', 'omero_wishlist_button', 20);
add_action('omero_woocommerce_after_shop_loop_item_image', 'omero_woocommerce_product_loop_action_close', 20);

/**
 * =================================================
 * Hook omero_woocommerce_shop_loop_item_caption
 * =================================================
 */
add_action('omero_woocommerce_shop_loop_item_caption', 'omero_woocommerce_get_product_category', 5);
add_action('omero_woocommerce_shop_loop_item_caption', 'omero_single__rating_brands', 10);
add_action('omero_woocommerce_shop_loop_item_caption', 'woocommerce_template_loop_product_title', 15);
add_action('omero_woocommerce_shop_loop_item_caption', 'omero_woocommerce_get_product_description', 20);
add_action('omero_woocommerce_shop_loop_item_caption', 'woocommerce_template_loop_price', 30);
add_action('omero_woocommerce_shop_loop_item_caption', 'omero_single_product_extra_label', 25);
add_action('omero_woocommerce_shop_loop_item_caption', 'omero_single__quantity_cart', 35);

/**
 * =================================================
 * Hook omero_woocommerce_after_shop_loop_item
 * =================================================
 */

/**
 * =================================================
 * Hook omero_product_list_start
 * =================================================
 */

/**
 * =================================================
 * Hook omero_product_list_image
 * =================================================
 */
add_action('omero_product_list_image', 'omero_woocommerce_product_list_image', 10);

/**
 * =================================================
 * Hook omero_product_list_content
 * =================================================
 */
add_action('omero_product_list_content', 'woocommerce_template_loop_product_title', 10);
add_action('omero_product_list_content', 'omero_single_product_extra_label', 15);
add_action('omero_product_list_content', 'woocommerce_template_loop_rating', 15);
add_action('omero_product_list_content', 'woocommerce_template_loop_price', 20);

/**
 * =================================================
 * Hook omero_product_list_end
 * =================================================
 */

<?php
/**
 * =================================================
 * Hook omero_header_archive_game
 * =================================================
 */
add_action('omero_header_archive_game', 'omero_header_archive_game_content', 10);

/**
 * =================================================
 * Hook omero_archive_games_list
 * =================================================
 */
add_action('omero_archive_games_list', 'omero_render_game_archive_list', 10);

/**
 * =================================================
 * Hook omero_single_elementor_builder
 * =================================================
 */
add_action('omero_single_elementor_builder', 'omero_page_content', 10);

/**
 * =================================================
 * Hook omero_page
 * =================================================
 */
add_action('omero_page', 'omero_page_header', 10);
add_action('omero_page', 'omero_page_content', 20);

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
add_action('omero_single_post', 'omero_post_thumbnail', 20);
add_action('omero_single_post', 'omero_post_excerpt', 15);
add_action('omero_single_post', 'omero_post_header', 10);
add_action('omero_single_post', 'omero_post_content', 30);

/**
 * =================================================
 * Hook omero_single_post_bottom
 * =================================================
 */
add_action('omero_single_post_bottom', 'omero_post_taxonomy', 5);
add_action('omero_single_post_bottom', 'omero_post_nav', 10);
add_action('omero_single_post_bottom', 'omero_single_author', 15);
add_action('omero_single_post_bottom', 'omero_display_comments', 20);

/**
 * =================================================
 * Hook omero_loop_post
 * =================================================
 */
add_action('omero_loop_post', 'omero_post_content', 30);

/**
 * =================================================
 * Hook omero_after_container
 * =================================================
 */
add_action('omero_after_container', 'omero_output_related_products', 20);

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
add_action('omero_footer', 'omero_footer_default', 20);

/**
 * =================================================
 * Hook omero_after_footer
 * =================================================
 */

/**
 * =================================================
 * Hook wp_footer
 * =================================================
 */
add_action('wp_footer', 'omero_template_account_dropdown', 1);
add_action('wp_footer', 'omero_mobile_nav', 1);

/**
 * =================================================
 * Hook wp_head
 * =================================================
 */
add_action('wp_head', 'omero_pingback_header', 1);

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
add_action('omero_before_content', 'omero_archive_blog_top', 10);

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
add_action('omero_sidebar', 'omero_get_sidebar', 10);

/**
 * =================================================
 * Hook omero_loop_before
 * =================================================
 */
add_action('omero_loop_before', 'omero_blog_category_navigation', 10);

/**
 * =================================================
 * Hook omero_loop_after
 * =================================================
 */
add_action('omero_loop_after', 'omero_paging_nav', 10);

/**
 * =================================================
 * Hook omero_page_after
 * =================================================
 */
add_action('omero_page_after', 'omero_display_comments', 10);

/**
 * =================================================
 * Hook omero_single_team_after
 * =================================================
 */
add_action('omero_single_team_after', 'omero_team_bottom_block_template', 10);

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
add_action('omero_team_single_infomations', 'omero_team_header', 10);
add_action('omero_team_single_infomations', 'omero_team_job', 10);
add_action('omero_team_single_infomations', 'omero_team_informations_tab', 10);

/**
 * =================================================
 * Hook omero_woocommerce_list_item_title
 * =================================================
 */

/**
 * =================================================
 * Hook omero_woocommerce_list_item_content
 * =================================================
 */

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

/**
 * =================================================
 * Hook omero_woocommerce_after_shop_loop_item_image
 * =================================================
 */

/**
 * =================================================
 * Hook omero_woocommerce_shop_loop_item_caption
 * =================================================
 */

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

/**
 * =================================================
 * Hook omero_product_list_content
 * =================================================
 */

/**
 * =================================================
 * Hook omero_product_list_end
 * =================================================
 */

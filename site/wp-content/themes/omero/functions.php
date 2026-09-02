<?php
$theme       = wp_get_theme('omero');
$omero_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if (!isset($content_width)) {
    $content_width = 980; /* pixels */
}
require get_theme_file_path('inc/class-tgm-plugin-activation.php');
$omero = (object)array(
    'version' => $omero_version,
    /**
     * Initialize all the things.
     */
    'main'    => require 'inc/class-main.php',
);

require get_theme_file_path('inc/functions.php');
require get_theme_file_path('inc/template-hooks.php');
require get_theme_file_path('inc/template-functions.php');

require_once get_theme_file_path('inc/merlin/vendor/autoload.php');
require_once get_theme_file_path('inc/merlin/class-merlin.php');
require_once get_theme_file_path('inc/merlin-config.php');

require_once get_theme_file_path('inc/class-customize.php');

/**
 * Support theme
 */
do_action('lexus_preload_theme', 'omero');

if (omero_is_woocommerce_activated()) {
    $omero->woocommerce = require get_theme_file_path('inc/woocommerce/class-woocommerce.php');

    require get_theme_file_path('inc/woocommerce/class-woocommerce-adjacent-products.php');
    require get_theme_file_path('inc/woocommerce/woocommerce-functions.php');
    require get_theme_file_path('inc/woocommerce/woocommerce-template-functions.php');
    require get_theme_file_path('inc/woocommerce/woocommerce-template-hooks.php');
    require get_theme_file_path('inc/woocommerce/template-hooks.php');
    require get_theme_file_path('inc/woocommerce/class-woocommerce-settings.php');
    require get_theme_file_path('inc/woocommerce/class-woocommerce-brand.php');
    require get_theme_file_path('inc/woocommerce/class-woocommerce-extra.php');
    require get_theme_file_path('inc/woocommerce/class-woocommerce-quantity-field-shop-page.php');
}

if (omero_is_contactform_activated()) {
    require get_theme_file_path('inc/cf7/class-cf7-service.php');
}

if (omero_is_autoptimize_activated()) {
    require get_theme_file_path('inc/autoptimize/class-autoptimize.php');
}

if (omero_is_wishlist_activated()) {
    require get_theme_file_path('inc/wishlist/class-wishlist.php');
}


if (omero_is_elementor_activated()) {
    require get_theme_file_path('inc/elementor/functions-elementor.php');

    if (!defined('ELEMENTOR_PRO_VERSION') && version_compare( ELEMENTOR_VERSION, '3.18.0', '>=' )) {
        require get_theme_file_path('inc/elementor/class-fix-elementor.php');
    }

    $omero->elementor = require get_theme_file_path('inc/elementor/class-elementor.php');
    //====start_premium
    $omero->megamenu = require get_theme_file_path('inc/megamenu/megamenu.php');
    //====end_premium
    $omero->parallax = require get_theme_file_path('inc/elementor/class-section-parallax.php');


    if (defined('ELEMENTOR_PRO_VERSION')) {
        require get_theme_file_path('inc/elementor/functions-elementor-pro.php');
    }

    if (function_exists('hfe_init')) {
        require get_theme_file_path('inc/header-footer-elementor/class-hfe.php');
        require get_theme_file_path('inc/header-footer-elementor/class-menu-walker.php');
    }

    require_once get_theme_file_path('inc/elementor/elementor-control/class-elementor-control.php');
    require_once get_theme_file_path('inc/elementor/nested-carousel/init.php');
}

if (!is_user_logged_in()) {
    require get_theme_file_path('inc/modules/class-login.php');
}

if ( omero_is_cmb2_activated() ) {
    require get_theme_file_path( 'inc/cmb2/class-cmb2.php' );
}

// CPT
require get_theme_file_path('inc/post-type/init.php');

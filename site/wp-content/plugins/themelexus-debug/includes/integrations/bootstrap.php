<?php

/**
 * Try to include theme function files 
 *
 * @param $theme
 */
function lexus_include_theme($theme) {
    $old_themes = in_array($theme, ['soudify', 'interanio']);
    
    // Elementor
    if (defined('ELEMENTOR_VERSION')) {
        if (function_exists('hfe_init')) {
            if (!$old_themes) {
                require LXDB_PLUGIN_DIR . 'includes/integrations/general/class-breadcrumb.php';
            }
            require LXDB_PLUGIN_DIR . 'includes/integrations/general/class-custom-shapes.php';
        }
    }

    // Woocommerce
    if (class_exists('WooCommerce')) {
        require LXDB_PLUGIN_DIR . 'includes/integrations/general/class-wc-widget-product-brands.php';
        require LXDB_PLUGIN_DIR . 'includes/integrations/general/class-product-360-view.php';
        require LXDB_PLUGIN_DIR . 'includes/integrations/general/class-wc-widget-layered-nav.php';
    }

    // Monster
    if (class_exists('Monster_Widget')) {
        require LXDB_PLUGIN_DIR . 'includes/integrations/general/monster-widget.php';
    }

    // Recent Post
    require LXDB_PLUGIN_DIR . 'includes/integrations/general/recent-post.php';


    $init_file = LXDB_PLUGIN_DIR.'includes/integrations/'.$theme.'/init.php';
    if (file_exists($init_file)) {
        require_once $init_file;
    }

    // Missing Functions
    require LXDB_PLUGIN_DIR . 'includes/integrations/general/missing-functions.php';
}
add_action('lexus_preload_theme', 'lexus_include_theme', 30);
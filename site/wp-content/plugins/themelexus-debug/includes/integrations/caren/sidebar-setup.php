<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action( 'init', function() {
    // Add sidebar 
    if (post_type_exists('project')) {
        register_sidebar( array(
            'name'          => __( 'Sidebar Single Project', 'themelexus-debug' ),
            'id'            => 'sidebar-project',
            'description'   => __( 'Display in project single page', 'themelexus-debug' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span class="gamma widget-title">',
            'after_title'   => '</span>',
        ) );
    }

    if (post_type_exists('service')) {
        register_sidebar( array(
            'name'          => __( 'Sidebar Single Service', 'themelexus-debug' ),
            'id'            => 'sidebar-service',
            'description'   => __( 'Display in service single page', 'themelexus-debug' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span class="gamma widget-title">',
            'after_title'   => '</span>',
        ) );
    }

    
}, 99 );


add_action( 'widgets_init', function() {
    // Add sidebar 
    require __DIR__ . '/class-wp-widget-project-information.php';
    register_widget('Lexus_WP_Widget_Project_Details');
    
    require LXDB_PLUGIN_DIR . 'includes/integrations/general/class-wp-widget-services-list.php';
    register_widget('Lexus_WP_Widget_Service');
} );
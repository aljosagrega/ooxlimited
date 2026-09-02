<?php

/**
 * The template for displaying Service content within loops
 *
 * Style: Grid 1
 *
 * @package Omero\Templates
 */

defined('ABSPATH') || exit;

extract($args);

$includes = $includes ?? [];
?>
<li class="<?php echo esc_attr($class); ?>">
    <div class="omero-service-block">
        <?php
        do_action('omero_loop_service_open', $args);
        ?>
        <div class="service-caption">
            <?php
            omero_object_loop_title();    
            omero_service_loop_index($index);
            ?>
        </div>
        <div class="service-information">
            <?php
            omero_object_loop_excerpt();
            omero_service_includes($includes);
            omero_service_loop_button();
            ?>
        </div>
        <?php
        do_action('omero_loop_service_close', $args);
        ?>
    </div>
</li>
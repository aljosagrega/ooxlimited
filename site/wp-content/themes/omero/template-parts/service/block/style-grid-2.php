<?php

/**
 * The template for displaying Service content within loops
 *
 * Style: Grid 2
 *
 * @package Omero\Templates
 */

defined('ABSPATH') || exit;

extract($args);

$override_image = $override_image ?? '';
$icon = $icon ?? '';
?>
<li class="<?php echo esc_attr($class); ?>">
    <div class="service-block-wrapper">
        <div class="omero-service-block omero-path-wrapper only-bottom-left">
            <?php
            do_action('omero_loop_service_open', $args);
            ?>
            <div class="service-caption">
                <?php
                omero_object_loop_title();
                omero_object_loop_excerpt();
                ?>
            </div>
            <div class="service-information">
                <?php
                omero_service_loop_index($index);
                omero_service_loop_button();
                ?>
            </div>
            <?php
            do_action('omero_loop_service_close', $args);
            ?>
        </div>
        <?php omero_service_loop_icon($icon); ?>
    </div>
</li>
<?php

/**
 * The template for displaying Service content within loops
 *
 * Style: List 1
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
        <div class="omero-service-block only-bottom-left omero-path-wrapper">
            <?php
            do_action('omero_loop_service_open', $args);
            ?>
            <div class="service-caption">
                <?php
                omero_service_loop_index($index);
                ?>
                <div class="service-content-box">
                    <?php
                    omero_object_loop_title();
                    omero_object_loop_excerpt();
                    omero_service_loop_button();
                    ?>
                </div>
                <?php
                do_action('omero_loop_service_content', $args);
                ?>
            </div>
            <div class="service-image-box only-bottom-left omero-path-wrapper">
                <?php
                omero_loop_object_thumbnail($override_image, false);
                do_action('omero_loop_service_image', $args);
                ?>
            </div>
            <?php
            do_action('omero_loop_service_close', $args);
            ?>
        </div>
        <?php
        omero_service_loop_icon($icon);
        ?>
    </div>
</li>
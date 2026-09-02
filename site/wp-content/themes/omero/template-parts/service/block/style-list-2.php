<?php

/**
 * The template for displaying Service content within loops
 *
 * Style: List 2
 * 
 * @package Omero\Templates
 */

defined('ABSPATH') || exit;

extract($args);

?>
<li class="<?php echo esc_attr($class); ?>">
    <div class="omero-service-block">
        <?php
        do_action('omero_loop_service_open', $args);
        ?>
        <div class="service-caption">
            <div class="service-content-box">
                <?php
                omero_object_loop_title();
                ?>
            </div>
            <?php
            do_action('omero_loop_service_content', $args);
            ?>
        </div>
        <?php
        do_action('omero_loop_service_close', $args);
        ?>
    </div>
</li>
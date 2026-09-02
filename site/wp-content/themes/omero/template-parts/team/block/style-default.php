<?php

/**
 * The template for displaying Event content within loops
 *
 * Style: Default
 * 
 * @package Omero\Templates
 */

defined('ABSPATH') || exit;

extract($args);

$override_image = $override_image ?? '';
?>
<li class="<?php echo esc_attr($class); ?>">
    <div class="omero-team-block">
        <?php
        do_action('omero_loop_team_open', $args);

        ?>
        <div class="team-avatar-wrapper">
            <?php
            omero_loop_object_thumbnail($override_image, true);
            omero_team_socials();
            ?>
        </div>
        <div class="team-caption">
            <?php
            omero_team_loop_title();
            omero_team_job();

            do_action('omero_loop_team_content', $args);
            ?>
        </div>
        <?php

        do_action('omero_loop_team_close', $args);
        ?>
    </div>
</li>
<?php
/**
 * The template for displaying Game content within loops
 *
 * Style: Normal
 * 
 * @package Justico\Templates
 */

defined('ABSPATH') || exit;

extract($args);

$override_image = $override_image ?? '';
?>
<li class="<?php echo esc_attr($class); ?>">
    <div class="omero-game-block">
        <?php
        do_action('omero_loop_game_open', $args);        
        ?>
        <div class="game-thumbnail-wrapper">
            <?php
            omero_loop_object_thumbnail($override_image, true);
            omero_game_logo_studio();
            ?>
        </div>
        <div class="game-content-box">
            <div class="game-caption">
                <?php
                omero_game_list_terms('game_genre');
                omero_object_loop_title();
                ?>
            </div>
            <div class="game-content-bottom">
                <?php
                omero_game_list_terms('game_platform');
                ?>
            </div>
        </div>
        <?php
        do_action('omero_loop_game_close', $args);
        ?>
    </div>
</li>

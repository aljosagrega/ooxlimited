<?php
get_header();
?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <?php
            while (have_posts()) :
                the_post();

                do_action('omero_single_team_before');

                if (omero_check_post_is_elementor()) {
                    get_template_part('content', 'elementor-builder');
                } else {
                    get_template_part('template-parts/team/content', 'single');
                }

                /**
                 * Functions hooked in to omero_single_team_after action
                 *
                 * @see omero_team_bottom_block_template - 10
                 *
                 */
                do_action('omero_single_team_after');

            endwhile; // End of the loop.
            ?>

        </main><!-- #main -->
    </div><!-- #primary -->
<?php
get_footer();
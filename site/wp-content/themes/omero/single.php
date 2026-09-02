<?php
get_header(); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <?php
            while (have_posts()) :
                the_post();

                do_action('omero_single_post_before');

                if (omero_check_post_is_elementor()) {
                    get_template_part('content', 'elementor-builder');
                } else {
                    get_template_part('content', 'single');
                }
                
                do_action('omero_single_post_after');

            endwhile; // End of the loop.
            ?>

        </main><!-- #main -->
    </div><!-- #primary -->
<?php
if (!omero_check_post_is_elementor()) {
    do_action('omero_sidebar');
}
get_footer();

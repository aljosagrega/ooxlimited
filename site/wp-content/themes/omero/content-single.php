<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="single-content">
        <?php
        /**
         * Functions hooked in to omero_single_post_top action
         *
         */
        do_action('omero_single_post_top');

        /**
         * Functions hooked in to omero_single_post action
         * @see omero_post_thumbnail     - 20
         * @see omero_post_excerpt     - 15
         * @see omero_post_header        - 10
         * @see omero_post_content       - 30
         */
        do_action('omero_single_post');

        /**
         * Functions hooked in to omero_single_post_bottom action
         *
         * @see omero_post_taxonomy        - 5
         * @see omero_post_nav             - 10
         * @see omero_single_author        - 15
         * @see omero_display_comments     - 20
         */
        do_action('omero_single_post_bottom');
        ?>

    </div>

</article><!-- #post-## -->

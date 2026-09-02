<article id="post-<?php the_ID(); ?>" <?php post_class('article-default'); ?>>
    <?php omero_post_thumbnail('post-thumbnail', false, false); ?>
    <div class="post-content">
        <?php
        omero_post_header();
        /**
         * Functions hooked in to omero_loop_post action.
         *
         * @see omero_post_content         - 30
         */
        do_action('omero_loop_post');
        ?>
    </div>
</article><!-- #post-## -->
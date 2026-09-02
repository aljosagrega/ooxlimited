<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	/**
	 * Functions hooked in to omero_page action
	 *
	 * @see omero_page_header          - 10
	 * @see omero_page_content         - 20
	 *
	 */
	do_action( 'omero_page' );
	?>
</article><!-- #post-## -->

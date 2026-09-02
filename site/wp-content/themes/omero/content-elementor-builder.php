<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	/**
	 * Functions hooked in to omero_single_elementor_builder action
	 *
	 * @see omero_page_content         - 10
	 *
	 */
	do_action( 'omero_single_elementor_builder' );
	?>
</article><!-- #post-## -->

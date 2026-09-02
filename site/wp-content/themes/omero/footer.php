
		</div><!-- .col-full -->
        <?php
		/**
		 * Functions hooked in to omero_after_container action
		 *
         * @see omero_output_related_products - 20
         * 
		 */
		do_action('omero_after_container');
		?>
	</div><!-- #content -->

	<?php
    /**
     * Functions hooked in to omero_before_footer action
     *
     *
     */
    do_action( 'omero_before_footer' );
    if (omero_is_elementor_activated() && function_exists('hfe_init') && (hfe_footer_enabled() || hfe_is_before_footer_enabled())) {
        do_action('hfe_footer_before');
        do_action('hfe_footer');
    } else {
        ?>

        <footer id="colophon" class="site-footer" role="contentinfo">
            <?php
            /**
             * Functions hooked in to omero_footer action
             *
             * @see omero_footer_default - 20
             *
             *
             */
            do_action('omero_footer');

            ?>

        </footer><!-- #colophon -->

        <?php
    }

		/**
		 * Functions hooked in to omero_after_footer action
		 * @see omero_sticky_single_add_to_cart 	- 999 - woo
		 */
		do_action( 'omero_after_footer' );
	?>

</div><!-- #page -->

<?php

/**
 * Functions hooked in to wp_footer action
 * @see omero_template_account_dropdown 	- 1
 * @see omero_mobile_nav - 1
 * @see omero_render_woocommerce_shop_canvas - 1 - woo
 */

wp_footer();
?>
</body>
</html>

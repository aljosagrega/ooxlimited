<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

extract($args);

$post_type = omero_get_object_loop_prop('post_type');

$total   = isset( $total ) ? $total : omero_get_object_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : omero_get_object_loop_prop( 'current_page' );
$format  = isset( $format ) ? $format : '';

if ( $total <= 1 || $current >= $total ) { 
	return;
}

$next_url = omero_loadmore_link(
    apply_filters(
        'object_loadmore_args',
        array( // WPCS: XSS ok.
            'base'      => $base,
            'format'    => $format,
            'add_args'  => false,
            'current'   => max( 1, $current ),
            'total'     => $total,
        ),
        $post_type
    )
);

?>
<nav class="omero-loadmore <?php echo esc_attr($post_type) ?>-loadmore pagination">
	<?php
    $data_attr = [
        'data-total' => $total,
        'data-current' => $current,
        // 'href' => 'javascript:void(0)',
        'href' => esc_url($next_url),
        'class' => 'omero-post-loadmore loadmore-btn omero-path-wrapper btn-slip-effect',
    ];
    ?>
    <a <?php omero_parse_attr_html($data_attr, 1) ?>>
        <span class="elementor-button-content-wrapper">
            <span class="omero-btn-content">
                <span class="omero-btn-text elementor-button-text hover-text" data-text="<?php _e('Load more', 'omero') ?>"><?php _e('Load more', 'omero') ?></span>
            </span>
            <span class="elementor-button-icon-inner">
                <i aria-hidden="true" class="omero-icon-arrow-right1"></i></span>
        </span>
    </a>
</nav>

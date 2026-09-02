<?php
/** 
 * Product Gallery slideshow
 * 
 * @uses $args['product_id']
 * @uses $args['gallery']
 * 
*/
extract($args);

?>
<div class="omero-wrapper" data-product="<?php echo esc_attr($product_id) ?>">
    <div class="omero-gallery-grid omero-con">
        <div class="omero-con-inner elementor-grid">
            <?php
            $index = 1;
            foreach ( (array) $gallery as $attachment_id => $attachment_url ) {
                $size = $index === 1 ? 'full' : 'medium';
                $img = wp_get_attachment_image( $attachment_id, $size );
                if ($img) {
                    ?>
                    <div class="product-slideshow-item">
                        <a class="product-slideshow-inner" data-fancybox="gallery" data-elementor-open-lightbox="no" href="<?php echo esc_url($attachment_url) ?>">
                            <?php printf('%s', $img); ?>
                        </a>
                    </div>
                    <?php
                }
                $index++;
            }
            ?>
        </div>
    </div>
</div>

<?php
/** 
 * Product Gallery slideshow
 * 
 * @uses $args['product_id']
 * @uses $args['gallery']
 * @uses $args['swiper_pagination']
 * 
*/
extract($args);

?>
<div class="omero-wrapper swiper" data-product="<?php echo esc_attr($product_id) ?>">
    <div class="omero-gallery-slideshow omero-con omero-swiper">
        <div class="swiper-wrapper">
            <?php
            foreach ( (array) $gallery as $attachment_id => $attachment_url ) {
                $size = 'large';
                $img = wp_get_attachment_image( $attachment_id, $size );
                if ($img) {
                    ?>
                    <div class="swiper-slide product-slideshow-item">
                        <a class="product-slideshow-inner" data-fancybox="gallery" data-elementor-open-lightbox="no" href="<?php echo esc_url($attachment_url) ?>">
                            <?php printf('%s', $img); ?>
                        </a>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <?php printf('%s', $swiper_pagination); ?>
</div>

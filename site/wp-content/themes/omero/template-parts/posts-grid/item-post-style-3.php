<div class="post-style-3">
    <div class="post-inner">
        <?php omero_post_thumbnail('omero-post-grid', true, true); ?>
        <div class="post-content">
            <div class="entry-content">
                <div class="entry-meta-top">
                    <?php omero_post_meta(['show_cat' => true, 'show_date' => true, 'show_author' => true, 'show_comment' => false]); ?>
                </div>
                <?php the_title('<h3 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h3>'); ?>
                <div class="excerpt-content"><?php echo wp_trim_words(get_the_excerpt(), 30); ?></div>
                <div class="more-link-wrap">
                    <a class="more-link btn-slip-effect" href="<?php the_permalink() ?>">
                        <span class="elementor-button-content-wrapper">
                            <span class="hover-text" data-text="<?php echo esc_attr('Continue Reading', 'omero') ?>"><?php echo esc_html__('Continue Reading', 'omero'); ?></span>
                            <i class="omero-icon-arrow-right1"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

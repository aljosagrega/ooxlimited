<?php
get_header(); ?>
    <div id="primary" class="content">
        <main id="main" class="site-main">
            <div class="error-404 not-found">
                <div class="page-content">
                    <div class="page-header">
                        <div class="image">
                            <h2 class="error-title"><?php esc_html_e('404', 'omero'); ?></h2>
                            <img class="img-404" src="<?php echo get_theme_file_uri('assets/images/404/404.png') ?>" alt="<?php echo esc_attr__('404 Page not found', 'omero') ?>">
                        </div>
                        <div class="text">
                            <h2 class="error-subtitle"><?php _e('Oops! Page is not found', 'omero'); ?></h2>
                            <p class="error-text"><?php esc_html_e("We're not being able to find the page you're looking for", 'omero'); ?></p>
                            <div class="error-button">
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="go-back omero-path-wrapper btn-slip-effect">
                                    <span class="elementor-button-content-wrapper">
                                        <span class="hover-text" data-text="<?php echo esc_attr('Back to homepage', 'omero') ?>"><?php esc_html_e('Back to homepage', 'omero'); ?></span>
                                        <i class="omero-icon-arrow-right1"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div><!-- .page-content -->
                </div><!-- .error-404 -->
            </div>
        </main><!-- #main -->
    </div><!-- #primary -->
<?php
get_footer();
<header id="masthead" class="site-header header-1" role="banner">
    <div class="header-container">
        <div class="container header-main">
            <div class="header-left">
                <?php
                omero_site_branding();
                if (omero_is_woocommerce_activated()) {
                    ?>
                    <div class="site-header-cart header-cart-mobile">
                        <?php omero_cart_link(); ?>
                    </div>
                    <?php
                }
                ?>
                <?php omero_mobile_nav_button(); ?>
            </div>
            <div class="header-center">
                <?php omero_primary_navigation(); ?>
            </div>
            <div class="header-right desktop-hide-down">
                <div class="header-group-action">
                    <?php
                    omero_header_account();
                    if (omero_is_woocommerce_activated()) {
                        omero_header_wishlist();
                        omero_header_cart();
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</header><!-- #masthead -->

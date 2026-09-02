(function ($) {
    'use strict';

    const buttonListSel = `
        .woosc-area .add_to_cart_button:not(.omero-path-wrapper),
        .woosw-popup-content-bot a:not(.omero-path-wrapper),
        .woocommerce-ResetPassword .button[type="submit"]:not(.omero-path-wrapper),
        .woosw-list .woosw-copy .woosw-copy-btn .button:not(.omero-path-wrapper),
        .woocommerce-cart .return-to-shop .button:not(.omero-path-wrapper),
        .single_add_to_cart_button.button:not(.omero-path-wrapper),
        .button[name="apply_coupon"]:not(.omero-path-wrapper),
        .woocommerce-cart-form .button[name="update_cart"]:not(.omero-path-wrapper),
        .checkout-button:not(.omero-path-wrapper),

        .woocommerce-mini-cart__buttons .button:not(.omero-path-wrapper),

        #place_order:not(.omero-path-wrapper),

        .wc-block-components-button:not(.omero-path-wrapper)
    `;

    function initTriggerButton() {
        const $buttonList = $(buttonListSel);
        if (!$buttonList.length) {
            return;
        }

        $buttonList.addClass('omero-path-wrapper');
        $(document).trigger('omero-path-reload');

        $buttonList.each(function() {
            if ($(this).find('.hover-text').length) {
                return;
            }

            let text = $(this).text();
            
            $(this).wrapInner(`
                <span class="elementor-button-content-wrapper">
                    <span class="hover-text" data-text="${text}">
                        <span></span>
                    </span>
                </span>
            `);
            // $(this).attr('data-name', $(this).text());
        })
    }

    $(document).ready(function () {
        // cross_sells_carousel();
        initTriggerButton();
    });
    
    $(document.body).on('woosc_table_loaded woosq_loaded woosw_wishlist_show wc_cart_emptied update_checkout updated_checkout updated_wc_div updated_cart_totals country_to_state_changed updated_shipping_method applied_coupon removed_coupon adding_to_cart added_to_cart removed_from_cart wc_cart_button_updated cart_page_refreshed cart_totals_refreshed wc_fragments_loaded init_checkout payment_method_selected update_checkout updated_checkout checkout_error applied_coupon_in_checkout removed_coupon_in_checkout', function(){
        initTriggerButton();
    });
    
    $(document.body).on('wc_fragments_loaded wc_fragments_refreshed', function() {
        initTriggerButton();
    });

    $(document.body).on('updated_checkout', function() {
        initTriggerButton();
    });

    if ($('.wp-block-woocommerce-cart').length || $('.wp-block-woocommerce-checkout').length) {
        const observer = new MutationObserver((mutationsList, observer) => {
            initTriggerButton();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

})(jQuery);
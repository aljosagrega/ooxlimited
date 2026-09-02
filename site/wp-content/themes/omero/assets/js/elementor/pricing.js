(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            $('.elementor-price-table__button', $element).initOmeroBtn();
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-pricing.default', addHandler);
    });

})(jQuery);

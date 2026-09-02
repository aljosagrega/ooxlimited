(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(omeroSwiperBase, {
                $element,
            });
            elementorFrontend.elementsHandler.addHandler(omeroLoadmorePost, {
                $element,
            });
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-services-grid.default', addHandler);
    });
})(jQuery);


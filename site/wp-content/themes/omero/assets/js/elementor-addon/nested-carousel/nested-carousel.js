(function($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(omeroNestedCarouselHandler, {
                $element,
            });
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-nested-carousel.default', addHandler);
    });
})(jQuery);

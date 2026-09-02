(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(omeroSwiperBase, {
                $element,
            })
        }

        elementorFrontend.hooks.addAction('frontend/element_ready/omero-timeline.default', addHandler);
    })
})(jQuery);

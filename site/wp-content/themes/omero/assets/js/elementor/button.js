(function ($) {
    "use strict";

    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(omeroButtonInit, {
                $element,
            });

            if ($element.hasClass('omero-button-popup-yes')) {
                const $settings = { '$element': $element },
                    setupIns = new elementorModules.frontend.handlers.Base($settings),
                    popup_type = setupIns.getElementSettings('popup_type'),
                    $button = $element.find('a.elementor-button');
                if ($button.length) {
                    switch (popup_type) {
                        case 'default':
                            $button.magnificPopup();
                            break;
                        case 'image':
                            break;
                        default:
                            $button.magnificPopup({
                                type: popup_type
                            });
                            break;
                    }
                }
            }
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-button.default', addHandler);
    });

})(jQuery);
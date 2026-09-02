(function ($) {
    "use strict";

    function hoverProcess($element) {
        var items = $element.find('.omero-inner-process');
        if (items.length) {
            items.on({
                mouseenter: function() {
                    var index = $(this).data('index'),
                        targetImg = $element.find(`.omero-process-image.img-${index}`);

                    if($(this).hasClass('activate')) return;

                    items.removeClass('activate');
                    $(this).addClass('activate');

                    $element.find('.omero-process-image').removeClass('show');
                    targetImg.addClass('show');
                },
                mouseleave: function() {
                    //stuff to do on mouse leave
                }
            });
        }
    }

    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(omeroSwiperBase, {
                $element,
            });

            $element.find('.omero-swiper').on('swiperInit', function(e, slider) {
                var slideSize = slider.slides[0].swiperSlideSize;

                $(this).css('--slider-item-width', slideSize+'px');
            })
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-process.default', addHandler);
    });
})(jQuery);


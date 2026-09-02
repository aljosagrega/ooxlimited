(function ($) {
    "use strict";

    function addLastItem($element) {
        var max = 6;
        if ($element.hasClass('product-gallery-style-grid')) {
            if ($(window).width() <= 1024) {
                max = 4;
            }
            if ($(window).width() <= 567) {
                max = 2;
            }
        }
        if ($element.hasClass('product-gallery-style-mansory')) {
            max = 4;
            if ($(window).width() <= 567) {
                max = 2;
            }
        }

        var lastItem = $('.last-item.product-slideshow-item', $element);
        if (lastItem.length) {
            lastItem.removeClass('last-item');
            lastItem.find('.product-gallery-viewmore').remove();
        }
        if ($('.product-slideshow-item', $element).length > max) {
            const moreText = $('.elementor-widget-inner', $element).data('viewmore');
            lastItem = $(`.product-slideshow-item:nth-child(${max})`, $element),
            lastItem.addClass('last-item');
            $('.product-slideshow-inner', lastItem).append(`<span class="product-gallery-viewmore"><i class="omero-icon-galleries"></i>${moreText}</span>`);
        }
    }

    function initFancyBox($element) {
        $('.product-slideshow-inner', $element).fancybox({
            loop: false,
            clickOutside: "close",
            thumbs: {
                autoStart: true
            }
        });
    }

    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(omeroSwiperBase, {
                $element,
            });

            if ($element.hasClass('product-gallery-style-slideshow')) {
                $('.omero-swiper', $element).on('swiperInit', function(e, slider) {
                    initFancyBox($element);
                })
            } else {
                initFancyBox($element);

                addLastItem($element);
                $(window).on('resize', function() {
                    addLastItem($element);
                })
            }


        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-product-gallery.default', addHandler);
    });
})(jQuery);


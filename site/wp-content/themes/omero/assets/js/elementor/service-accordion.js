(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            var $sliderEffect = $element.find('.omero-service-side-images-swiper'),
                $navItem = $element.find('.service-title-wrapper');

                if($sliderEffect.length){
                    const asyncSwiper = elementorFrontend.utils.swiper;
                    const $slider = new asyncSwiper($sliderEffect, {
                        spaceBetween: 0,
                        slidesPerView: 1,
                        on: {
                            afterInit: function (swiperIns) {
                                $navItem.on('click', function (e) {
                                    e.preventDefault();
                                    let $nav = $(this).closest('.omero-service-item-titles');
                                    let goto = $nav.attr('data-goto');
                                    if ($nav.hasClass('actived')) {
                                        return;
                                    }
                                    
                                    swiperIns.slideToLoop(goto);
                                    
                                    $nav.siblings('.actived').removeClass('actived').find('.service-information-wrapper').slideUp();
                                    $nav.addClass('actived').find('.service-information-wrapper').slideDown();
                                });
                            },
                        }
                    });

                }
        }

        elementorFrontend.hooks.addAction('frontend/element_ready/omero-services-accordion.default', addHandler);
    })
    
})(jQuery);


(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            var $sliderEffect = $element.find('.swiper-image-effect'),
                $navItem = $element.find('.accordion-image-nav-title');

                if($sliderEffect.length){
                    const asyncSwiper = elementorFrontend.utils.swiper;
                    const $slider = new asyncSwiper($sliderEffect, {
                        spaceBetween: 0,
                        slidesPerView: 1,
                        effect: 'cube',
                        grabCursor: true,
                        cubeEffect: {
                            slideShadows: false,
                            shadow: false,
                        },
                        on: {
                            afterInit: function (swiperIns) {
                                $navItem.on('click', function (e) {
                                    e.preventDefault();
                                    let $nav = $(this).parent('.elementor-nav-accordion-image');
                                    let goto = $nav.attr('data-goto');
                                    if ($nav.hasClass('active')) {
                                        return;
                                    }

                                    $element.find('.elementor-nav-accordion-image').removeClass('active');
                                    $nav.addClass('active');
                                    
                                    swiperIns.slideToLoop(goto);
                                    
                                    $element.find('.elementor-accordion-image-item.show').slideUp();
                                    $nav.find('.elementor-accordion-image-item').addClass('show').slideDown();
                                });
                            },
                        }
                    });

                }
        }

        elementorFrontend.hooks.addAction('frontend/element_ready/omero-accordion-image.default', addHandler);
    })
    
})(jQuery);


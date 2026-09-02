(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        class omeroImageSwitcher extends omeroSwiperBase {

            getImageTitleWidth($items) {
                $items.each(function () {
                    let $imageTitle = $(this).find('.image-title');
                    if ($imageTitle.length) {
                        $(this).css({
                            '--image-title-width': $imageTitle.width() + 'px'
                        })
                    }
                })
            }

            getColsNavigation(getDefault = false) {
                const elementSettings = this.getElementSettings(),
                    elementorBreakpoints = elementorFrontend.config.responsive.activeBreakpoints, defaultSlidesToShowMap = {
                        mobile: 1, tablet: 2
                    };

                var slidesToShow = +elementSettings.column_navigation || 4,
                    breakpoints = {};

                if (getDefault) {
                    return slidesToShow;
                }

                let lastBreakpointSlidesToShowValue = slidesToShow;

                Object.keys(elementorBreakpoints).reverse().forEach(breakpointName => {
                    // Tablet has a specific default `column`.
                    const defaultSlidesToShow = defaultSlidesToShowMap[breakpointName] ? defaultSlidesToShowMap[breakpointName] : lastBreakpointSlidesToShowValue;

                    breakpoints[elementorBreakpoints[breakpointName].value] = {
                        slidesPerView: +elementSettings['column_navigation_' + breakpointName] || defaultSlidesToShow,
                        // slidesPerGroup: +elementSettings['column_navigation_' + breakpointName] || 1,
                    };
                    lastBreakpointSlidesToShowValue = +elementSettings['column_navigation_' + breakpointName] || defaultSlidesToShow;
                });

                return breakpoints;
            }

            customSwiperSettings() {
                const eIns = this,
                    elementSettings = this.getElementSettings(),
                    $element = this.$element,
                    $navSlide = $element.find('.swiper-navigation');

                var options = {
                    breakpoints: false,
                    slidesPerView: 1,
                    handleElementorBreakpoints: true,
                    loop: false,
                    on: {
                        init: function (swiperWrapper) {
                            if ($navSlide.length) {
                                const asyncSwiper = elementorFrontend.utils.swiper;
                                new asyncSwiper($navSlide, {
                                    spaceBetween: $element.hasClass('omero-image-switcher-style-2') ? 20 : 0,
                                    slidesPerView: eIns.getColsNavigation(true),
                                    loop: false,
                                    breakpoints: eIns.getColsNavigation(),
                                    handleElementorBreakpoints: true,
                                    navigation: {
                                        prevEl: '.elementor-swiper-button-prev-' + $element.data('id'),
                                        nextEl: '.elementor-swiper-button-next-' + $element.data('id')
                                    },
                                    on: {
                                        afterInit: function (swiper) {
                                            const $el = $(swiper.$el),
                                                $items = $(swiper.slides);

                                            eIns.getImageTitleWidth($items);
                                            $(window).on('resize', function () {
                                                eIns.getImageTitleWidth($items);
                                            })

                                            $el.find(`.image-switcher-title[data-goto="${swiperWrapper.activeIndex}"]:not(.swiper-slide-duplicate)`).addClass('actived');
                                            $el.on('click', '.wrapper-title', function () {
                                                let $cur = $(this).parent('.image-switcher-title'),
                                                    $imageTitle = $cur.find('.image-title'),
                                                    goto = $cur.data('goto');

                                                if ($cur.hasClass('actived')) {
                                                    return false;
                                                }

                                                // $cur.siblings('.actived').removeClass('actived');
                                                // $cur.addClass('actived');
                                                swiperWrapper.slideToLoop(goto);
                                            });
                                        }
                                    }
                                });
                            }

                        },
                        slideChange: function (swiperWrapper) {
                            const realIndex = swiperWrapper.realIndex;

                            $element.find('.image-switcher-title.actived').removeClass('actived');
                            $element.find(`.image-switcher-title[data-goto="${realIndex}"]:not(.swiper-slide-duplicate)`).addClass('actived');
                        }
                    }
                };

                if (elementSettings.gl_effect === 'yes') {
                    var glOptions = {
                        modules: [SwiperGL],
                        effect: 'gl',
                        gl: {
                            'shader': 'random'
                        }
                    };

                    options = Object.assign({}, glOptions, options);
                } else {
                    options.effect = 'fade';
                    options.fadeEffect = {
                        crossFade: true
                    };
                }

                return options;
            }
        }

        const addHandler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(omeroImageSwitcher, {
                $element,
            })
        }

        elementorFrontend.hooks.addAction('frontend/element_ready/omero-image-switcher.default', addHandler);
    })
})(jQuery);

(function ($) {
    "use strict";

    const THUMB_WRAPPER_SELECTOR = '.omero-testimonial-images-side-wrapper';
    const SLIDE_SELECTOR = '.swiper-slide';
    const SLIDE_INDEX_ATTR = 'data-swiper-slide-index';

    function getSlideRealIndex($slide, fallbackIndex) {
        const raw = $slide.attr(SLIDE_INDEX_ATTR);

        if (raw !== undefined && raw !== null && raw !== '') {
            const parsed = parseInt(raw, 10);
            if (!Number.isNaN(parsed)) {
                return parsed;
            }
        }

        return fallbackIndex;
    }

    function isUsableSwiper(swiper) {
        return swiper && !swiper.destroyed;
    }

    $(window).on('elementor/frontend/init', () => {
        class omeroTestimonialCarousel extends omeroSwiperBase {
            customSwiperSettings() {
                const handler = this;

                // The base `getSwiperSettings()` registers `resize` and `progress`
                // handlers on `on`; `Object.assign` in the base's `onInit` shallow
                // merges, so we must re-declare them here to keep them working.
                return {
                    on: {
                        init(mainSwiper) {
                            handler.initThumbsSwiper(mainSwiper);
                        },
                        slideChange(mainSwiper) {
                            handler.syncThumbsToMain(mainSwiper);
                        },
                        resize() {
                            handler.addLastVisibleClass();
                        },
                        progress() {
                            handler.addLastVisibleClass();
                        },
                    },
                };
            }

            async initThumbsSwiper(mainSwiper) {
                const $element = this.$element;
                const $thumbWrapper = $element.find(THUMB_WRAPPER_SELECTOR);

                if (!$thumbWrapper.length) {
                    return;
                }

                const $thumbSlides = $thumbWrapper.find(SLIDE_SELECTOR);
                if ($thumbSlides.length < 2) {
                    this.bindThumbClicks($thumbWrapper, mainSwiper, null);
                    return;
                }

                const isStyle2 = $element.hasClass('omero-testimonial-layout-2');
                const AsyncSwiper = elementorFrontend.utils.swiper;

                const thumbOptions = {
                    slidesPerView: isStyle2 ? 3 : 1,
                    spaceBetween: 0,
                    centeredSlides: true,
                    loop: true,
                    allowTouchMove: false,
                    slideToClickedSlide: false,
                    watchSlidesProgress: true,
                };

                const thumbSwiper = await new AsyncSwiper($thumbWrapper, thumbOptions);
                this.thumbSwiper = thumbSwiper;
                this.mainSwiper = mainSwiper;

                $thumbWrapper.data('swiper', thumbSwiper);

                this.bindThumbClicks($thumbWrapper, mainSwiper, thumbSwiper);
            }

            bindThumbClicks($thumbWrapper, mainSwiper, thumbSwiper) {
                $thumbWrapper.find(SLIDE_SELECTOR).css('cursor', 'pointer');

                // Namespaced delegated handler avoids duplicate bindings if
                // `init` ever fires more than once.
                $thumbWrapper
                    .off('click.omeroTestimonialCarousel')
                    .on('click.omeroTestimonialCarousel', SLIDE_SELECTOR, function (event) {
                        event.preventDefault();

                        const $slide = $(this);
                        const $slides = $thumbWrapper.find(SLIDE_SELECTOR);
                        const realIndex = getSlideRealIndex($slide, $slides.index($slide));

                        if (realIndex < 0 || !isUsableSwiper(mainSwiper)) {
                            return;
                        }

                        const speed = mainSwiper.params.speed;

                        if (mainSwiper.realIndex !== realIndex) {
                            mainSwiper.slideToLoop(realIndex, speed);
                        }

                        if (isUsableSwiper(thumbSwiper) && thumbSwiper.realIndex !== realIndex) {
                            thumbSwiper.slideToLoop(realIndex, speed);
                        }
                    });
            }

            syncThumbsToMain(mainSwiper) {
                const thumbSwiper = this.thumbSwiper;

                if (!isUsableSwiper(thumbSwiper)) {
                    return;
                }

                if (thumbSwiper.realIndex === mainSwiper.realIndex) {
                    return;
                }

                thumbSwiper.slideToLoop(mainSwiper.realIndex, mainSwiper.params.speed);
            }
        }

        const addHandler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(omeroTestimonialCarousel, {
                $element,
            });
        };

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/omero-testimonials-carousel.default',
            addHandler
        );
    });
})(jQuery);
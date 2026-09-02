
class omeroNestedCarouselHandler extends elementorModules.frontend.handlers.SwiperBase {

    getDefaultSettings() {
        return {
            selectors: {
                slider: ".elementor-slides-wrapper",
                slide: ".swiper-slide",
                slideInnerContents: ".swiper-slide-contents",
                activeSlide: ".swiper-slide-active",
                activeDuplicate: ".swiper-slide-duplicate-active"
            },
            classes: {
                animated: "animated",
                kenBurnsActive: "elementor-ken-burns--active",
                slideBackground: "swiper-slide-bg"
            },
            attributes: {
                dataSliderOptions: "slider_options",
                dataAnimation: "animation"
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings("selectors");
        const elements = { $swiperContainer: this.$element.find(selectors.slider) };
        elements.$slides = elements.$swiperContainer.find(selectors.slide);
        return elements;
    }

    getSwiperOptions() {
        const elementSettings = this.getElementSettings();
        const slidesToShow = +elementSettings.slides_to_show || 1;
        const isSingleSlide = 1 === slidesToShow;
        const activeBreakpoints = elementorFrontend.config.responsive.activeBreakpoints;
        const defaultSlidesPerBreakpoint = { mobile: 1, tablet: isSingleSlide ? 1 : 2 };

        const swiperConfig = {
            autoplay: this.getAutoplayConfig(),
            grabCursor: true,
            initialSlide: this.getInitialSlide(),
            slidesPerView: slidesToShow,
            slidesPerGroup: 1,
            loop: "yes" === elementSettings.infinite,
            centeredSlides: "yes" === elementSettings.center_mode,
            speed: elementSettings.transition_speed,
            effect: elementSettings.transition,
            observeParents: true,
            observer: true,
            handleElementorBreakpoints: true,
            on: {
                slideChange: () => {
                    this.handleKenBurns();
                },
                afterInit: (swiper) => {
                    let cl = this;
                    setTimeout(function () {
                        cl.triggerElements(swiper);
                    }, 1000);
                },
                slideChangeTransitionEnd: (swiper) => {
                    this.triggerElements(swiper, true);
                },

            },
            breakpoints: {},
        };

        let currentSlidesToShow = slidesToShow;

        // Configure responsive breakpoints
        Object.keys(activeBreakpoints)
            .reverse()
            .forEach((breakpointName) => {
                const slidesForBreakpoint = defaultSlidesPerBreakpoint[breakpointName] ?
                    defaultSlidesPerBreakpoint[breakpointName] : currentSlidesToShow;

                swiperConfig.breakpoints[activeBreakpoints[breakpointName].value] = {
                    slidesPerView: +elementSettings["slides_to_show_" + breakpointName] || slidesForBreakpoint,
                    slidesPerGroup: +elementSettings["slides_to_scroll_" + breakpointName] || 1
                };

                if (elementSettings.slide_spacing) {
                    swiperConfig.breakpoints[activeBreakpoints[breakpointName].value].spaceBetween =
                        this.getSpaceBetween(breakpointName);
                }

                currentSlidesToShow = +elementSettings["slides_to_show_" + breakpointName] || slidesForBreakpoint;
            });

        if (elementSettings.slide_spacing) {
            swiperConfig.spaceBetween = this.getSpaceBetween();
        }

        const hasArrowNavigation = "arrows" === elementSettings.navigation || "both" === elementSettings.navigation;
        const hasDotNavigation = "dots" === elementSettings.navigation || "both" === elementSettings.navigation;

        // Configure navigation arrows
        if (hasArrowNavigation) {
            if ('yes' === elementSettings.custom_navigation) {
                swiperConfig.navigation = {
                    prevEl: elementSettings.custom_navigation_previous,
                    nextEl: elementSettings.custom_navigation_next,
                };
            } else {
                swiperConfig.navigation = {
                    prevEl: '.elementor-swiper-button-prev-' + this.$element.data('id'),
                    nextEl: '.elementor-swiper-button-next-' + this.$element.data('id')
                };
            }
            // swiperConfig.navigation = {
            //     prevEl: ".elementor-swiper-button-prev",
            //     nextEl: ".elementor-swiper-button-next"
            // };
        }

        if (elementSettings.disable_drag) {
            swiperConfig.allowTouchMove = false;
        }

        // Configure pagination dots
        if (hasDotNavigation && elementSettings.pagination) {
            swiperConfig.pagination = {
                el: ".swiper-pagination",
                type: elementSettings.pagination,
                clickable: true
            };

            if ("dynamic" == elementSettings.pagination) {
                swiperConfig.pagination.dynamicBullets = true;
                delete swiperConfig.pagination.type;
            }
        }

        if (true === swiperConfig.loop) {
            swiperConfig.loopedSlides = this.getSlidesCount();
        }

        // Configure single slide effects
        if (isSingleSlide) {
            if ("fade" === swiperConfig.effect) {
                swiperConfig.fadeEffect = { crossFade: true };
            }
        } else {
            swiperConfig.slidesPerGroup = +elementSettings.slides_to_scroll || 1;
        }

        if ('yes' === elementSettings.mousewheel) {
            swiperConfig.mousewheel = {
                releaseOnEdges: true,
            };
        }

        // Configure special effects
        this.configureSpecialEffects(swiperConfig, elementSettings);

        return swiperConfig;
    }

    configureSpecialEffects(swiperConfig, elementSettings) {
        const effect = swiperConfig.effect;

        if ("coverflow" == effect) {
            swiperConfig.coverflowEffect = {
                rotate: 50, stretch: 0, depth: 100, modifier: 1, slideShadows: true
            };
        } else if ("creative" == effect) {
            swiperConfig.creativeEffect = {
                prev: { shadow: true, translate: [0, 0, -400] },
                next: { translate: ["100%", 0, 0] }
            };
        } else if ("creative2" == effect) {
            swiperConfig.effect = "creative";
            swiperConfig.creativeEffect = {
                perspective: true,
                limitProgress: 2,
                shadowPerProgress: true,
                prev: { shadow: true, translate: ["-10%", 0, -200], rotate: [0, 0, -2] },
                next: { shadow: false, translate: ["120%", 0, 0] }
            };
        } else if ("creative3" == effect) {
            swiperConfig.effect = "creative";
            swiperConfig.creativeEffect = {
                prev: { shadow: true, translate: ["-125%", 0, -800], rotate: [0, 0, -90] },
                next: { shadow: true, translate: ["125%", 0, -800], rotate: [0, 0, 90] }
            };
        } else if ("creative4" == effect) {
            swiperConfig.effect = "creative";
            swiperConfig.creativeEffect = {
                prev: { shadow: true, origin: "left center", translate: ["-5%", 0, -200], rotate: [0, 100, 0] },
                next: { origin: "right center", translate: ["5%", 0, -200], rotate: [0, -100, 0] }
            };
        } else if ("cube" == effect) {
            swiperConfig.cubeEffect = {
                shadow: true, slideShadows: true, shadowOffset: 20, shadowScale: 0.94
            };
        } else if ("coverflow2" == effect) {
            swiperConfig.effect = "coverflow";
            swiperConfig.coverflowEffect = {
                rotate: 0, stretch: 0, depth: 100, modifier: 3, slideShadows: true
            };
        }
    }

    getAutoplayConfig() {
        const elementSettings = this.getElementSettings();
        return "yes" === elementSettings.autoplay && {
            stopOnLastSlide: true,
            delay: elementSettings.autoplay_speed,
            disableOnInteraction: "yes" === elementSettings.pause_on_interaction
        };
    }

    initSingleSlideAnimations() {
        const settings = this.getSettings();
        const animationType = this.elements.$swiperContainer.data(settings.attributes.dataAnimation);

        this.elements.$swiperContainer
            .find("." + settings.classes.slideBackground)
            .addClass(settings.classes.kenBurnsActive);

        if (animationType) {
            this.elements.$swiperContainer
                .find(settings.selectors.slideInnerContents)
                .addClass(settings.classes.animated + " " + animationType);
        }
    }

    async initSlider() {
        const swiperContainer = this.elements.$swiperContainer;

        if (!swiperContainer.length) return;
        if (1 >= this.getSlidesCount()) return;

        const SwiperClass = elementorFrontend.utils.swiper;
        const swiperOptions = this.getSwiperOptions();

        this.swiper = await new SwiperClass(swiperContainer, swiperOptions);
        swiperContainer.data("swiper", this.swiper);
        this.handleKenBurns();

        const elementSettings = this.getElementSettings();

        if ("creative2" == elementSettings.transition) {
            this.elements.$swiperContainer.css("overflow", "visible");
        }

        if (elementSettings.pause_on_hover) {
            this.togglePauseOnHover(true);
        }
    }

    triggerElements(swiper, runTrigger = false) {
        const $ = jQuery;
        const animationKeys = omero_nested_carousel.animation_keys;
        const containerRect = swiper.el.getBoundingClientRect();
        $(swiper.el).find('.swiper-slide').each(function () {
            const rect = this.getBoundingClientRect();
            const isVisible = rect.right > containerRect.left && rect.left < containerRect.right;

            if (!isVisible) {
                $(this).find(".elementor-element.animated").each(function () {
                    let curEl = $(this),
                        foundClass = animationKeys.find(cls => curEl.hasClass(cls));

                    curEl.addClass("elementor-invisible").removeClass("animated");
                    if (foundClass) {
                        curEl.removeClass(foundClass);
                    }
                });
            } else if (runTrigger) {
                $(this).find(".elementor-invisible").each(function () {
                    elementorFrontend.elementsHandler.runReadyTrigger($(this));
                });
            }
        });
    }


    onInit() {
        // Wrap containers in edit mode
        if (elementorFrontend.isEditMode()) {
            this.findElement(".e-con").each(function () {
                jQuery(this).wrap('<div class="swiper-slide"></div>');
            });
        }

        elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);

        if (2 > this.getSlidesCount()) {
            this.initSingleSlideAnimations();
        } else {
            this.initSlider();
        }
    }

    getChangeableProperties() {
        return {
            pause_on_hover: "pauseOnHover",
            pause_on_interaction: "disableOnInteraction",
            autoplay_speed: "delay",
            transition_speed: "speed"
        };
    }

    updateSwiperOption(settingName) {
        if (0 === settingName.indexOf("width")) {
            this.swiper.update();
            return;
        }

        const elementSettings = this.getElementSettings();
        const settingValue = elementSettings[settingName];
        let swiperProperty = this.getChangeableProperties()[settingName];
        let newValue = settingValue;

        switch (settingName) {
            case "autoplay_speed":
                swiperProperty = "autoplay";
                newValue = {
                    stopOnLastSlide: true,
                    delay: settingValue,
                    disableOnInteraction: "yes" === elementSettings.pause_on_interaction
                };
                break;
            case "pause_on_hover":
                this.togglePauseOnHover("yes" === settingValue);
                break;
            case "pause_on_interaction":
                newValue = "yes" === settingValue;
        }

        if ("pause_on_hover" !== settingName) {
            this.swiper.params[swiperProperty] = newValue;
        }

        this.swiper.update();
    }

    onElementChange(settingName) {
        if (0 === settingName.indexOf("slide_spacing")) {
            this.updateSpaceBetween(settingName);
            return;
        }

        if (1 >= this.getSlidesCount()) return;

        const changeableProperties = this.getChangeableProperties();

        if (Object.prototype.hasOwnProperty.call(changeableProperties, settingName)) {
            this.updateSwiperOption(settingName);
            this.swiper.autoplay.start();
        }
    }

    getSpaceBetween(breakpoint = null) {
        return elementorFrontend.utils.controls.getResponsiveControlValue(
            this.getElementSettings(),
            "slide_spacing",
            "size",
            breakpoint
        ) || 0;
    }

    updateSpaceBetween(settingName) {
        const breakpointMatch = settingName.match("slide_spacing_(.*)");
        const breakpointName = breakpointMatch ? breakpointMatch[1] : "desktop";
        const spaceBetweenValue = this.getSpaceBetween(breakpointName);

        if ("desktop" !== breakpointName) {
            this.swiper.params.breakpoints[
                elementorFrontend.config.responsive.activeBreakpoints[breakpointName].value
            ].spaceBetween = spaceBetweenValue;
        }

        this.swiper.params.spaceBetween = spaceBetweenValue;
        this.swiper.update();
    }

    onEditSettingsChange(settingName) {
        if (1 >= this.getSlidesCount()) return;

        if ("activeItemIndex" === settingName) {
            this.swiper.slideToLoop(this.getEditSettings("activeItemIndex") - 1);
            this.swiper.autoplay.stop();
        }
    }
}
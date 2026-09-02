(function ($) {
    "use strict";

    class omeroAjaxFilter extends elementorModules.frontend.handlers.Base {
        getDefaultSettings() {
            return {
                selectors: {
                    filterBtn: '.submit-filter',
                    filterField: '.fitler-field',
                    renderBox: '.elementor-widget-render',
                    paginationLink: '.omero-pagination-list a.page-numbers'
                }
            };
        }
        getDefaultElements() {
            const selectors = this.getSettings('selectors');
            return {
                filterBtn: this.$element.find(selectors.filterBtn),
                filterField: this.$element.find(selectors.filterField),
                renderBox: this.$element.find(selectors.renderBox),
                paginationLink: this.$element.find(selectors.paginationLink)
            };
        }
        onInit() {
            if (elementorFrontend.isEditMode()) {
                return;
            }
            
            this.filterAction();
            this.paginationAction();
        }
        filterAction() {
            var $filterField = this.getDefaultElements().filterField;
            if ($filterField.length) {
                super.onInit();
                var ins = this;

                this.$element.on('click', this.getSettings('selectors').filterBtn, function (e) {
                    e.preventDefault();

                    if ($(this).hasClass('loading')) {
                        return false;
                    }

                    var url = new URL(window.location.href);

                    $filterField.each(function () {
                        var taxonomy = $(this).attr('name'); // game_genre, game_platform
                        var value = $(this).val();

                        if (value) {
                            url.searchParams.set(taxonomy, value);
                        } else {
                            url.searchParams.delete(taxonomy);
                        }
                        // url.searchParams.set('w_id', ins.$element.data('id'));
                    });

                    ins.sendRequest(url, $(this));
                })
            }
        }
        paginationAction() {
            var $paginationLink = this.getDefaultElements().paginationLink;
            if (!$paginationLink.length) {
                return false;
            }

            var ins = this;
            this.$element.on('click', this.getSettings('selectors').paginationLink, function (e) {
                e.preventDefault();

                if ($(this).hasClass('loading')) {
                    return false;
                }

                var url = $(this).attr('href');
                ins.sendRequest(url, $(this));
            })
            
        }
        sendRequest(url, btn) {
            var xhr = false;
            if (xhr) {
                xhr.abort();
            }

            var $filterBtn = btn,
                $renderBox = this.getDefaultElements().renderBox,
                id = this.$element.data('id'),
                selectors = this.getSettings('selectors'),
                renderBoxSelector = selectors.renderBox;

            xhr = $.ajax({
                type: "GET",
                url: url,
                beforeSend: function () {
                    $filterBtn.addClass('loading');
                    $renderBox.addClass('filtering');
                },
                success: function (data) {
                    var $html = $(data),
                        $filteredRenderBox = $html.find(`[data-id="${id}"] ${renderBoxSelector}`);

                    if ($filteredRenderBox.length) {
                        $renderBox.html($filteredRenderBox.html());
                        $(document).trigger('omero-path-reload');
                    } else {
                        console.log('List or Item is not found!');
                    }
                    
                    xhr = false;
                },
                error: function (xhr) { // if error occured
                    alert("Error occured. Please try again!");
                    $filterBtn.removeClass('loading');
                    $renderBox.removeClass('filtering');
                },
                complete: function () {
                    $filterBtn.removeClass('loading');
                    $renderBox.removeClass('filtering');
                },
            });
        }
    }

    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            if  ( $element.hasClass('omero-scroll-sticky-yes') &&
                $element.closest('.elementor-widget-omero-games-list').length) {

                $('body').addClass('omero-scroll-sticky');
                let $items = $element.find('li.omero-item'),
                    $offsetEl = $element.find('.scroll-offset-data');

                let offset = 40;
                if ($offsetEl.length && typeof $offsetEl.data('sticky-offset') !== 'undefined') {
                    offset = parseInt($offsetEl.data('sticky-offset'));
                }

                if ($items.length) {
                    $items.each(function (index) {
                        var index_start = index + 1;
                        $(this).css('--offset', `${index_start * offset}px`);
                    })
                }

                const mm = gsap.matchMedia();
                mm.add("(min-width: 1025px)", () => {

                    $items.each(function (index) {
                        const section = $(this);

                        if (index === $items.length - 1) return;

                        const nextSection = $items.eq(index + 1);

                        gsap.fromTo(
                            section,
                            { opacity: 1 },
                            {
                                opacity: 0,
                                ease: "none",
                                scrollTrigger: {
                                    trigger: section,
                                    start: "top top+=20%",
                                    endTrigger: nextSection,
                                    end: "top 50%",
                                    scrub: 0.2,
                                    markers: false
                                }
                            }
                        );
                    });

                    gsap.config({ force3D: true });

                    return () => {
                        ScrollTrigger.getAll().forEach(t => t.kill());
                    };
                });


            } else {
                elementorFrontend.elementsHandler.addHandler(omeroSwiperBase, {
                    $element,
                });
            }
            elementorFrontend.elementsHandler.addHandler(omeroLoadmorePost, {
                $element,
            });
            elementorFrontend.elementsHandler.addHandler(omeroButtonInit, {
                $element,
            });
            elementorFrontend.elementsHandler.addHandler(omeroAjaxFilter, {
                $element,
            });

            $element.on('omero-posttype-loaded', function() {
                $(document).trigger('omero-path-reload');
            });
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-games-list.default', addHandler);
    });
})(jQuery);


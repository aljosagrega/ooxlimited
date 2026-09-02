(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            if ($element.hasClass('omero-scroll-sticky-yes') &&
                $element.closest('.omero-services-list-style-list-1').length) {
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
                                    end: "top 10%",
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
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-services-list.default', addHandler);
    });
})(jQuery);


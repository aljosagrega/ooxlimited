(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', () => {
        const addHandler = ($element) => {
            $('li.omero-timeline-item-titles', $element).on('click', function(e) {
                let id = $(this).data('id');

                $(this).toggleClass('show');
                $(this).siblings().removeClass('show');

                $('.omero-list-wrapper .omero-item', $element).removeClass('actived');
                $(`.omero-list-wrapper .omero-item.elementor-repeater-item-${id}`, $element).addClass('actived');
            });

            $('li.omero-timeline-item-titles', $element).hover(
                // Mouse enter
                function() {
                    let id = $(this).data('id');
                    $(this).addClass('show');
                    $(this).siblings().removeClass('show');

                    $('.omero-list-wrapper .omero-item', $element).removeClass('actived');
                    $(`.omero-list-wrapper .omero-item.elementor-repeater-item-${id}`, $element).addClass('actived');
                },
                // Mouse leave
                function() {
                    if ($('li.omero-timeline-item-titles.show', $element).length === 0) {
                        let id = $(this).data('id');
                        $(this).removeClass('show');
                        $(`.omero-list-wrapper .omero-item.elementor-repeater-item-${id}`, $element).removeClass('actived');

                        if ($('.omero-list-wrapper .omero-item.actived', $element).length === 0) {
                            $('li.omero-timeline-item-titles:first', $element).addClass('show');
                            $('.omero-list-wrapper .omero-item:first', $element).addClass('actived');
                        }
                    }
                }
            );
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/omero-timelines-accordion.default', addHandler);
    });

})(jQuery);

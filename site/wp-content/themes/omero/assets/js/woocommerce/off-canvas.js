(function ($) {
    'use strict';

    $(function () {
        var $body = $('body');
        $body.on('click', '.filter-toggle', function (e) {
            e.preventDefault();
            $('html').toggleClass('off-canvas-active');
        });

        $body.on('click', '.filter-close, .omero-overlay-filter', function (e) {
            e.preventDefault();
            $('html').toggleClass('off-canvas-active');
        });

        // Dropdown
		var $dropdownWrapper = $('body .omero-dropdown-filter');

		$body.on('click','.filter-toggle-dropdown',function (e) {
			e.preventDefault();
			$dropdownWrapper.toggleClass('active-dropdown').slideToggle();
		});

		function clone_sidebar() {
		    var $canvas = $('.omero-canvas-filter-wrap');
		    if(!$('body').hasClass('shop_filter_canvas')){
                if($(window).width() < 1024){
                    $('#secondary').children().appendTo(".omero-canvas-filter-wrap");
                    $('.omero-dropdown-filter-wrap').children().appendTo(".omero-canvas-filter-wrap");
                }else {
                    $canvas.children().appendTo("#secondary");

                    $canvas.children().appendTo(".omero-dropdown-filter-wrap");
                }
            }

        }
        $(document).ready(function () {
            clone_sidebar();
            $(window).on( 'resize',function () {
                clone_sidebar();
            });
        });
    });


})(jQuery);

(function ($) {
    'use strict';

    console.log('test1');
    $(document).ready(function() {
        console.log('test2');
        
        $(document).on('click', '.team-tab-button', function(e) {
            e.preventDefault();

            console.log('test');
            
            var $button = $(this);
            var tabKey = $button.data('tab');
            var $container = $button.closest('.team-imformations-tab');
            
            $container.find('.team-tab-button').removeClass('active');
            $container.find('.team-tab-pane').removeClass('active');
            
            $button.addClass('active');
            $container.find('.team-tab-pane[data-tab="' + tabKey + '"]').addClass('active');
        });
    });

})(jQuery);

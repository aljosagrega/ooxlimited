(function ($) {
    "use strict";
    $(window).on('elementor/frontend/init', function () {

        elementorFrontend.hooks.addAction('frontend/element_ready/column', function ($scope) {
            if ($scope.hasClass('animated-slide-column')) {
                new Waypoint({
                    element: $scope, offset: '50%', handler: function () {
                        $scope.addClass('col-loaded');
                    }
                });
            }

            if ($scope.hasClass('animated-bg-parallax')) {
                var $wrap = $scope.find('>[class*="elementor-column-"]');
                var linkImage = $wrap.css('backgroundImage').replace('url(', '').replace(')', '').replace(/\"/gi, "");
                if (linkImage === 'none') {
                    return;
                }
                $wrap.prepend('<img src="' + linkImage + '" class="img-banner-parallax" alt="banner-parallax"/>')

                $wrap.find('>.img-banner-parallax').panr({
                    moveTarget: $wrap,
                    sensitivity: 20,
                    scale: false,
                    scaleOnHover: true,
                    scaleTo: 1.1,
                    scaleDuration: .25,
                    panY: true,
                    panX: true,
                    panDuration: 1.25,
                    resetPanOnMouseLeave: true
                });
            }
        });
        elementorFrontend.hooks.addAction('frontend/element_ready/section', function ($scope) {
            $(window).load(function () {
                if ($scope.hasClass('animated-bg-parallax')) {
                    var linkImage = $scope.css('backgroundImage').replace('url(', '').replace(')', '').replace(/\"/gi, "");
                    if (linkImage === 'none') {
                        return;
                    }
                    $scope.prepend('<img src="' + linkImage + '" class="img-banner-parallax" alt="banner-parallax" />')
                    $scope.find('>.img-banner-parallax').panr({
                        moveTarget: $scope,
                        sensitivity: 20,
                        scale: false,
                        scaleOnHover: true,
                        scaleTo: 1.1,
                        scaleDuration: .25,
                        panY: true,
                        panX: true,
                        panDuration: 1.25,
                        resetPanOnMouseLeave: false
                    });
                }


            })
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/button.default', function ($scope) {
            if ($scope.find('.elementor-button-text').length) {
                var $text = $scope.find('.elementor-button-text').text();
                $scope.find('.elementor-button').addClass('btn-slip-effect');
                $scope.find('.elementor-button-text').attr('data-text', $text);
                $scope.find('.elementor-button').attr('data-text', $text);
            }
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/video.default', function ($scope) {
            const $settings = { '$element': $scope },
                $setupIns = new elementorModules.frontend.handlers.Base($settings);

            if ($setupIns.getElementSettings('hover_play') === 'yes' && $setupIns.getElementSettings('autoplay') !== 'yes') {
                elementorFrontend.elementsHandler.getHandler('video.default').then((VideoClass) => {
                    let onMouseOverWidget = function (e) {
                        const videoIns = new VideoClass($settings);
                        videoIns.handleVideo();

                        if ('youtube' === videoIns.getElementSettings('video_type')) {
                            videoIns.apiProvider.onApiReady(apiObject => {
                                if (typeof window.omeroVideoPlay === 'function' && videoIns.youtubePlayer) {
                                    window.omeroVideoPlay($scope, true, videoIns.youtubePlayer);
                                }
                            });
                        } else {
                            if (typeof window.omeroVideoPlay === 'function') {
                                window.omeroVideoPlay($scope, true);
                            }
                        }
                    }
                    
                    $scope.on('mouseenter.elementorMouseOverWidget', onMouseOverWidget);
                });
            } else {
                $scope.off('.elementorMouseOverWidget');
                if (typeof window.omeroDestroyVideoPlay === 'function') {
                    window.omeroDestroyVideoPlay($scope);
                }
            }

        });


        elementorFrontend.hooks.addAction('frontend/element_ready/counter.default', function ($element) {

            elementorFrontend.elementsHandler.addHandler(omeroCounter, {
                $element,
            })

        });
    })

})(jQuery)

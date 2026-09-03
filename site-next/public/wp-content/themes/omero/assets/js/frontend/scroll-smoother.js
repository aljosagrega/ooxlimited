(function ($) {
    'use strict';

    let lenisInstance = null;
    let rafId = null;

    function startLenis() {
        if ($(window).width() <= 992) return;
        if (lenisInstance) return;
        
        lenisInstance = new Lenis({
            duration: 1.5,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            direction: 'vertical',
            gestureDirection: 'vertical',
            smooth: true,
            mouseMultiplier: 1,
            smoothTouch: false,
            touchMultiplier: 2,
            infinite: false,
        });

        function raf(time) {
            lenisInstance.raf(time);
            rafId = requestAnimationFrame(raf);
        }
        rafId = requestAnimationFrame(raf);
        $('html').css('scroll-behavior', 'initial');
    }

    function stopLenis() {
        if (!lenisInstance) return;
        if (rafId) cancelAnimationFrame(rafId);
        lenisInstance.destroy();
        lenisInstance = null;
        $('html').css('scroll-behavior', 'smooth');
    }

    function handleResize() {
        if ($(window).width() <= 992) {
            stopLenis();
        } else {
            startLenis();
        }
    }

    // Expose global Lenis Actions
    window.startLenis = startLenis;
    window.stopLenis = stopLenis;

    // Init on load
    handleResize();

    $(window).on('resize', function () {
        handleResize();
    });

})(jQuery);

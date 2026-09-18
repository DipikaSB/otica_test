require(['jquery'], function ($) {
    'use strict';
    $(function () {
        var intervalMs = 3000; /* change autoplay speed here (milliseconds) */
        var timer = null;
        function startAutoplay() {
            stopAutoplay();
            timer = setInterval(function () {
                /* 1) Fotorama API (most Magento setups) */
                var $f = $('.fotorama');
                if ($f.length && $f.data('fotorama')) {
                    try {
                        var api = $f.data('fotorama');
                        api.show((api.activeIndex + 1) % api.size);
                        return;
                    } catch (e) { /* fallthrough */ }
                }
            }, intervalMs);
        }
        function stopAutoplay() {
            if (timer) { clearInterval(timer); timer = null; }
        }
        /* Start only when product gallery exists on page */
        if ($('.product.media, .gallery, .fotorama, .owl-carousel').length) {
            startAutoplay();
            /* Pause on hover (optional) */
            $('.product.media, .gallery, .fotorama').on('mouseenter', stopAutoplay).on('mouseleave', startAutoplay);
        }
    });
});
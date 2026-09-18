require(['jquery', 'domReady!', 'waypoints'], function ($) {
    $('.ox-animate:not(.animated)').each(function () {
        var element = this;
        new Waypoint({
            element: element,
            handler: function () {
                var $this = $(element),
                    delay = $this.data('animdelay') || 1;

                if ($this.hasClass('animated')) {
                    return;
                }

                setTimeout(function () {
                    $this.addClass('animated');
                }, delay);
            },
            offset: '80%'
        });
    });
});
/**
 * Portfolio Showcase: On Item Click = "Whole item opens the lightbox".
 *
 * The item's full-size link and title (marked with data-wpz-ps-lightbox)
 * open the item's lightbox instead of the post. Ctrl/Cmd/middle click still
 * opens the post. The lightbox itself is set up by the theme.
 */
(function ($) {
    'use strict';

    $(document).on('click', '.portfolio-showcase [data-wpz-ps-lightbox]', function (e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.which > 1) {
            return;
        }

        var $trigger = $(this).closest('article')
            .find('.portfolio-popup-video')
            .not('.portfolio-popup-video--post')
            .first();

        if (!$trigger.length) {
            return;
        }

        e.preventDefault();
        $trigger.trigger('click');
    });
})(jQuery);

(function($) {
    function dpessrInitTabs() {
        $('.dpessr-tabs .dpessr-tab').on('click', function(e) {
            e.preventDefault();

            if( $(this).hasClass('active') ) { return; }

            var target = $(this).attr('href');

            if( !$(target).length ) { return;}

            // Activate the clicked tab
            $('.dpessr-tabs .dpessr-tab').removeClass('active');
            $(this).addClass('active');

            // Show the corresponding tab content
            $('.dpessr-tab-panel').removeClass('active').hide();
            $(target).show().addClass('active');
        });
    }

    function dpessrInitReviewNotice() {
        // Clicking "rate it" opens WordPress.org in a new tab (native <a target="_blank">)
        // and silently marks the notice as dismissed on our end in the background.
        $(document).on('click', '.dpessr-rate-link', function() {
            var markUrl = $(this).data('mark-url');
            if (markUrl) {
                $.get(markUrl);
            }
        });
    }

    $(document).ready(function() {
        dpessrInitTabs();
        dpessrInitReviewNotice();
    });
})(jQuery);
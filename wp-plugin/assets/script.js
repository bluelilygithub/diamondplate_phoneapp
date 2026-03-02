(function ($) {
    'use strict';

    $(document).on('click', '.dpct-row', function () {
        var $row      = $(this);
        var id        = $row.data('id');
        var $detail   = $('#dpct-detail-' + id);
        var $content  = $detail.find('.dpct-detail-content');
        var isOpen    = $detail.is(':visible');

        // Close all other open rows
        $('.dpct-detail-row').hide();
        $('.dpct-row').removeClass('dpct-row--active');

        if (isOpen) {
            // Toggle closed
            return;
        }

        // Open this row
        $row.addClass('dpct-row--active');
        $detail.show();

        // Load content if not already loaded
        if ($content.data('loaded') === 'true') {
            return;
        }

        $content.html('<div class="dpct-loading">Loading call details...</div>');

        $.post(dpctAjax.url, {
            action: 'dpct_get_call',
            nonce:  dpctAjax.nonce,
            id:     id
        }, function (response) {
            if (!response.success) {
                $content.html('<p style="color:#dc2626;">Failed to load call details.</p>');
                return;
            }

            var call      = response.data;
            var transcript = call.transcript || '<em>Transcript not yet available.</em>';
            var summary    = call.summary    || '<em>Summary not yet available.</em>';
            var sentiment  = call.sentiment  || 'unknown';
            var badgeClass = 'dpct-badge dpct-badge--' + sentiment;

            var html = '<div class="dpct-detail-grid">'
                + '<div class="dpct-detail-block">'
                +   '<h4>Summary</h4>'
                +   '<p>' + escHtml(summary) + '</p>'
                + '</div>'
                + '<div class="dpct-detail-block">'
                +   '<h4>Sentiment</h4>'
                +   '<span class="' + escHtml(badgeClass) + '">' + escHtml(ucFirst(sentiment)) + '</span>'
                + '</div>'
                + '</div>'
                + '<div class="dpct-detail-block" style="margin-top:16px;">'
                +   '<h4>Transcript</h4>'
                +   '<pre class="dpct-transcript">' + escHtml(transcript) + '</pre>'
                + '</div>';

            $content.html(html);
            $content.data('loaded', 'true');
        }).fail(function () {
            $content.html('<p style="color:#dc2626;">Network error loading call details.</p>');
        });
    });

    function escHtml(str) {
        return $('<div>').text(str).html();
    }

    function ucFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

})(jQuery);

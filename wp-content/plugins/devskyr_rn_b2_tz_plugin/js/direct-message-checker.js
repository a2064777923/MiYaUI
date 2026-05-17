jQuery(document).ready(function($) {

    $.ajax({
        type: 'POST',
        url: dmChecker.ajax_url,
        data: {
            action: 'check_direct_messages',
            user_id: dmChecker.user_id
        },
        success: function(response) {
            if (response.success && response.data.has_unread) {

                $('#message-modal').show();
            }
        }
    });

    $('.close').click(function() {
        $('#message-modal').hide();
    });

    $(window).click(function(event) {
        if ($(event.target).is("#message-modal")) {
            $('#message-modal').hide();
        }
    });
});
jQuery(document).ready(function($) {
    $('#user-info-form').on('submit', function(e) {
        e.preventDefault();
        var username = $('#username').val();
        var email = $('#email').val();

        $.ajax({
            url: mypageAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'update_user_info',
                username: username,
                email: email,
                security: mypageAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#message').removeClass('error').addClass('success').text(response.data).show();
                } else {
                    $('#message').removeClass('success').addClass('error').text(response.data).show();
                }
            },
            error: function() {
                $('#message').removeClass('success').addClass('error').text('エラーが発生しました。').show();
            }
        });
    });
});
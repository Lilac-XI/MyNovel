jQuery(document).ready(function($) {
    $('#favorite-button').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var novelId = $button.data('novel-id');
        var action = $button.hasClass('add-favorite') ? 'add_favorite_novel' : 'remove_favorite_novel';

        $.ajax({
            url: favoriteAjax.ajax_url,
            type: 'POST',
            data: {
                action: action,
                novel_id: novelId,
                nonce: favoriteAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (action === 'add_favorite_novel') {
                        $button.removeClass('add-favorite').addClass('remove-favorite').text('お気に入りから削除');
                    } else {
                        $button.removeClass('remove-favorite').addClass('add-favorite').text('お気に入りに追加');
                    }
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('エラーが発生しました。');
            }
        });
    });
});
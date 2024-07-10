jQuery(document).ready(function($) {
    $(document).on('click', '#favorite-button', function(e) {
        e.preventDefault();
        var $button = $(this);
        var novelId = $button.data('novel-id');
        var action = $button.hasClass('active') ? 'remove_favorite_novel' : 'add_favorite_novel';

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
                    $button.toggleClass('active');
                    if (action === 'add_favorite_novel') {
                        $button.attr('aria-label', 'お気に入りから削除');
                    } else {
                        $button.attr('aria-label', 'お気に入りに追加');
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
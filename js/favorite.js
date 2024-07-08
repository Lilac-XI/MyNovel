jQuery(document).ready(function($) {
    $(document).on('click', '.favorite-star', function(e) {
        e.preventDefault();
        var $star = $(this);
        var novelId = $star.data('novel-id');
        var action = $star.hasClass('empty') ? 'add_favorite_novel' : 'remove_favorite_novel';

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
                        $star.removeClass('empty').addClass('filled').html('★').attr('title', 'お気に入りから削除');
                    } else {
                        $star.removeClass('filled').addClass('empty').html('☆').attr('title', 'お気に入りに追加');
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
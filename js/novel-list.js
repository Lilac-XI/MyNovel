jQuery(document).ready(function($) {
    var $searchInput = $('#novel-search-input');
    var $searchButton = $('#novel-search-button');
    var $tagSearchButton = $('#tag-search-button');
    var $sortSelect = $('#novel-sort-select');
    var $limitedEpisodesFilter = $('#limited-episodes-filter');
    var $novelList = $('#novel-list');

    function performSearch(searchType) {
        var query = $searchInput.val();
        var sort = $sortSelect.val();
        var limitedEpisodes = $limitedEpisodesFilter.is(':checked');
        var selectedTags = [];

        if (searchType === 'tag') {
            $('.tag-cloud .novel-tag.active').each(function() {
                selectedTags.push($(this).data('tag-id'));
            });
        }

        $.ajax({
            url: novelListAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'search_novel_parents',
                query: query,
                sort: sort,
                limited_episodes: limitedEpisodes,
                tags: selectedTags,
                search_type: searchType
            },
            success: function(response) {
                if (response.success) {
                    $novelList.html(response.data);
                } else {
                    console.error('Error in search:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }

    $searchButton.on('click', function() {
        performSearch('text');
    });
    
    $tagSearchButton.on('click', function() {
        performSearch('tag');
    });
    
    $searchInput.on('keypress', function(e) {
        if (e.which == 13) {
            performSearch('text');
            return false;
        }
    });

    $sortSelect.on('change', function() {
        performSearch($('.search-tab.active').data('tab') === 'text-search' ? 'text' : 'tag');
    });

    // タブ切り替え
    $('.search-tab').on('click', function() {
        $('.search-tab').removeClass('active');
        $(this).addClass('active');
        $('.search-panel').removeClass('active');
        $('#' + $(this).data('tab')).addClass('active');
    });

    // タググループのアコーディオン機能
    $(document).on('click', '.tag-group-name', function() {
        var $tagGroup = $(this).parent('.tag-group');
        var $tagCloud = $tagGroup.find('.tag-cloud');
        var $icon = $(this).find('.toggle-icon');
        
        $tagCloud.slideToggle(300, function() {
            if ($tagCloud.is(':visible')) {
                $icon.text('−');
            } else {
                $icon.text('+');
            }
        });
    });

    // タグの選択状態を切り替える
    $(document).on('click', '.novel-tag', function() {
        $(this).toggleClass('active');
    });

    // ページ読み込み時に全てのタググループを開く
    $('.tag-cloud').show();
    $('.toggle-icon').text('−');

    // タッチデバイスでのホバー効果を制御
    function removeHoverCssRule() {
        if ('ontouchstart' in document.documentElement) {
            var styleSheets = document.styleSheets;
            for (var i = 0; i < styleSheets.length; i++) {
                var styleSheet = styleSheets[i];
                try {
                    var cssRules = styleSheet.cssRules || styleSheet.rules;
                    for (var j = 0; j < cssRules.length; j++) {
                        var rule = cssRules[j];
                        if (rule.selectorText && rule.selectorText.match(/:hover/gi)) {
                            styleSheet.deleteRule(j);
                            j--;
                        }
                    }
                } catch (e) {
                    console.log("Can't read the css rules of: " + styleSheet.href, e);
                }
            }
        }
    }

    // ページ読み込み時に実行
    removeHoverCssRule();

    // Ajaxリクエスト完了後にも実行（動的に追加された要素にも適用するため）
    $(document).ajaxComplete(function() {
        removeHoverCssRule();
    });
});
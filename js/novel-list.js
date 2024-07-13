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

    $limitedEpisodesFilter.on('change', function() {
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
        
        $tagCloud.slideToggle(300);
        $tagGroup.toggleClass('active');
        
        var $icon = $(this).find('.toggle-icon');
        $icon.text($tagGroup.hasClass('active') ? '−' : '+');
    });

    // タグ選択時の処理
    $(document).on('click', '.novel-tag', function() {
        $(this).toggleClass('active');
    });
});
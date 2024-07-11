jQuery(document).ready(function($) {
    var $searchInput = $('#novel-search-input');
    var $searchButton = $('#novel-search-button');
    var $sortSelect = $('#novel-sort-select');
    var $limitedEpisodesFilter = $('#limited-episodes-filter');
    var $novelList = $('#novel-list');

    function performSearch() {
        var query = $searchInput.val();
        var sort = $sortSelect.val();
        var limitedEpisodes = $limitedEpisodesFilter.is(':checked');

        $.ajax({
            url: novelListAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'search_novel_parents',
                query: query,
                sort: sort,
                limited_episodes: limitedEpisodes
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

    $searchButton.on('click', performSearch);
    
    $searchInput.on('keypress', function(e) {
        if (e.which == 13) {
            performSearch();
            return false;
        }
    });
});
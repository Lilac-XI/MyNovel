<?php
/*
Template Name: Novel Parents Page
*/
get_header(); ?>

<div class="novel-wrapper">
    <h1 class="novel-title">小説一覧</h1>
    <div class="novel-search">
        <input type="text" id="novel-search-input" placeholder="小説親を検索">
        <button id="novel-search-button">検索</button>
    </div>
    <div class="novel-content">
        <?php
        $args = array(
            'post_type' => 'novel_parent',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        $novel_parents = new WP_Query($args);

        if ($novel_parents->have_posts()) :
            echo '<ul class="novel-child-list">';
            while ($novel_parents->have_posts()) : $novel_parents->the_post();
                echo '<li>';
                echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
                $content = get_the_content();
                $content = strip_tags($content); // HTMLタグを除去
                $content = mb_substr($content, 0, 100); // 最初の100文字を取得
                if (mb_strlen($content) >= 100) {
                    $content .= '...'; // 100文字以上の場合は末尾に「...」を追加
                }
                echo '<p class="novel-excerpt">' . $content . '</p>';
            endwhile;
            echo '</ul>';
            wp_reset_postdata();
        else :
            echo '<p>小説がありません。</p>';
        endif;
        ?>
    </div>
</div>
<script>
document.getElementById('novel-search-button').addEventListener('click', function() {
    var searchValue = document.getElementById('novel-search-input').value;
    var data = {
        'action': 'search_novel_parents',
        'query': searchValue
    };

    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
        document.querySelector('.novel-child-list').innerHTML = response;
    }).fail(function() {
        alert('検索に失敗しました。');
    });
});
</script>
<?php get_footer(); ?>
<?php
/*
Template Name: お気に入り小説
*/

get_header();

if (is_user_logged_in()) :
    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'favorite_novels', true);
    if (!is_array($favorites)) {
        $favorites = array();
    }
?>

<div class="novel-wrapper">
    <h1 class="novel-title">お気に入り小説</h1>
    <div class="novel-search-sort">
        <div class="novel-search">
            <input type="text" id="favorite-search" placeholder="お気に入り小説を検索">
            <button type="button" id="favorite-search-button">検索</button>
        </div>
        <div class="novel-sort">
            <select id="favorite-sort">
                <option value="newest">新着順</option>
                <option value="oldest">古い順</option>
                <option value="title">タイトル順</option>
                <option value="popular">人気順</option>
                <option value="fav_newest">お気に入り登録日（新→旧）</option>
                <option value="fav_oldest">お気に入り登録日（旧→新）</option>
            </select>
        </div>
    </div>
    <ul class="novel-list" id="favorite-novel-list">
        <?php
        if (!empty($favorites)) :
            $args = array(
                'post_type' => 'novel_parent',
                'post__in' => $favorites,
                'posts_per_page' => -1,
            );
            $favorite_novels = new WP_Query($args);

            if ($favorite_novels->have_posts()) :
                while ($favorite_novels->have_posts()) : $favorite_novels->the_post();
                    $fav_date = get_user_meta($user_id, 'favorite_date_' . get_the_ID(), true);
        ?>
                    <li class="novel-item">
                        <h3 class="novel-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="novel-item-description"><?php echo wp_trim_words(get_the_excerpt(), 30); ?></p>
                        <div class="novel-item-info">
                            <span>最新話: <?php echo get_latest_episode(get_the_ID()); ?></span>
                            <span>お気に入り登録日: <?php echo $fav_date ? date('Y/m/d', strtotime($fav_date)) : '不明'; ?></span>
                        </div>
                        <?php echo do_shortcode('[favorite_button novel_id="' . get_the_ID() . '"]'); ?>
                    </li>
        <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>お気に入りの小説がありません。</p>';
            endif;
        else :
            echo '<p>お気に入りの小説がありません。</p>';
        endif;
        ?>
    </ul>
</div>

<?php
else :
    echo '<p>お気に入り小説を表示するにはログインが必要です。</p>';
endif;

get_footer();
?>
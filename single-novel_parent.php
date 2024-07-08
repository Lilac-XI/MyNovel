<?php get_header(); ?>

<div class="novel-wrapper novel-parent">
    <div class="novel-header-container">
        <h1 class="novel-title"><?php the_title(); ?></h1>
        <div class="favorite-button-container">
            <?php
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $novel_id = get_the_ID();
                $favorites = get_user_meta($user_id, 'favorite_novels', true);
                $is_favorite = is_array($favorites) && in_array($novel_id, $favorites);

                if ($is_favorite) {
                    echo '<button id="favorite-button" class="remove-favorite" data-novel-id="' . $novel_id . '">お気に入りから削除</button>';
                } else {
                    echo '<button id="favorite-button" class="add-favorite" data-novel-id="' . $novel_id . '">お気に入りに追加</button>';
                }
            } else {
                echo '<a href="' . wp_login_url(get_permalink()) . '" class="login-to-favorite">ログインしてお気に入りに追加</a>';
            }
            ?>
        </div>
    </div>
    
    <div class="novel-description">
        <?php the_content(); ?>
    </div>
</div>

<?php get_footer(); ?>
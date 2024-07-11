<?php get_header(); ?>

<div class="novel-wrapper novel-parent">
    <div class="favorite-button-container">
        <?php
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $novel_id = get_the_ID();
            $favorites = get_user_meta($user_id, 'favorite_novels', true);
            $is_favorite = is_array($favorites) && in_array($novel_id, $favorites);

            $button_class = $is_favorite ? 'favorite-button active' : 'favorite-button';
            $button_aria_label = $is_favorite ? 'お気に入りから削除' : 'お気に入りに追加';
            ?>
            <button id="favorite-button" class="<?php echo $button_class; ?>" data-novel-id="<?php echo $novel_id; ?>" aria-label="<?php echo $button_aria_label; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </button>
        <?php
        }
        ?>
    </div>
    
    <div class="novel-header-container">
        <h1 class="novel-title"><?php the_title(); ?></h1>
    </div>
    
    <div class="novel-description">
        <?php the_content(); ?>
    </div>
</div>

<?php get_footer(); ?>
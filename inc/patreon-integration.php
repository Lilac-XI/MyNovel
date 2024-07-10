<?php
// 小説親のPatreon制限を小説子に同期する関数
function sync_patreon_restriction_to_children($post_id) {
    if (get_post_type($post_id) !== 'novel_parent') {
        return;
    }

    $patreon_level = get_post_meta($post_id, 'patreon-level', true);

    $child_novels = get_posts(array(
        'post_type' => 'novel_child',
        'meta_query' => array(
            array(
                'key' => 'parent_novel',
                'value' => $post_id,
            ),
        ),
        'posts_per_page' => -1,
    ));

    foreach ($child_novels as $child) {
        update_post_meta($child->ID, 'patreon-level', $patreon_level);
    }
}
add_action('save_post', 'sync_patreon_restriction_to_children');

// お気に入りボタンのショートコード
function favorite_button_shortcode($atts) {
    $atts = shortcode_atts(array(
        'novel_id' => get_the_ID(),
    ), $atts, 'favorite_button');

    $novel_id = intval($atts['novel_id']);
    $user_id = get_current_user_id();

    if (!$user_id) {
        return '<a href="' . wp_login_url(get_permalink($novel_id)) . '" class="favorite-star" title="ログインしてお気に入りに追加">☆</a>';
    }

    $favorites = get_user_meta($user_id, 'favorite_novels', true);
    if (!is_array($favorites)) {
        $favorites = array();
    }

    if (in_array($novel_id, $favorites)) {
        $star = '<span class="favorite-star filled" data-novel-id="' . $novel_id . '" title="お気に入りから削除">★</span>';
    } else {
        $star = '<span class="favorite-star empty" data-novel-id="' . $novel_id . '" title="お気に入りに追加">☆</span>';
    }

    return $star;
}
add_shortcode('favorite_button', 'favorite_button_shortcode');
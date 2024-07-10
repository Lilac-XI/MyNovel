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
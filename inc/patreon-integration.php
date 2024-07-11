<?php
// 小説親のPatreon制限を小説子に同期する関数
function sync_patreon_restriction_to_children($post_id) {
    // 投稿タイプが小説親であることを確認
    if (get_post_type($post_id) !== 'novel_parent') {
        return;
    }

    // チェックボックスがチェックされているか確認
    if (!isset($_POST['sync_patreon_to_children']) || $_POST['sync_patreon_to_children'] != '1') {
        return;
    }

    // Patreonの制限値を取得
    $patreon_level = get_post_meta($post_id, 'patreon-level', true);

    // 関連する小説子を取得
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

    // 各小説子に制限を適用
    foreach ($child_novels as $child) {
        update_post_meta($child->ID, 'patreon-level', $patreon_level);
    }
}
add_action('save_post', 'sync_patreon_restriction_to_children');

function update_has_locked_parent_on_patreon_level_change($post_id, $post, $update) {
    // 更新時のみ処理を行う
    if (!$update) {
        return;
    }

    // novel_parent の投稿タイプでのみ処理を行う
    if ($post->post_type !== 'novel_parent') {
        return;
    }

    // 自動保存時は処理しない
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // ユーザーが適切な権限を持っているか確認
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // patreon-level の値を取得
    $patreon_level = get_post_meta($post_id, 'patreon-level', true);

    // patreon-level の値に基づいて has_locked_parent を更新
    if ($patreon_level && $patreon_level != '0') {
        update_post_meta($post_id, 'has_locked_parent', 'true');
    } else {
        update_post_meta($post_id, 'has_locked_parent', 'false');
    }
}
add_action('save_post', 'update_has_locked_parent_on_patreon_level_change', 10, 3);

function bulk_update_has_locked_parent() {
    $args = array(
        'post_type' => 'novel_parent',
        'posts_per_page' => -1,
    );

    $novel_parents = get_posts($args);

    foreach ($novel_parents as $novel_parent) {
        $patreon_level = get_post_meta($novel_parent->ID, 'patreon-level', true);
        
        if ($patreon_level && $patreon_level != '0') {
            update_post_meta($novel_parent->ID, 'has_locked_parent', 'true');
        } else {
            update_post_meta($novel_parent->ID, 'has_locked_parent', 'false');
        }
    }

    echo "All novel parents have been updated.";
    exit;
}
// この関数を実行するためのURLを作成する場合はコメントアウトを外します
// add_action('admin_init', 'bulk_update_has_locked_parent');

function update_has_locked_child_on_novel_change($post_id, $post, $update) {
    // novel_child または novel_parent の投稿タイプでのみ処理を行う
    if ($post->post_type !== 'novel_child' && $post->post_type !== 'novel_parent') {
        return;
    }

    // 自動保存時は処理しない
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // ユーザーが適切な権限を持っているか確認
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 親小説のIDを取得
    $parent_novel_id = ($post->post_type === 'novel_parent') ? $post_id : get_post_meta($post_id, 'parent_novel', true);
    if (!$parent_novel_id) {
        return;
    }

    // 親小説に紐づくすべての小説子を取得
    $args = array(
        'post_type' => 'novel_child',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'parent_novel',
                'value' => $parent_novel_id,
            ),
        ),
    );
    $novel_children = get_posts($args);

    $has_locked_child = false;

    // 各小説子の patreon_level をチェック
    foreach ($novel_children as $child) {
        $patreon_level = get_post_meta($child->ID, 'patreon-level', true);
        if ($patreon_level && $patreon_level != '0') {
            $has_locked_child = true;
            break;
        }
    }

    // 親小説の has_locked_child を更新
    update_post_meta($parent_novel_id, 'has_locked_child', $has_locked_child ? 'true' : 'false');
}
add_action('save_post', 'update_has_locked_child_on_novel_change', 10, 3);

function bulk_update_has_locked_child() {
    $args = array(
        'post_type' => 'novel_parent',
        'posts_per_page' => -1,
    );

    $novel_parents = get_posts($args);

    foreach ($novel_parents as $novel_parent) {
        $child_args = array(
            'post_type' => 'novel_child',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'parent_novel',
                    'value' => $novel_parent->ID,
                ),
            ),
        );
        $novel_children = get_posts($child_args);

        $has_locked_child = false;

        foreach ($novel_children as $child) {
            $patreon_level = get_post_meta($child->ID, 'patreon-level', true);
            if ($patreon_level && $patreon_level != '0') {
                $has_locked_child = true;
                break;
            }
        }

        update_post_meta($novel_parent->ID, 'has_locked_child', $has_locked_child ? 'true' : 'false');
    }

    echo "All novel parents' has_locked_child status have been updated.";
    exit;
}
// この関数を実行するためのURLを作成する場合は以下のようにします
// add_action('admin_init', 'bulk_update_has_locked_child');
<?php
// 小説子の順序を保存する関数
function save_novel_children_order() {
    check_ajax_referer( 'save_novel_children_order_nonce', 'security' );

    $order = explode(',', $_POST['order']);
    foreach ( $order as $menu_order => $post_id ) {
        wp_update_post( array(
            'ID' => intval( str_replace('post-', '', $post_id) ),
            'menu_order' => $menu_order
        ));
    }
    wp_send_json_success();
}
add_action( 'wp_ajax_save_novel_children_order', 'save_novel_children_order' );

// ユーザー情報更新用のAjax処理
function update_user_info() {
    check_ajax_referer('update_user_info_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error('ログインが必要です。');
        return;
    }

    $user_id = get_current_user_id();
    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);

    $result = wp_update_user([
        'ID' => $user_id,
        'user_login' => $username,
        'user_email' => $email
    ]);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success('ユーザー情報が更新されました。');
    }
}
add_action('wp_ajax_update_user_info', 'update_user_info');

// お気に入り機能の追加
function add_favorite_novel() {
    if (!is_user_logged_in()) {
        wp_send_json_error('ログインが必要です。');
        return;
    }

    $user_id = get_current_user_id();
    $novel_id = intval($_POST['novel_id']);

    $favorites = get_user_meta($user_id, 'favorite_novels', true);
    if (!is_array($favorites)) {
        $favorites = array();
    }

    if (!in_array($novel_id, $favorites)) {
        $favorites[] = $novel_id;
        update_user_meta($user_id, 'favorite_novels', $favorites);
        wp_send_json_success('お気に入りに追加しました。');
    } else {
        wp_send_json_error('すでにお気に入りに追加されています。');
    }
}
add_action('wp_ajax_add_favorite_novel', 'add_favorite_novel');

// お気に入りから削除する機能
function remove_favorite_novel() {
    if (!is_user_logged_in()) {
        wp_send_json_error('ログインが必要です。');
        return;
    }

    $user_id = get_current_user_id();
    $novel_id = intval($_POST['novel_id']);

    $favorites = get_user_meta($user_id, 'favorite_novels', true);
    if (!is_array($favorites)) {
        $favorites = array();
    }

    $key = array_search($novel_id, $favorites);
    if ($key !== false) {
        unset($favorites[$key]);
        update_user_meta($user_id, 'favorite_novels', array_values($favorites));
        wp_send_json_success('お気に入りから削除しました。');
    } else {
        wp_send_json_error('お気に入りに追加されていません。');
    }
}
add_action('wp_ajax_remove_favorite_novel', 'remove_favorite_novel');

function search_novel_parents() {
    $query = sanitize_text_field($_POST['query']);
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'newest';
    
    $args = array(
        'post_type' => 'novel_parent',
        's' => $query,
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );

    switch ($sort) {
        case 'oldest':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 'title':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'popular':
            $args['meta_key'] = 'novel_views';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        default: // newest
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
    }

    $novel_parents = new WP_Query($args);
    ob_start();
    if ($novel_parents->have_posts()) :
        while ($novel_parents->have_posts()) : $novel_parents->the_post();
            ?>
            <li class="novel-item">
                <h3 class="novel-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="novel-item-description"><?php echo wp_trim_words(get_the_excerpt(), 100, "..."); ?></p>
                <div class="novel-item-info">
                    <?php
                    $latest_episode = get_latest_episode(get_the_ID());
                    echo '<span>最新話: ' . $latest_episode . '</span>';
                    ?>
                </div>
            </li>
            <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<li>該当する小説が見つかりません。</li>';
    endif;
    $output = ob_get_clean();
    wp_send_json_success($output);
}
add_action('wp_ajax_search_novel_parents', 'search_novel_parents');
add_action('wp_ajax_nopriv_search_novel_parents', 'search_novel_parents');
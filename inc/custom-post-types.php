<?php
// カスタム投稿タイプを作成する関数
function create_novel_post_types() {
    // 小説親のカスタム投稿タイプ
    register_post_type( 'novel_parent',
        array(
            'labels' => array(
                'name' => __( 'Novel Parents' ),
                'singular_name' => __( 'Novel Parent' )
            ),
            'public' => true,
            'has_archive' => true,
            'hierarchical' => true,
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
        )
    );

    // 小説子のカスタム投稿タイプ
    register_post_type( 'novel_child',
        array(
            'labels' => array(
                'name' => __( 'Novel Children' ),
                'singular_name' => __( 'Novel Child' )
            ),
            'public' => true,
            'has_archive' => true,
            'hierarchical' => true,
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'page-attributes' ),
        )
    );
}
add_action( 'init', 'create_novel_post_types' );

// カスタム投稿タイプのスラッグを変更
function change_novel_post_type_slugs($args, $post_type) {
    if ('novel_parent' === $post_type) {
        $args['rewrite']['slug'] = '';
    }
    if ('novel_child' === $post_type) {
        $args['rewrite']['slug'] = '';
    }
    return $args;
}
add_filter('register_post_type_args', 'change_novel_post_type_slugs', 10, 2);

// カスタムリライトルールを追加
function add_novel_rewrite_rules() {
    add_rewrite_rule(
        '^novel/([0-9]+)/?$',
        'index.php?post_type=novel_parent&p=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^novel/([0-9]+)/([0-9]+)/?$',
        'index.php?post_type=novel_child&p=$matches[2]',
        'top'
    );
}
add_action('init', 'add_novel_rewrite_rules', 10, 0);

// パーマリンクを変更
function custom_novel_permalink($permalink, $post, $leavename) {
    if ('novel_parent' === get_post_type($post)) {
        return home_url('novel/' . $post->ID);
    }
    if ('novel_child' === get_post_type($post)) {
        $parent_id = get_post_meta($post->ID, 'parent_novel', true);
        if ($parent_id) {
            return home_url('novel/' . $parent_id . '/' . $post->ID);
        }
    }
    return $permalink;
}
add_filter('post_type_link', 'custom_novel_permalink', 10, 3);
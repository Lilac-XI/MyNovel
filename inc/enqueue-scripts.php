<?php
// スタイルとスクリプトをキューに追加する関数
function novel_theme_scripts() {
    wp_enqueue_style( 'style', get_stylesheet_uri() );
    wp_enqueue_script( 'dark-mode', get_template_directory_uri() . '/js/dark-mode.js', array(), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'novel_theme_scripts' );

// function enqueue_custom_styles() {
//     wp_enqueue_style('base-style', get_template_directory_uri() . '/css/base.css');
//     wp_enqueue_style('layout-style', get_template_directory_uri() . '/css/layout.css');
//     wp_enqueue_style('components-style', get_template_directory_uri() . '/css/components.css');
//     wp_enqueue_style('dark-mode-style', get_template_directory_uri() . '/css/dark-mode.css');
//     wp_enqueue_style('responsive-style', get_template_directory_uri() . '/css/responsive.css');
// }
// add_action('wp_enqueue_scripts', 'enqueue_custom_styles');

function enqueue_google_fonts() {
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&display=swap', array(), null);
}
add_action('wp_enqueue_scripts', 'enqueue_google_fonts');

function enqueue_novel_list_scripts() {
    if (is_page_template('page-novel_parents.php')) {
        wp_enqueue_script('novel-list-js', get_template_directory_uri() . '/js/novel-list.js', array('jquery'), '1.0', true);
        wp_localize_script('novel-list-js', 'novelListAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_novel_list_scripts');

function enqueue_mypage_script() {
    if (is_page_template('page-mypage.php')) {
        wp_enqueue_script('mypage-script', get_template_directory_uri() . '/js/mypage.js', array('jquery'), '1.0', true);
        wp_localize_script('mypage-script', 'mypageAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('update_user_info_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_mypage_script');

function enqueue_favorite_script() {
    wp_enqueue_script('favorite-script', get_template_directory_uri() . '/js/favorite.js', array('jquery'), '1.0', true);
    wp_localize_script('favorite-script', 'favoriteAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('favorite_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_favorite_script');

function enqueue_nav_reset_script() {
    if (is_singular('novel_child')) {
        wp_enqueue_script('nav-reset', get_template_directory_uri() . '/js/nav-reset.js', array(), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_nav_reset_script');
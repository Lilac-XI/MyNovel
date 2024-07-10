<?php
// テーマのセットアップを行う関数
function novel_theme_setup() {
    add_theme_support('title-tag');
    register_nav_menus(array(
        'main-menu' => 'メインメニュー',
    ));
}
add_action( 'after_setup_theme', 'novel_theme_setup' );

// 小説子の順序サポートを追加する関数
function add_novel_child_order_support() {
    add_post_type_support( 'novel_child', 'page-attributes' );
}
add_action( 'init', 'add_novel_child_order_support' );
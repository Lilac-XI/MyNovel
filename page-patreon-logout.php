<?php
/*
Template Name: パトロン用ログアウト画面
*/

get_header(); // ヘッダーを取得

?>

<div class="novel-wrapper">
    <h1 class="novel-title">ログアウトしました</h1>
    <div class="novel-content">
        <p>再度ログインするには、以下のボタンをクリックしてください。</p>
        <?php
        // Patreonログインボタンのショートコードを表示
        echo do_shortcode('[patreon_login_button]');
        ?>
    </div>
</div>

<?php
get_footer(); // フッターを取得
?>
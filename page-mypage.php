<?php
/*
Template Name: マイページ
*/

get_header();
?>

<div class="novel-wrapper">
    <?php if ( is_user_logged_in() ) : ?>
        <?php $current_user = wp_get_current_user(); ?>
        <h1 class="mypage-title">マイページ</h1>
        <div class="favorite-novels">
            <h2>お気に入り小説</h2>
            <a href="/favorite" class="favorite-button">お気に入り小説一覧へ</a>
        </div>
        <div class="favorite-novels">
            <h2>限定小説の説明</h2>
            <a href="/subscription-info" class="favorite-button">限定小説について</a>
        </div>
        <div class="user-info">
            <h2>ユーザー情報</h2>
            <form id="user-info-form">
                <div class="info-item">
                    <label for="username">ユーザー名</label>
                    <p><?php echo esc_attr( $current_user->user_login ); ?> </p>
                </div>
                <div class="info-item">
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
                </div>
                <button type="submit" class="update-button">更新</button>
            </form>
        </div>
        <div id="message" class="message"></div>
    <?php else : ?>
        <h1 class="mypage-title">マイページ</h1>
        <p class="login-required-message">このページにアクセスするにはログインが必要です。</p>
        <?php
        // Patreonログインボタンのショートコードを表示
        echo do_shortcode('[patreon_login_button]');
        ?>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
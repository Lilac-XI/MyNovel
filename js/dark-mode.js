jQuery(document).ready(function($) {
    const darkModeToggle = $('#darkModeToggle');
    const body = $('body');

    // ローカルストレージからダークモードの状態を取得
    const isDarkMode = localStorage.getItem('darkMode') === 'true';

    // 初期状態を設定
    body.toggleClass('dark-mode', isDarkMode);
    darkModeToggle.prop('checked', isDarkMode);

    // ダークモードの切り替え
    darkModeToggle.on('change', function() {
        body.toggleClass('dark-mode', this.checked);
        localStorage.setItem('darkMode', this.checked);
    });
});
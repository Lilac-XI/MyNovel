<?php
// テーマのセットアップを行う関数
function novel_theme_setup() {
    add_theme_support('title-tag');
    register_nav_menus(array(
        'main-menu' => 'メインメニュー',
    ));
}
add_action( 'after_setup_theme', 'novel_theme_setup' );

// スタイルとスクリプトをキューに追加する関数
function novel_theme_scripts() {
    wp_enqueue_style( 'style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'novel_theme_scripts' );

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
            'hierarchical' => true, // これによりページ属性が有効になります
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
            'hierarchical' => true, // これによりページ属性が有効になります
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'page-attributes' ),
        )
    );
}
add_action( 'init', 'create_novel_post_types' );

// 小説の種類に関するメタボックスを追加する関数
function add_novel_type_meta_box() {
    add_meta_box(
        'novel_type_meta_box',
        '小説の種類',
        'display_novel_type_meta_box',
        'novel_parent',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'add_novel_type_meta_box' );

// 小説の種類のメタボックスを表示する関数
function display_novel_type_meta_box( $post ) {
    $novel_type = get_post_meta( $post->ID, 'novel_type', true );
    if (empty($novel_type)) {
        $novel_type = 'long'; // デフォルトを長編に設定
    }
    wp_nonce_field( 'novel_type_meta_box', 'novel_type_meta_box_nonce' );
    ?>
    <select name="novel_type" id="novel_type">
        <option value="long" <?php selected( $novel_type, 'long' ); ?>>長編</option>
        <option value="short" <?php selected( $novel_type, 'short' ); ?>>短編</option>
    </select>
    <?php
}

// 小説の種類のメタデータを保存する関数
function save_novel_type_meta_box( $post_id ) {
    if ( ! isset( $_POST['novel_type_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['novel_type_meta_box_nonce'], 'novel_type_meta_box' ) ) {
        return;
    }
    if ( isset( $_POST['novel_type'] ) ) {
        update_post_meta( $post_id, 'novel_type', sanitize_text_field( $_POST['novel_type'] ) );
    }
}
add_action( 'save_post', 'save_novel_type_meta_box' );


// 小説子の編集ページで親小説のメタボックスを表示
function add_parent_novel_meta_box() {
    add_meta_box(
        'parent_novel_meta_box',
        '親小説',
        'display_parent_novel_meta_box',
        'novel_child',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'add_parent_novel_meta_box' );

// 親小説のメタボックスを表示する関数
function display_parent_novel_meta_box( $post ) {
    $parent_novel = get_post_meta( $post->ID, 'parent_novel', true );
    // URLから親小説のIDを取得
    $parent_novel_from_query = isset($_GET['parent_novel']) ? $_GET['parent_novel'] : '';

    // URLパラメータがあればそれを使用し、なければ既存のメタデータを使用
    $parent_novel = !empty($parent_novel_from_query) ? $parent_novel_from_query : $parent_novel;

    $novel_parents = get_posts( array( 'post_type' => 'novel_parent', 'numberposts' => -1 ) );
    echo '<select name="parent_novel">';
    echo '<option value="">選択してください</option>';
    foreach ( $novel_parents as $novel_parent ) {
        $selected = ($parent_novel == $novel_parent->ID) ? 'selected' : '';
        echo '<option value="' . $novel_parent->ID . '" ' . $selected . '>';
        echo $novel_parent->post_title;
        echo '</option>';
    }
    echo '</select>';
}

// 親小説のメタデータを保存する関数
function save_parent_novel_meta_box( $post_id ) {
    if ( isset( $_POST['parent_novel'] ) ) {
        update_post_meta( $post_id, 'parent_novel', $_POST['parent_novel'] );
    }
}
add_action( 'save_post', 'save_parent_novel_meta_box' );

// 小説子の順序サポートを追加する関数
function add_novel_child_order_support() {
    add_post_type_support( 'novel_child', 'page-attributes' );
}
add_action( 'init', 'add_novel_child_order_support' );

// 小説子に関連するメタボックスを追加する関数
function add_novel_children_meta_box() {
    add_meta_box(
        'novel_children_meta_box',
        '関連する小説子',
        'display_novel_children_meta_box',
        'novel_parent',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'add_novel_children_meta_box' );

// 小説子に関連するメタボックスを表示する関数
function display_novel_children_meta_box( $post ) {
    $novel_type = get_post_meta( $post->ID, 'novel_type', true );
    ?>
    <div id="novel-children-section" style="display: <?php echo $novel_type === 'long' ? 'block' : 'none'; ?>;">
        <a href="<?php echo admin_url( 'post-new.php?post_type=novel_child&parent_novel=' . $post->ID ); ?>" class="button button-primary">新規小説子を作成</a>
        <?php
        $args = array(
            'post_type' => 'novel_child',
            'meta_query' => array(
                array(
                    'key' => 'parent_novel',
                    'value' => $post->ID,
                    'compare' => '='
                )
            ),
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        $novel_children = new WP_Query( $args );

        if ( $novel_children->have_posts() ) {
            echo '<ul id="novel-children-list">';
            while ( $novel_children->have_posts() ) {
                $novel_children->the_post();
                echo '<li id="post-' . get_the_ID() . '" class="menu-item"><a href="' . get_edit_post_link() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
            wp_reset_postdata();
        } else {
            echo '<p>関連する小説子が見つかりません。</p>';
        }
        ?>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#novel-children-list').sortable({
            update: function(event, ui) {
                var order = $(this).sortable('toArray').toString();
                $.post(ajaxurl, {
                    action: 'save_novel_children_order',
                    order: order,
                    security: '<?php echo wp_create_nonce("save_novel_children_order_nonce"); ?>'
                }, function(response) {
                    if (response.success) {
                    } else {
                        console.log('順番の保存に失敗しました');
                    }
                });
            }
        });

        function toggleNovelChildrenSection() {
            var novelType = $('#novel_type').val();
            if (novelType === 'long') {
                $('#novel-children-section').show();
            } else {
                $('#novel-children-section').hide();
                }
            }

            $('#novel_type').change(toggleNovelChildrenSection);
        
        // 初期表示時にも実行
        toggleNovelChildrenSection();
        });
    </script>
    <?php
}

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

// 小説親の編集画面に章に関するメタボックスを追加する関数
function add_chapters_meta_box() {
    add_meta_box(
        'chapters_meta_box',
        '章',
        'display_chapters_meta_box',
        'novel_parent',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'add_chapters_meta_box' );

// 章のメタボックスを表示する関数
function display_chapters_meta_box( $post ) {
    $chapters = get_post_meta( $post->ID, 'chapters', true );
    wp_nonce_field( 'chapters_meta_box', 'chapters_meta_box_nonce' );
    ?>
    <div id="chapters-wrapper">
        <?php if ( ! empty( $chapters ) ) : ?>
            <?php foreach ( $chapters as $index => $chapter ) : ?>
                <div class="chapter-item" data-index="<?php echo $index; ?>">
                    <span class="chapter-text"><?php echo esc_html( $chapter ); ?></span>
                    <input type="text" name="chapters[]" value="<?php echo esc_attr( $chapter ); ?>" style="display: none;" />
                    <button type="button" class="edit-chapter">編集</button>
                    <button type="button" class="remove-chapter">削除</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" id="add-chapter" class="button button-primary">章を追加</button>
    <script>
    jQuery(document).ready(function($) {
        var wrapper = $('#chapters-wrapper');

        $('#add-chapter').on('click', function() {
            var newIndex = wrapper.children().length;
            var newChapter = $('<div class="chapter-item" data-index="' + newIndex + '">' +
                '<span class="chapter-text"></span>' +
                '<input type="text" name="chapters[]" value="" />' +
                '<button type="button" class="edit-chapter">編集</button>' +
                '<button type="button" class="remove-chapter">削除</button>' +
                '</div>');
            wrapper.append(newChapter);
            newChapter.find('input').show().focus();
            newChapter.find('.chapter-text').hide();
        });

        wrapper.on('click', '.edit-chapter', function() {
            var item = $(this).closest('.chapter-item');
            item.find('.chapter-text').hide();
            item.find('input').show().focus();
        });

        wrapper.on('click', '.remove-chapter', function() {
            $(this).closest('.chapter-item').remove();
        });

        wrapper.on('blur', 'input', function() {
            var item = $(this).closest('.chapter-item');
            var text = $(this).val();
            item.find('.chapter-text').text(text).show();
            $(this).hide();
        });

        wrapper.sortable({
            items: '.chapter-item',
            cursor: 'move',
            axis: 'y',
            handle: '.chapter-text'
        });
    });
    </script>
    <?php
}

// 章のメタデータを保存する関数
function save_chapters_meta_box( $post_id ) {
    if ( ! isset( $_POST['chapters_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['chapters_meta_box_nonce'], 'chapters_meta_box' ) ) {
        return;
    }
    if ( isset( $_POST['chapters'] ) ) {
        $chapters = array_map( 'sanitize_text_field', $_POST['chapters'] );
        $chapters = array_filter( $chapters ); // 空の章を削除
        update_post_meta( $post_id, 'chapters', $chapters );
    }
}
add_action( 'save_post', 'save_chapters_meta_box' );

// 小説親のPatreon制限を小説子に同期する関数
function sync_patreon_restriction_to_children($post_id) {
    // 投稿タイプが小説親であることを確認
    if (get_post_type($post_id) !== 'novel_parent') {
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


// 小説子の編集画面に章のメタボックスを追加する関数
function add_chapter_meta_box() {
    add_meta_box(
        'chapter_meta_box',
        '章',
        'display_chapter_meta_box',
        'novel_child',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'add_chapter_meta_box' );

// 章のメタボックスを表示する関数
function display_chapter_meta_box($post) {
    // 小説子の親小説IDを取得
    $parent_novel_id = isset($_GET['parent_novel']) ? $_GET['parent_novel'] : get_post_meta($post->ID, 'parent_novel', true);

    // 親小説のチャプター情報を取得
    $chapters = get_post_meta($parent_novel_id, 'chapters', true);

    // 現在の画面がpost-newかどうかを確認
    $is_new_post = isset($_GET['post_type']) && $_GET['post_type'] === 'novel_child';

    // チャプターセレクトボックスを表示
    echo '<select name="chapter">';
    echo '<option value="">選択してください</option>';
    if (!empty($chapters)) {
        // 最新のチャプターを取得
        $latest_chapter = end($chapters);
        // 現在の記事のチャプターを取得
        $current_chapter = get_post_meta($post->ID, 'chapter', true);

        foreach ($chapters as $chapter) {
            // 新規作成時は最新のチャプターをデフォルトに、それ以外は現在のチャプターをデフォルトに
            $selected = '';
            if ($is_new_post && $chapter == $latest_chapter) { // 新規作成時
                $selected = 'selected';
            } elseif ($chapter == $current_chapter) { // 編集時
                $selected = 'selected';
            }
            echo '<option value="' . esc_attr($chapter) . '" ' . $selected . '>' . esc_html($chapter) . '</option>';
        }
    }
    echo '</select>';
}

// 章のメタデータを保存する関数
function save_chapter_meta_box( $post_id ) {
    if ( isset( $_POST['chapter'] ) ) {
        update_post_meta( $post_id, 'chapter', $_POST['chapter'] );
    }
}
add_action( 'save_post', 'save_chapter_meta_box' );

// ｜《》フリガナをHTMLのルビタグに変換する関数
function apply_furigana($content) {
    // ｜と《》を使用したふりがなのパターンを正規表現でマッチング
    $pattern = '/\｜([^《]+)《([^》]+)》/u';

    // マッチした部分をHTMLのルビタグで置換
    $replacement = '<ruby>$1<rt>$2</rt></ruby>';

    // コンテンツ内のすべてのパターンに対して置換を実行
    $content = preg_replace($pattern, $replacement, $content);

    return $content;
}
add_filter('the_content', 'apply_furigana', 9);

// 小説子のコンテンツを表示する前に、関連するリンクを追加する関数
// function add_novel_child_navigation() {
//     global $post;

//     if (get_post_type($post) !== 'novel_child') {
//         return '';
//     }

//     $parent_id = get_post_meta($post->ID, 'parent_novel', true);
//     if (!$parent_id) {
//         return '';
//     }

//     $novel_children = get_posts(array(
//         'post_type' => 'novel_child',
//         'meta_query' => array(
//             array(
//                 'key' => 'parent_novel',
//                 'value' => $parent_id,
//             ),
//         ),
//         'orderby' => 'menu_order',
//         'order' => 'ASC',
//         'posts_per_page' => -1,
//     ));

//     $current_index = 0;
//     foreach ($novel_children as $index => $child) {
//         if ($child->ID == $post->ID) {
//             $current_index = $index;
//             break;
//         }
//     }

//     $navigation = '<div class="novel-child-navigation">';

//     // 前のリンク
//     if ($current_index > 0) {
//         $prev_post = $novel_children[$current_index - 1];
//         $navigation .= '<a href="' . get_permalink($prev_post->ID) . '">前へ</a> | ';
//     }

//     // 目次リンク
//     $navigation .= '<a href="' . get_permalink($parent_id) . '">目次</a> | ';

//     // 次のリンク
//     if ($current_index < count($novel_children) - 1) {
//         $next_post = $novel_children[$current_index + 1];
//         $navigation .= '<a href="' . get_permalink($next_post->ID) . '">次へ</a>';
//     }

//     $navigation .= '</div>';

//     return $navigation;
// }

function enqueue_google_fonts() {
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;700&display=swap', array(), null);
}
add_action('wp_enqueue_scripts', 'enqueue_google_fonts');

// Patreonプラグインの campaign-banner を中央寄せにする
add_action('wp_enqueue_scripts', 'center_patreon_campaign_banner');

function center_patreon_campaign_banner() {
    $patreon_styles = '
        .patreon-campaign-banner {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 20px 0;
            text-align: center;
        }
        .patreon-campaign-banner > * {
            margin: 10px 0;
        }
        .patreon-campaign-banner a {
            display: inline-block;
        }
        .patreon-campaign-banner p {
            margin: 0;
        }
    ';

    wp_add_inline_style('patreon-connect-public', $patreon_styles);
}



function add_novel_parent_link_meta_box() {
    add_meta_box(
        'novel_parent_link',
        '小説親へのリンク',
        'novel_parent_link_meta_box_callback',
        'novel_child',
        'side',
        'high'
    );
}

// 小説子の編集画面に小説親へのリンクを追加
add_action('add_meta_boxes', 'add_novel_parent_link_meta_box');

function novel_parent_link_meta_box_callback($post) {
    $parent_novel_id = get_post_meta($post->ID, 'parent_novel', true);
    
    if ($parent_novel_id) {
        $parent_novel = get_post($parent_novel_id);
        if ($parent_novel) {
            $edit_link = get_edit_post_link($parent_novel_id);
            echo '<a href="' . esc_url($edit_link) . '" class="button">小説親を編集</a>';
        } else {
            echo '紐づく小説親が見つかりません。';
        }
    } else {
        echo '小説親が設定されていません。';
    }
}

// 小説子の編集画面に関連する小説子のリストを追加
add_action('add_meta_boxes', 'add_related_novel_children_meta_box');

function add_related_novel_children_meta_box() {
    add_meta_box(
        'related_novel_children',
        '関連する小説子',
        'related_novel_children_meta_box_callback',
        'novel_child',
        'side',
        'default'
    );
}

function related_novel_children_meta_box_callback($post) {
    $parent_novel_id = get_post_meta($post->ID, 'parent_novel', true);
    
    if (!$parent_novel_id) {
        echo '小説親が設定されていません。';
        return;
    }

    $parent_novel = get_post($parent_novel_id);
    if (!$parent_novel) {
        echo '紐づく小説親が見つかりません。';
        return;
    }

    // 新規小説子を作成するリンク
    $new_child_link = admin_url("post-new.php?post_type=novel_child&parent_novel={$parent_novel_id}");
    echo "<a href='{$new_child_link}' class='button'>新規小説子を作成</a><br><br>";

    // 関連する小説子のリストを取得
    $args = array(
        'post_type' => 'novel_child',
        'meta_query' => array(
            array(
                'key' => 'parent_novel',
                'value' => $parent_novel_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );
    $novel_children = new WP_Query($args);

    if ($novel_children->have_posts()) {
        echo '<ul id="related-novel-children-list">';
        while ($novel_children->have_posts()) {
            $novel_children->the_post();
            $edit_link = get_edit_post_link();
            $title = get_the_title();
            echo "<li><a href='{$edit_link}'>{$title}</a></li>";
        }
        echo '</ul>';
        wp_reset_postdata();
    } else {
        echo '<p>関連する小説子が見つかりません。</p>';
    }
}

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

function append_novel_child_list($content) {
    if (is_singular('novel_parent')) {
        ob_start();
        ?>
        <div class="novel-chapters">
            <?php
            $chapters = get_post_meta(get_the_ID(), 'chapters', true);
            $args = array(
                'post_type' => 'novel_child',
                'meta_query' => array(
                    array(
                        'key' => 'parent_novel',
                        'value' => get_the_ID(),
                    ),
                ),
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'posts_per_page' => -1,
            );
            $novel_children = new WP_Query($args);

            if ($chapters && $novel_children->have_posts()) :
                $current_chapter = '';
                while ($novel_children->have_posts()) : $novel_children->the_post();
                    $chapter = get_post_meta(get_the_ID(), 'chapter', true);
                    if ($chapter !== $current_chapter) {
                        if ($current_chapter !== '') {
                            echo '</ul></div>';
                        }
                        echo '<div class="chapter">';
                        echo '<div class="chapter-title">' . esc_html($chapter) . '</div>';
                        echo '<ul class="episode-list">';
                        $current_chapter = $chapter;
                    }
                    ?>
                    <li>
                        <div class="episode-item">
                            <a href="<?php the_permalink(); ?>" class="episode-link"><?php the_title(); ?></a>
                            <span class="episode-date"><?php echo get_the_modified_date('Y年m月d日(D) H:i'); ?></span>
                        </div>
                    </li>
                    <?php
                endwhile;
                echo '</ul></div>';
                wp_reset_postdata();
            else :
                echo '<p>エピソードがありません。</p>';
            endif;
            ?>
        </div>
        <?php
        $novel_child_list = ob_get_clean();
        $content .= $novel_child_list;
    }
    return $content;
}
add_filter('the_content', 'append_novel_child_list');

function custom_logout_redirect($logout_url, $redirect) {
    if (!current_user_can('manage_options')) {  // 管理者権限を持たないユーザーの場合
        $logout_url = add_query_arg('redirect_to', urlencode(home_url('/patreon-logout')), $logout_url);
    }
    return $logout_url;
}
add_filter('logout_url', 'custom_logout_redirect', 10, 2);

function search_novel_parents() {
    $query = sanitize_text_field($_POST['query']);
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'newest';
    
    $args = array(
        'post_type' => 'novel_parent',
        's' => $query,
        'posts_per_page' => -1,
        'orderby' => 'menu_order', // 追加: 並び替えの基準をmenu_orderに設定
        'order' => 'ASC'           // 追加: 昇順で並べ替え
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
            $args['meta_key'] = 'novel_views'; // 閲覧数を記録するカスタムフィールドを想定
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
                <p class="novel-item-description"><?php echo wp_trim_words(get_the_excerpt(), 30); ?></p>
                <div class="novel-item-info">
                    <?php
                    $args = array(
                        'post_type' => 'novel_child',
                        'meta_query' => array(
                            array(
                                'key' => 'parent_novel',
                                'value' => get_the_ID(),
                            ),
                        ),
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'posts_per_page' => 1,
                    );
                    $latest_child = new WP_Query($args);
                    if ($latest_child->have_posts()) :
                        $latest_child->the_post();
                        ?>
                        <span>最新話: <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> (<?php echo get_the_date('Y/n/j'); ?>)</span>
                        <?php
                        wp_reset_postdata();
                    endif;
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

function enqueue_novel_list_scripts() {
    if (is_page_template('page-novel_parents.php')) {
        wp_enqueue_script('novel-list-js', get_template_directory_uri() . '/js/novel-list.js', array('jquery'), '1.0', true);
        wp_localize_script('novel-list-js', 'novelListAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_novel_list_scripts');

// 日付フォーマットをカスタマイズする関数
function custom_date_format($date) {
    $weekdays = array('日', '月', '火', '水', '木', '金', '土');
    $timestamp = strtotime($date);
    $formatted_date = date('Y年m月d日', $timestamp) . '(' . $weekdays[date('w', $timestamp)] . ') ' . date('H:i', $timestamp);
    return $formatted_date;
}

// get_the_modified_date()のフォーマットをカスタマイズ
function custom_modified_date($the_date, $d = '', $post = null) {
    if (empty($post)) {
        $post = get_post();
    }

    if (!$post) {
        return $the_date;
    }

    $modified_date = $post instanceof WP_Post ? $post->post_modified : get_post_field('post_modified', $post);

    if (!$modified_date) {
        return $the_date;
    }

    return custom_date_format($modified_date);
}
add_filter('get_the_modified_date', 'custom_modified_date', 10, 3);
?>


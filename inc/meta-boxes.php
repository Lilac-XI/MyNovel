<?php
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
        $novel_type = 'long';
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
    $parent_novel_from_query = isset($_GET['parent_novel']) ? $_GET['parent_novel'] : '';
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
        // 章の追加、編集、削除、並び替えの JavaScript コード
        // （省略）
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
        $chapters = array_filter( $chapters );
        update_post_meta( $post_id, 'chapters', $chapters );
    }
}
add_action( 'save_post', 'save_chapters_meta_box' );

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
    $parent_novel_id = isset($_GET['parent_novel']) ? $_GET['parent_novel'] : get_post_meta($post->ID, 'parent_novel', true);
    $chapters = get_post_meta($parent_novel_id, 'chapters', true);
    $is_new_post = isset($_GET['post_type']) && $_GET['post_type'] === 'novel_child';
    $current_chapter = get_post_meta($post->ID, 'chapter', true);

    echo '<select name="chapter">';
    echo '<option value="">選択してください</option>';
    if (!empty($chapters)) {
        $latest_chapter = end($chapters);
        foreach ($chapters as $chapter) {
            $selected = '';
            if ($is_new_post && $chapter == $latest_chapter) {
                $selected = 'selected';
            } elseif ($chapter == $current_chapter) {
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

// 小説子の編集画面に小説親へのリンクを追加
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
add_action('add_meta_boxes', 'add_related_novel_children_meta_box');

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

    $new_child_link = admin_url("post-new.php?post_type=novel_child&parent_novel={$parent_novel_id}");
    echo "<a href='{$new_child_link}' class='button'>新規小説子を作成</a><br><br>";

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
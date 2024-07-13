<?php
// カスタムタクソノミー（タグ）を作成
function create_novel_tag_taxonomy() {
    $labels = array(
        'name'              => _x('小説タグ', 'taxonomy general name'),
        'singular_name'     => _x('小説タグ', 'taxonomy singular name'),
        'search_items'      => __('小説タグを検索'),
        'all_items'         => __('すべての小説タグ'),
        'parent_item'       => __('親タグ'),
        'parent_item_colon' => __('親タグ:'),
        'edit_item'         => __('小説タグを編集'),
        'update_item'       => __('小説タグを更新'),
        'add_new_item'      => __('新しい小説タグを追加'),
        'new_item_name'     => __('新しい小説タグ名'),
        'menu_name'         => __('小説タグ'),
    );

    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'novel-tag'),
    );

    register_taxonomy('novel_tag', array('novel_parent'), $args);
}
add_action('init', 'create_novel_tag_taxonomy', 0);

// 管理メニューに「タグ管理」を追加
function add_novel_tag_admin_menu() {
    add_menu_page(
        '小説タグ管理',
        '小説タグ管理',
        'manage_options',
        'edit-tags.php?taxonomy=novel_tag',
        '',
        'dashicons-tag',
        20
    );
}
add_action('admin_menu', 'add_novel_tag_admin_menu');

// タグ管理ページのスラッグを修正
function fix_novel_tag_admin_menu_slug($parent_file) {
    global $pagenow, $taxnow;
    if ($pagenow == 'edit-tags.php' && $taxnow == 'novel_tag') {
        $parent_file = 'edit-tags.php?taxonomy=novel_tag';
    }
    return $parent_file;
}
add_filter('parent_file', 'fix_novel_tag_admin_menu_slug');

// タグ管理ページのタイトルを修正
function fix_novel_tag_admin_title($admin_title, $title) {
    global $pagenow, $taxnow;
    if ($pagenow == 'edit-tags.php' && $taxnow == 'novel_tag') {
        $admin_title = '小説タグ管理' . $admin_title;
    }
    return $admin_title;
}
add_filter('admin_title', 'fix_novel_tag_admin_title', 10, 2);

// 小説親の編集画面にタグ選択欄を追加
function add_novel_tag_meta_box() {
    remove_meta_box('tagsdiv-novel_tag', 'novel_parent', 'side');
    add_meta_box(
        'novel_tag_meta_box',
        '小説タグ',
        'display_novel_tag_meta_box',
        'novel_parent',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_novel_tag_meta_box');

// タグ選択欄の表示
function display_novel_tag_meta_box($post) {
    $taxonomy = 'novel_tag';
    $tags = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));
    $current_tags = wp_get_object_terms($post->ID, $taxonomy);
    ?>
    <div class="tagsdiv" id="<?php echo $taxonomy; ?>">
        <div class="jaxtag">
            <div class="ajaxtag">
                <input type="text" name="newtag[<?php echo $taxonomy; ?>]" class="newtag form-input-tip" size="16" autocomplete="off" value="" />
                <input type="button" class="button tagadd" value="追加" />
            </div>
        </div>
        <p class="description">複数のタグを追加する場合はカンマ（,）で区切ってください。</p>
        <div class="tag-suggestions" style="display: none;"></div>
        <ul class="tagchecklist" role="list">
            <?php foreach ($current_tags as $tag) : ?>
                <li>
                    <button type="button" class="ntdelbutton" data-term-id="<?php echo $tag->term_id; ?>">
                        <span class="remove-tag-icon" aria-hidden="true"></span>
                        <span class="screen-reader-text">タグを削除: <?php echo $tag->name; ?></span>
                    </button>
                    <span class="tag-name"><?php echo $tag->name; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <input type="hidden" name="tax_input[<?php echo $taxonomy; ?>]" class="the-tags" value="<?php echo esc_attr(implode(',', wp_list_pluck($current_tags, 'name'))); ?>" />
    </div>
    <style>
    .tagchecklist {
        margin-top: 10px;
    }
    .tagchecklist li {
        display: inline-block;
        margin: 5px;
        padding: 5px 10px;
        background-color: #f1f1f1;
        border-radius: 3px;
        cursor: pointer;
    }
    .tagchecklist li:hover {
        background-color: #e1e1e1;
    }
    .tagchecklist .ntdelbutton {
        margin-right: 5px;
        color: #a00;
        border: none;
        background: none;
        cursor: pointer;
    }
    .tag-suggestions {
        margin-top: 5px;
    }
    .tag-suggestions .tag-suggestion {
        display: inline-block;
        margin: 2px;
        padding: 3px 7px;
        background-color: #e1e1e1;
        border-radius: 3px;
        cursor: pointer;
    }
    .tag-suggestions .tag-suggestion:hover {
        background-color: #d1d1d1;
    }
    </style>
    <script>
    jQuery(document).ready(function($) {
        var taxonomy = '<?php echo $taxonomy; ?>';
        var tagBox;
        var $tagInput = $('input.newtag', '#<?php echo $taxonomy; ?>');
        var $tagSuggestions = $('.tag-suggestions', '#<?php echo $taxonomy; ?>');
        var allTags = <?php echo json_encode(wp_list_pluck($tags, 'name')); ?>;
        
        if (typeof window.tagBox === 'undefined') {
            tagBox = {
                clean: function(tags) {
                    return tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
                },
                parseTags: function(el) {
                    var id = el.id,
                        num = id.split('-check-num-')[1],
                        taxbox = $(el).closest('.tagsdiv'),
                        thetags = taxbox.find('.the-tags'),
                        current_tags = thetags.val().split(','),
                        new_tags = [];

                    delete current_tags[num];

                    $.each(current_tags, function(key, val) {
                        val = $.trim(val);
                        if (val) {
                            new_tags.push(val);
                        }
                    });

                    thetags.val(this.clean(new_tags.join(',')));

                    this.quickClicks(taxbox);
                    return false;
                },
                quickClicks: function(el) {
                    var thetags = $('.the-tags', el),
                        tagchecklist = $('.tagchecklist', el),
                        id = $(el).attr('id'),
                        current_tags, disabled;

                    if (!thetags.length)
                        return;

                    disabled = thetags.prop('disabled');

                    current_tags = thetags.val().split(',');
                    tagchecklist.empty();

                    $.each(current_tags, function(key, val) {
                        var span, xbutton;

                        val = $.trim(val);

                        if (!val)
                            return;

                        // Create a visual span
                        span = $('<span class="tag-name" />').text(val);

                        // Create a button to remove the tag
                        xbutton = $('<button type="button" class="ntdelbutton">')
                            .attr('data-term-id', val)
                            .html('<span class="remove-tag-icon" aria-hidden="true"></span><span class="screen-reader-text">タグを削除: ' + val + '</span>');

                        xbutton.on('click', function() {
                            tagBox.parseTags(this);
                        });

                        // Append the span and button to the tag list
                        tagchecklist.append($('<li />').append(xbutton).append(span));
                    });
                },
                flushTags: function(el, a, f) {
                    var tagsval, newtags, text,
                        tags = $('.the-tags', el),
                        newtag = $('input.newtag', el),
                        comma = tagBox.comma;

                    a = a || false;

                    text = a ? $(a).text() : newtag.val();
                    tagsval = tags.val();
                    newtags = tagsval ? tagsval + comma + text : text;

                    newtags = this.clean(newtags);
                    newtags = array_unique_noempty(newtags.split(comma)).join(comma);
                    tags.val(newtags);
                    this.quickClicks(el);

                    if (!a)
                        newtag.val('');
                    if ('undefined' == typeof(f))
                        newtag.focus();

                    return false;
                },
                comma: ','
            };
        } else {
            tagBox = window.tagBox;
        }

        $('.tagsdiv').each(function() {
            tagBox.quickClicks(this);
        });

        $('.tagadd', '.tagsdiv').click(function() {
            tagBox.userAction = 'add';
            tagBox.flushTags($(this).closest('.tagsdiv'));
        });

        $tagInput.on('input', function() {
            var input = $(this).val().toLowerCase();
            if (input.length > 0) {
                var suggestions = allTags.filter(function(tag) {
                    return tag.toLowerCase().indexOf(input) !== -1;
                });
                renderSuggestions(suggestions);
            } else {
                $tagSuggestions.hide().empty();
            }
        });

        function renderSuggestions(suggestions) {
            $tagSuggestions.empty();
            if (suggestions.length > 0) {
                suggestions.forEach(function(tag) {
                    $('<span class="tag-suggestion"></span>')
                        .text(tag)
                        .appendTo($tagSuggestions)
                        .on('click', function() {
                            $tagInput.val(tag);
                            tagBox.flushTags($tagInput.closest('.tagsdiv'));
                            $tagSuggestions.hide().empty();
                        });
                });
                $tagSuggestions.show();
            } else {
                $tagSuggestions.hide();
            }
        }

        $('input.newtag', '.tagsdiv').keyup(function(e) {
            if (13 == e.which) {
                tagBox.userAction = 'add';
                tagBox.flushTags($(this).closest('.tagsdiv'));
                $tagSuggestions.hide().empty();
            }
        }).keypress(function(e) {
            if (13 == e.which) {
                e.preventDefault();
                return false;
            }
        });

        // Ensure the original tag suggestion functionality still works
        $('input.newtag', '.tagsdiv').each(function() {
            var tax = $(this).closest('div.tagsdiv').attr('id');
            $(this).suggest(ajaxurl + '?action=ajax-tag-search&tax=' + tax, {
                delay: 500,
                minchars: 2,
                multiple: true,
                multipleSep: tagBox.comma
            });
        });
    });
    </script>
    <?php
}

// タグの保存
function save_novel_tag($post_id) {
    $taxonomy = 'novel_tag';
    if (isset($_POST['tax_input'][$taxonomy])) {
        $tags = explode(',', $_POST['tax_input'][$taxonomy]);
        $tags = array_filter(array_map('trim', $tags));
        wp_set_object_terms($post_id, $tags, $taxonomy);
    }
}
add_action('save_post_novel_parent', 'save_novel_tag');

// タググループタクソノミーの作成
function create_tag_group_taxonomy() {
    $labels = array(
        'name'              => 'タググループ',
        'singular_name'     => 'タググループ',
        'search_items'      => 'タググループを検索',
        'all_items'         => 'すべてのタググループ',
        'parent_item'       => '親タググループ',
        'parent_item_colon' => '親タググループ:',
        'edit_item'         => 'タググループを編集',
        'update_item'       => 'タググループを更新',
        'add_new_item'      => '新しいタググループを追加',
        'new_item_name'     => '新しいタググループ名',
        'menu_name'         => 'タググループ',
    );

    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'tag-group' ),
    );

    register_taxonomy( 'tag_group', 'novel_tag', $args );
}
add_action( 'init', 'create_tag_group_taxonomy', 0 );

// タグ編集画面にグループ選択フィールドを追加
function add_tag_group_field($tag) {
    // $tag が文字列の場合（新規追加時）は、term_id を 0 とする
    $term_id = is_object($tag) ? $tag->term_id : 0;
    $tag_group = get_term_meta($term_id, 'tag_group', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="tag_group">タググループ</label></th>
        <td>
            <select name="tag_group" id="tag_group">
                <option value="">グループを選択</option>
                <?php
                $tag_groups = get_terms([
                    'taxonomy' => 'tag_group',
                    'hide_empty' => false,
                ]);
                foreach ($tag_groups as $group) {
                    echo '<option value="' . esc_attr($group->term_id) . '"' . selected($tag_group, $group->term_id, false) . '>' . esc_html($group->name) . '</option>';
                }
                ?>
            </select>
            <p class="description">このタグが属するグループを選択してください。</p>
        </td>
    </tr>
    <?php
}
add_action('novel_tag_edit_form_fields', 'add_tag_group_field', 10, 2);
add_action('novel_tag_add_form_fields', 'add_tag_group_field', 10, 2);

// タグのグループを保存
function save_tag_group($term_id) {
    if (isset($_POST['tag_group'])) {
        update_term_meta($term_id, 'tag_group', sanitize_text_field($_POST['tag_group']));
    }
}
add_action('edited_novel_tag', 'save_tag_group', 10, 2);
add_action('create_novel_tag', 'save_tag_group', 10, 2);

// タグ一覧画面にグループ列を追加
function add_tag_group_column($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'name') {
            $new_columns['tag_group'] = 'グループ';
        }
    }
    return $new_columns;
}
add_filter('manage_edit-novel_tag_columns', 'add_tag_group_column');

// タグ一覧画面のグループ列にデータを表示
function display_tag_group_column($content, $column_name, $term_id) {
    if ($column_name === 'tag_group') {
        $tag_group_id = get_term_meta($term_id, 'tag_group', true);
        if ($tag_group_id) {
            $tag_group = get_term($tag_group_id, 'tag_group');
            if ($tag_group && !is_wp_error($tag_group)) {
                $content = esc_html($tag_group->name);
                // JavaScriptで使用するために、グループIDも非表示で追加
                $content .= '<span class="hidden">' . esc_html($tag_group_id) . '</span>';
            }
        }
    }
    return $content;
}
add_filter('manage_novel_tag_custom_column', 'display_tag_group_column', 10, 3);

// タググループ管理メニューを小説タグ管理の下に追加
function add_tag_group_submenu() {
    add_submenu_page(
        'edit-tags.php?taxonomy=novel_tag',
        'タググループ管理',
        'タググループ管理',
        'manage_categories',
        'edit-tags.php?taxonomy=tag_group'
    );
}
add_action('admin_menu', 'add_tag_group_submenu');

// タググループ管理画面のタイトルを変更
function change_tag_group_title($title) {
    $screen = get_current_screen();
    if ($screen->id == 'edit-tag_group') {
        return 'タググループ管理';
    }
    return $title;
}
add_filter('admin_title', 'change_tag_group_title');

// タググループ管理画面のパンくずリストを修正
function modify_tag_group_parent_file($parent_file) {
    global $pagenow, $taxonomy;
    if ($pagenow == 'edit-tags.php' && $taxonomy == 'tag_group') {
        $parent_file = 'edit-tags.php?taxonomy=novel_tag';
    }
    return $parent_file;
}
add_filter('parent_file', 'modify_tag_group_parent_file');

// タググループ管理画面のサブメニューをハイライト
function highlight_tag_group_submenu($parent_file) {
    global $submenu_file, $current_screen, $pagenow, $taxonomy;
    if ($pagenow == 'edit-tags.php' && $taxonomy == 'tag_group') {
        $submenu_file = 'edit-tags.php?taxonomy=tag_group';
    }
    return $parent_file;
}
add_filter('parent_file', 'highlight_tag_group_submenu');

// タググループ管理画面にヘルプタブを追加
function add_tag_group_help_tab() {
    $screen = get_current_screen();
    if ($screen->id != 'edit-tag_group') {
        return;
    }

    $content = '<p>タググループを使用して、小説タグを整理できます。</p>';
    $content .= '<p>グループを作成、編集、削除するには、以下の手順に従ってください：</p>';
    $content .= '<ul>';
    $content .= '<li>新しいグループを追加するには、左側のフォームに名前を入力し、「新規グループを追加」ボタンをクリックします。</li>';
    $content .= '<li>既存のグループを編集するには、グループ名をクリックして編集画面を開きます。</li>';
    $content .= '<li>グループを削除するには、グループ名にカーソルを合わせて表示される「削除」リンクをクリックします。</li>';
    $content .= '</ul>';

    $screen->add_help_tab(array(
        'id'      => 'tag_group_help',
        'title'   => 'タググループの管理',
        'content' => $content,
    ));
}
add_action('admin_head', 'add_tag_group_help_tab');

// クイック編集にタググループフィールドを追加
function add_quick_edit_tag_group($column_name, $screen, $name) {
    if ($screen === 'edit-tags' && $name === 'novel_tag' && $column_name === 'tag_group') {
        ?>
        <fieldset>
            <div class="inline-edit-col">
                <label>
                    <span class="title">タググループ</span>
                    <span class="input-text-wrap">
                        <select name="tag_group" class="tag_group">
                            <option value="">グループを選択</option>
                            <?php
                            $tag_groups = get_terms([
                                'taxonomy' => 'tag_group',
                                'hide_empty' => false,
                            ]);
                            foreach ($tag_groups as $group) {
                                echo '<option value="' . esc_attr($group->term_id) . '">' . esc_html($group->name) . '</option>';
                            }
                            ?>
                        </select>
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
    }
}
add_action('quick_edit_custom_box', 'add_quick_edit_tag_group', 10, 3);

// クイック編集用のJavaScriptを追加
function add_quick_edit_tag_group_js() {
    $screen = get_current_screen();
    if ($screen->id === 'edit-novel_tag') {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $wp_inline_edit = inlineEditTax.edit;
            inlineEditTax.edit = function(id) {
                $wp_inline_edit.apply(this, arguments);
                var tag_id = 0;
                if (typeof(id) === 'object') {
                    tag_id = parseInt(this.getId(id));
                }
                if (tag_id > 0) {
                    var tag_group = $('#tag-' + tag_id).find('.column-tag_group').text();
                    $('select[name="tag_group"]', '.inline-edit-row').val(tag_group);
                }
            };
        });
        </script>
        <?php
    }
}
add_action('admin_footer', 'add_quick_edit_tag_group_js');

// クイック編集でのタググループ保存を処理
function save_quick_edit_tag_group($term_id) {
    if (isset($_POST['tag_group'])) {
        update_term_meta($term_id, 'tag_group', sanitize_text_field($_POST['tag_group']));
    }
}
add_action('edited_novel_tag', 'save_quick_edit_tag_group', 10, 2);

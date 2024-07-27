<?php
// ｜《》フリガナをHTMLのルビタグに変換する関数
function apply_furigana($content) {
    $pattern = '/\｜([^《]+)《([^》]+)》/u';
    $replacement = '<ruby>$1<rt>$2</rt></ruby>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
add_filter('the_content', 'apply_furigana', 9);

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

// Patreonプラグインの campaign-banner を中央寄せにする
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
add_action('wp_enqueue_scripts', 'center_patreon_campaign_banner');

function get_latest_episode($parent_id) {
    $args = array(
        'post_type' => 'novel_child',
        'meta_query' => array(
            array(
                'key' => 'parent_novel',
                'value' => $parent_id,
            ),
        ),
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => 1,
    );
    $latest_child = new WP_Query($args);
    if ($latest_child->have_posts()) {
        $latest_child->the_post();
        $output = '<a href="' . get_permalink() . '">' . get_the_title() . '</a> (' . get_the_date('Y/n/j') . ')';
        wp_reset_postdata();
        return $output;
    }
    return '最新話はありません';
}

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
                    <li class="episode-item">
                        <a href="<?php the_permalink(); ?>" class="episode-link"><?php the_title(); ?></a>
                        <span class="episode-date"><?php echo get_the_modified_date('Y年m月d日(D) H:i'); ?></span>
                    </li>
                    <?php
                endwhile;
                echo '</ul></div>';
                wp_reset_postdata();
            else :
                // echo '<p>エピソードがありません。</p>';
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
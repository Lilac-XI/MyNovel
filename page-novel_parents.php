<?php
/*
Template Name: Novel Parents Page
*/
get_header(); ?>

<div class="novel-wrapper">
    <h2 class="novel-list-title">小説一覧</h2>
    
    <div class="novel-search-sort">
        <div class="search-tabs">
            <button class="search-tab active" data-tab="text-search">テキスト検索</button>
            <button class="search-tab" data-tab="tag-search">タグ検索</button>
        </div>
        <div class="search-content">
            <div class="search-panel active" id="text-search">
                <div class="novel-search">
                    <input type="text" id="novel-search-input" placeholder="小説を検索">
                    <button id="novel-search-button">検索</button>
                </div>
            </div>
            <div class="search-panel" id="tag-search">
                <div class="tag-cloud">
                    <?php
                    // 小説親のメタ情報からタグを取得
                    $args = array(
                        'post_type' => 'novel_parent',
                        'posts_per_page' => -1,
                    );
                    $novel_parents = get_posts($args);
                    $all_tags = array();
                    foreach ($novel_parents as $novel) {
                        $tags = get_post_meta($novel->ID, 'novel_tags', true);
                        if (is_array($tags)) {
                            $all_tags = array_merge($all_tags, $tags);
                        }
                    }
                    $unique_tags = array_unique($all_tags);
                    foreach ($unique_tags as $tag) {
                        echo '<span class="novel-tag">' . esc_html($tag) . '</span>';
                    }
                    ?>
                </div>
                <div class="novel-search">
                    <button id="tag-search-button">検索</button>
                </div>
            </div>
        </div>
        <div class="novel-sort">
            <select id="novel-sort-select">
                <option value="newest">新着順</option>
                <option value="oldest">古い順</option>
                <option value="title">タイトル順</option>
                <option value="popular">人気順</option>
            </select>
        </div>
        <div class="novel-filter">
            <label>
                <input type="checkbox" id="limited-episodes-filter">
                限定エピソードあり
            </label>
        </div>
    </div>

    <ul id="novel-list" class="novel-list">
        <?php
        $args = array(
            'post_type' => 'novel_parent',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        $novel_parents = new WP_Query($args);

        if ($novel_parents->have_posts()) :
            while ($novel_parents->have_posts()) : $novel_parents->the_post();
                ?>
                <li class="novel-item">
                    <h3 class="novel-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php
                        // タグを取得して表示
                        $tags = get_post_meta(get_the_ID(), 'novel_tags', true);
                        if (!empty($tags)) {
                            echo '<ul class="novel-item-tags">';
                            foreach ($tags as $tag) {
                                echo '<li data-category="">' . esc_html($tag) . '</li>';
                            }
                            echo '</ul>';
                        }
                    ?>
                    <p class="novel-item-description"><?php echo wp_trim_words(get_the_excerpt(), 100, "..."); ?></p>
                    <div class="novel-item-info">
                        <?php
                        $latest_episode = get_latest_episode(get_the_ID());
                        echo '<span>最新話: ' . $latest_episode . '</span>';
                        ?>
                    </div>
                </li>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>小説がありません。</p>';
        endif;
        ?>
    </ul>
</div>

<?php get_footer(); ?>
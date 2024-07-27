<?php
/*
Template Name: 小説一覧
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
                <div class="novel-filter">
                    <label>
                        <input type="checkbox" id="limited-episodes-filter">
                        限定エピソードあり
                    </label>
                </div>
                <div class="novel-search">
                    <input type="text" id="novel-search-input" placeholder="小説を検索">
                    <button id="novel-search-button">検索</button>
                </div>
            </div>
            <div class="search-panel" id="tag-search">
                <div class="tag-groups">
                    <?php
                    // タググループを取得
                    $tag_groups = get_terms(array(
                        'taxonomy' => 'tag_group',
                        'hide_empty' => false,
                    ));

                    foreach ($tag_groups as $group) {
                        // グループに属するタグを取得
                        $tags = get_terms(array(
                            'taxonomy' => 'novel_tag',
                            'hide_empty' => false,
                            'meta_query' => array(
                                array(
                                'key' => 'tag_group',
                                'value' => $group->term_id,
                                'compare' => '='
                                )
                            )
                        ));

                        if (!empty($tags)) {
                            echo '<div class="tag-group">';
                            echo '<h3 class="tag-group-name">' . esc_html($group->name) . ' <span class="toggle-icon">+</span></h3>';
                            echo '<div class="tag-cloud" style="display: block;">';
                            foreach ($tags as $tag) {
                                echo '<span class="novel-tag" data-tag-id="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</span>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                    }

                    // グループに属していないタグを取得
                    $ungrouped_tags = get_terms(array(
                        'taxonomy' => 'novel_tag',
                        'hide_empty' => false,
                        'meta_query' => array(
                            'relation' => 'OR',
                            array(
                                'key' => 'tag_group',
                                'compare' => 'NOT EXISTS'
                            ),
                            array(
                                'key' => 'tag_group',
                                'value' => '',
                                'compare' => '='
                            )
                        )
                    ));

                    if (!empty($ungrouped_tags)) {
                        echo '<div class="tag-group">';
                        echo '<h3 class="tag-group-name">未分類 <span class="toggle-icon">+</span></h3>';
                        echo '<div class="tag-cloud" style="display: none;">';
                        foreach ($ungrouped_tags as $tag) {
                            echo '<span class="novel-tag" data-tag-id="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</span>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <div class="novel-filter">
                    <label>
                        <input type="checkbox" id="limited-episodes-filter">
                        限定エピソードあり
                    </label>
                </div>
                <div class="tag-search-button-container">
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
    </div>

    <ul id="novel-list" class="novel-list">
        <?php
        $args = array(
            'post_type' => 'novel_parent',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $novel_parents = new WP_Query($args);

        if ($novel_parents->have_posts()) :
            while ($novel_parents->have_posts()) : $novel_parents->the_post();
                ?>
                <li class="novel-item">
                    <h3 class="novel-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php
                        $tags = get_the_terms(get_the_ID(), 'novel_tag');
                        if ($tags && !is_wp_error($tags)) {
                            echo '<ul class="novel-item-tags">';
                            foreach ($tags as $tag) {
                                echo '<li data-tag-id="' . $tag->term_id . '">' . esc_html($tag->name) . '</li>';
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
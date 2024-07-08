<?php
/*
Template Name: Novel Parents Page
*/
get_header(); ?>

<div class="novel-wrapper">
    <h2 class="novel-list-title">小説一覧</h2>
    
    <div class="novel-search-sort">
        <div class="novel-search">
            <input type="text" id="novel-search-input" placeholder="小説を検索">
            <button id="novel-search-button">検索</button>
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
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        $novel_parents = new WP_Query($args);

        if ($novel_parents->have_posts()) :
            while ($novel_parents->have_posts()) : $novel_parents->the_post();
                ?>
                <li class="novel-item">
                    <div class="novel-item-header">
                        <h3 class="novel-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <?php echo do_shortcode('[favorite_button novel_id="' . get_the_ID() . '"]'); ?>
                    </div>
                    <p class="novel-item-description"><?php echo wp_trim_words(get_the_excerpt(), 100, "..."); ?></p>
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
            echo '<p>小説がありません。</p>';
        endif;
        ?>
    </ul>
</div>

<?php get_footer(); ?>
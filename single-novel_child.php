<?php get_header(); ?>

<div class="novel-wrapper">
    <div class="novel-navigation top">
        <?php
        $parent_id = get_post_meta(get_the_ID(), 'parent_novel', true);
        $siblings = get_posts(array(
            'post_type' => 'novel_child',
            'meta_query' => array(
                array(
                    'key' => 'parent_novel',
                    'value' => $parent_id,
                ),
            ),
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'posts_per_page' => -1,
        ));

        $current_index = array_search(get_the_ID(), wp_list_pluck($siblings, 'ID'));
        $prev_post = $current_index > 0 ? $siblings[$current_index - 1] : null;
        $next_post = $current_index < count($siblings) - 1 ? $siblings[$current_index + 1] : null;

        if ($prev_post) {
            echo '<a href="' . get_permalink($prev_post->ID) . '" class="nav-button">前へ</a>';
        } else {
            echo '<span class="nav-button disabled">前へ</span>';
        }
        echo '<a href="' . get_permalink($parent_id) . '" class="nav-button">目次</a>';
        if ($next_post) {
            echo '<a href="' . get_permalink($next_post->ID) . '" class="nav-button">次へ</a>';
        } else {
            echo '<span class="nav-button disabled">次へ</span>';
        }
        ?>
    </div>
    
    <h1 class="novel-title"><?php the_title(); ?></h1>
    
    <div class="novel-content">
        <?php the_content(); ?>
    </div>
    
    <div class="novel-navigation bottom">
        <?php
        if ($prev_post) {
            echo '<a href="' . get_permalink($prev_post->ID) . '" class="nav-button">前へ</a>';
        } else {
            echo '<span class="nav-button disabled">前へ</span>';
        }
        echo '<a href="' . get_permalink($parent_id) . '" class="nav-button">目次</a>';
        if ($next_post) {
            echo '<a href="' . get_permalink($next_post->ID) . '" class="nav-button">次へ</a>';
        } else {
            echo '<span class="nav-button disabled">次へ</span>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>
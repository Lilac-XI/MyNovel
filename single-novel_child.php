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
        ?>
        <table class="nav-table">
            <tr>
                <td class="nav-cell prev">
                    <?php if ($prev_post): ?>
                        <a href="<?php echo get_permalink($prev_post->ID); ?>" class="nav-link nav-reset"><< 前の話</a>
                    <?php else: ?>
                        <span class="nav-link disabled"><< 前の話</span>
                    <?php endif; ?>
                </td>
                <td class="nav-cell toc">
                    <a href="<?php echo get_permalink($parent_id); ?>" class="nav-link nav-reset">目次</a>
                </td>
                <td class="nav-cell next">
                    <?php if ($next_post): ?>
                        <a href="<?php echo get_permalink($next_post->ID); ?>" class="nav-link nav-reset">次の話 >></a>
                    <?php else: ?>
                        <span class="nav-link disabled">次の話 >></span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <h1 class="novel-title"><?php the_title(); ?></h1>
    
    <div class="novel-content">
        <?php the_content(); ?>
    </div>
    
    <div class="novel-navigation bottom">
        <table class="nav-table">
            <tr>
                <td class="nav-cell prev">
                    <?php if ($prev_post): ?>
                        <a href="<?php echo get_permalink($prev_post->ID); ?>" class="nav-link nav-reset"><< 前の話</a>
                    <?php else: ?>
                        <span class="nav-link disabled"><< 前の話</span>
                    <?php endif; ?>
                </td>
                <td class="nav-cell toc">
                    <a href="<?php echo get_permalink($parent_id); ?>" class="nav-link nav-reset">目次</a>
                </td>
                <td class="nav-cell next">
                    <?php if ($next_post): ?>
                        <a href="<?php echo get_permalink($next_post->ID); ?>" class="nav-link nav-reset">次の話 >></a>
                    <?php else: ?>
                        <span class="nav-link disabled">次の話 >></span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php get_footer(); ?>
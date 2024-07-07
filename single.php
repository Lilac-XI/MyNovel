<?php get_header(); ?>
<div class="content">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <h2><?php the_title(); ?></h2>
        <div class="post-content">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>
</div>

<div class="novel-parents">
    <h2>Novel Parents</h2>
    <ul>
        <?php
        $args = array(
            'post_type' => 'novel_parent',
            'posts_per_page' => -1
        );
        $novel_parents = new WP_Query( $args );
        if ( $novel_parents->have_posts() ) :
            while ( $novel_parents->have_posts() ) : $novel_parents->the_post();
        ?>
                <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
        <?php
            endwhile;
            wp_reset_postdata();
        else :
        ?>
            <li><?php _e( 'No Novel Parents found', 'textdomain' ); ?></li>
        <?php endif; ?>
    </ul>
</div>

<?php get_footer(); ?>
<?php get_header(); ?>

<div class="novel-wrapper">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h1 class="novel-title"><?php the_title(); ?></h1>
        
        <div class="novel-content">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
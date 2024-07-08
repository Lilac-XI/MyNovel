<?php get_header(); ?>

<div class="novel-wrapper novel-parent">
    <h1 class="novel-title"><?php the_title(); ?></h1>
    
    <div class="novel-description">
        <?php the_content(); ?>
    </div>
</div>

<?php get_footer(); ?>
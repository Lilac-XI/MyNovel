<?php get_header(); ?>

<div class="novel-wrapper">
    <h1 class="novel-title"><?php the_title(); ?></h1>
    <div class="novel-content">
        <?php the_content(); ?>
    </div>
</div>

<?php get_footer(); ?>
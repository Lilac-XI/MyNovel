<?php get_header(); ?>
<div class="content">
    <h2><?php post_type_archive_title(); ?></h2>
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div class="post-content">
            <?php the_excerpt(); ?>
        </div>
    <?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>
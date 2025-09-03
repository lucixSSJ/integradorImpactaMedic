<?php

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that other
 * 'pages' on your WordPress site will use a different template.
 *
 */

get_header('tarjeta-digital');
?>

<div class="site-content" role="main">

    <?php while (have_posts()) : the_post(); ?>

        <?php get_template_part('content', 'without-header'); ?>

    <?php endwhile; ?>

</div><!-- .site-content -->

<?php get_footer('tarjeta-digital'); ?>
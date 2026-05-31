<?php get_header(); ?>

<main class="container">
    <section class="games-section">
        <div class="section-header">
            <h1><?php the_title(); ?></h1>
        </div>
        <div class="page-content">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    the_content();
                endwhile;
            endif;
            ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>

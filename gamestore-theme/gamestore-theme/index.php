<?php get_header(); ?>

<main class="container">
    <section class="games-section">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="section-header">
                        <h2><?php the_title(); ?></h2>
                    </div>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else : ?>
            <p><?php esc_html_e( 'Ничего не найдено.', 'gamestore' ); ?></p>
        <?php endif; ?>
    </section>
</main>

<?php get_footer(); ?>

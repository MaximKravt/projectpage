<?php get_header(); ?>

<main class="container">
    <section class="games-section news-archive">
        <div class="section-header">
            <h1><?php single_cat_title(); ?></h1>
        </div>

        <div class="news-grid">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="news-card">
                        <a href="<?php the_permalink(); ?>" class="news-link">
                            <div class="news-img">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium' ); ?>
                                <?php else : ?>
                                    <img src="https://images.unsplash.com/photo-1538481199705-c710c4e965fc?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="<?php the_title(); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="news-info">
                                <span class="news-date"><?php echo get_the_date(); ?></span>
                                <h3><?php the_title(); ?></h3>
                                <p><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e( 'Новостей пока нет.', 'gamestore' ); ?></p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php
            echo paginate_links( array(
                'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                'type'      => 'list',
            ) );
            ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>

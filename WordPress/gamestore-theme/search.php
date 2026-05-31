<?php get_header(); ?>

<main class="container">
    <section class="games-section search-results">
        <div class="section-header">
            <h1><?php printf( esc_html__( 'Результаты поиска: %s', 'gamestore' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
        </div>

        <div class="games-grid">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php if ( get_post_type() === 'product' ) : 
                        global $product; ?>
                        <div class="game-card">
                            <?php if ( function_exists( 'gamestore_render_wishlist_button' ) ) { gamestore_render_wishlist_button( get_the_ID() ); } ?>
                            <a href="<?php the_permalink(); ?>" class="game-link">
                                <div class="game-img">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'medium' ); ?>
                                    <?php else : ?>
                                        <img src="<?php echo wc_placeholder_img_src(); ?>" alt="<?php the_title(); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="game-info">
                                    <h3><?php the_title(); ?></h3>
                                    <p class="developer">
                                        <?php
                                        $developer = get_post_meta( get_the_ID(), '_developer', true );
                                        if ( ! $developer ) {
                                            $terms = get_the_terms( get_the_ID(), 'pa_developer' );
                                            if ( $terms && ! is_wp_error( $terms ) ) {
                                                $developer = $terms[0]->name;
                                            }
                                        }
                                        echo $developer ? esc_html( $developer ) : '&nbsp;';
                                        ?>
                                    </p>
                                    <p class="price"><?php echo $product->get_price_html(); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php else : ?>
                        <!-- Fallback for non-product posts (like news) in search -->
                        <div class="game-card news-card-search">
                            <a href="<?php the_permalink(); ?>" class="game-link">
                                <div class="game-img">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'medium' ); ?>
                                    <?php else : ?>
                                        <img src="https://images.unsplash.com/photo-1538481199705-c710c4e965fc?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="<?php the_title(); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="game-info">
                                    <span class="news-date"><?php echo get_the_date(); ?></span>
                                    <h3><?php the_title(); ?></h3>
                                    <p><?php echo wp_trim_words( get_the_excerpt(), 10 ); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e( 'По вашему запросу ничего не найдено.', 'gamestore' ); ?></p>
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

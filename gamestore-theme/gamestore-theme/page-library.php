<?php
/**
 * Template Name: Library Page
 */
get_header(); ?>

<main class="container">
    <section class="games-section library-archive">
        <div class="section-header">
            <h1><?php the_title(); ?></h1>
        </div>

        <div class="games-grid">
            <?php
            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 25,
                'paged'          => $paged,
                'orderby'        => 'title',
                'order'          => 'ASC'
            );
            $loop = new WP_Query( $args );

            if ( $loop->have_posts() ) :
                while ( $loop->have_posts() ) : $loop->the_post();
                    global $product;
                    ?>
                    <div class="game-card">
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
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e( 'Игры не найдены.', 'gamestore' ); ?></p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php
            echo paginate_links( array(
                'total'     => $loop->max_num_pages,
                'current'   => $paged,
                'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                'type'      => 'list',
            ) );
            wp_reset_postdata();
            ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>

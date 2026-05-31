<?php get_header(); ?>

<main class="container">
    <!-- Hero Section -->
    <section class="hero-section hero-slider">
        <div class="slider-container">
            <?php
            $news_args = array(
                'category_name'  => 'news',
                'posts_per_page' => 3,
            );
            $news_query = new WP_Query( $news_args );
            $count = 0;

            if ( $news_query->have_posts() ) :
                while ( $news_query->have_posts() ) : $news_query->the_post();
                    $bg_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
                    if ( ! $bg_url ) {
                        $bg_url = 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';
                    }
                    ?>
                    <div class="hero-banner slide <?php echo $count === 0 ? 'active' : ''; ?>" 
                         style="background-image: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.8) 100%), url('<?php echo esc_url( $bg_url ); ?>');">
                        <div class="hero-content">
                            <h1><?php the_title(); ?></h1>
                            <p><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
                        </div>
                        <div class="hero-side">
                            <span class="badge"><?php echo esc_html__( 'Новости', 'gamestore' ); ?></span>
                            <div class="hero-actions">
                                <a href="<?php the_permalink(); ?>" class="btn btn-primary"><?php echo esc_html__( 'Читать', 'gamestore' ); ?></a>
                            </div>
                        </div>
                    </div>
                    <?php
                    $count++;
                endwhile;
                wp_reset_postdata();
            else :
                // Fallback if no posts in 'news'
                ?>
                <div class="hero-banner slide active" style="background-image: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.8) 100%), url('https://images.unsplash.com/photo-1538481199705-c710c4e965fc?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');">
                    <div class="hero-content">
                        <h1>Star Wars Jedi: Fallen Order</h1>
                        <p>Вам предстоит очутиться в роли джедая-падавана, которому едва удалось избежать уничтожения, санкционированного Приказом 66.</p>
                    </div>
                    <div class="hero-side">
                        <span class="badge">Новинка</span>
                        <div class="hero-actions">
                            <button class="btn btn-primary">Купить</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $count > 1 || ! $news_query->have_posts() ) : ?>
        <div class="hero-controls">
            <button class="slider-prev" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="slider-next" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <div class="hero-nav">
            <?php 
            $dot_count = $count > 0 ? $count : 3;
            for ( $i = 0; $i < $dot_count; $i++ ) : ?>
                <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></span>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- New Games -->
    <section class="games-section" id="new-games">
        <div class="section-header">
            <h2>Новые игры</h2>
            <div class="section-actions">
                <i class="fa-solid fa-bars"></i>
                <i class="fa-solid fa-table-cells-large active"></i>
            </div>
        </div>
        <div class="games-grid">
            <?php
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 5,
                'orderby'        => 'date',
                'order'          => 'DESC'
            );
            $loop = new WP_Query( $args );
            if ( $loop->have_posts() ) {
                while ( $loop->have_posts() ) : $loop->the_post();
                    global $product;
                    ?>
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
                                <?php if ( function_exists( 'gamestore_render_platform_icons' ) ) { gamestore_render_platform_icons( get_the_ID() ); } ?>
                                <h3><?php the_title(); ?></h3>
                                <p class="developer">
                                    <?php
                                    // Assuming developer is a custom field or attribute
                                    $developer = get_post_meta( get_the_ID(), '_developer', true );
                                    if ( ! $developer ) {
                                        $terms = get_the_terms( get_the_ID(), 'pa_developer' ); // Try attribute
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
                    <?php
                endwhile;
            } else {
                echo '<p>Товары не найдены</p>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </section>

    <!-- Sales Section -->
    <section class="games-section" id="sales">
        <div class="section-header">
            <h2>Скидки и распродажи</h2>
        </div>
        <div class="games-grid">
            <?php
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 5,
                'meta_query'     => WC()->query->get_meta_query(),
                'post__in'       => array_merge( array( 0 ), wc_get_product_ids_on_sale() )
            );
            $loop = new WP_Query( $args );
            if ( $loop->have_posts() ) {
                while ( $loop->have_posts() ) : $loop->the_post();
                    global $product;
                    ?>
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
                                <div class="price-wrapper">
                                    <p class="price"><?php echo $product->get_price_html(); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php
                endwhile;
            } else {
                echo '<p>Товары со скидкой не найдены</p>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </section>

    <!-- Secondary Hero -->
    <section class="hero-section secondary">
        <div class="hero-banner" style="background-image: linear-gradient(to right, rgba(0,0,0,0.8) 20%, transparent), url('https://images.unsplash.com/photo-1533236897111-3e94666b2edf?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');">
            <div class="hero-content">
                <span class="badge">Выбор редакции</span>
                <h1>Battlefield 5: издание второго года</h1>
                <p>Приготовьтесь к разрушительной «королевской битве» под названием «Огненный шторм». Участвуйте в крупнейшем военном конфликте в истории в Battlefield V.</p>
                <div class="hero-actions">
                    <button class="btn btn-primary">Купить</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Best Sellers -->
    <section class="games-section" id="best-sellers">
        <div class="section-header">
            <h2>Лидеры продаж</h2>
        </div>
        <div class="games-grid">
            <?php
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 5,
                'meta_key'       => 'total_sales',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC'
            );
            $loop = new WP_Query( $args );
            if ( $loop->have_posts() ) {
                while ( $loop->have_posts() ) : $loop->the_post();
                    global $product;
                    ?>
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
                    <?php
                endwhile;
            } else {
                echo '<p>Лидеры продаж не найдены</p>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </section>

    <!-- Promo Blocks -->
    <section class="promo-section">
        <div class="promo-grid">
            <?php
            $daily_deal_args = array(
                'post_type'      => 'product',
                'posts_per_page' => 1,
                'meta_query'     => array(
                    array(
                        'key'     => '_is_daily_deal',
                        'value'   => 'yes',
                        'compare' => '='
                    )
                ),
                'orderby' => 'modified',
                'order'   => 'DESC'
            );
            $daily_deal_query = new WP_Query( $daily_deal_args );

            if ( $daily_deal_query->have_posts() ) :
                while ( $daily_deal_query->have_posts() ) : $daily_deal_query->the_post();
                    global $product;
                    $bg_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
                    $price_html = $product->get_price_html();
                    $sale_price = $product->get_sale_price();
                    $regular_price = $product->get_regular_price();
                    $discount = 0;
                    if ( $regular_price && $sale_price ) {
                        $discount = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
                    }
                    ?>
                    <div class="promo-block daily-deal" style="background-image: linear-gradient(to right, rgba(0,0,0,0.9) 30%, rgba(0,0,0,0.2) 100%), url('<?php echo esc_url( $bg_url ); ?>');">
                        <div class="promo-content">
                            <span class="badge deal-badge"><?php esc_html_e( 'Скидка дня', 'gamestore' ); ?></span>
                            <h2><?php the_title(); ?></h2>
                            <p><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
                            
                            <?php if ( $discount > 0 ) : ?>
                                <div class="discount-pill">-<?php echo $discount; ?>%</div>
                            <?php endif; ?>

                            <div class="deal-actions">
                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                    <?php 
                                    $active_price = strip_tags( wc_price( $product->get_price() ) );
                                    printf( esc_html__( 'Купить за %s', 'gamestore' ), $active_price ); 
                                    ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                // Fallback static block if no product is marked
                ?>
                <div class="promo-block rdr2">
                    <div class="promo-content">
                        <span class="badge">Лидер продаж</span>
                        <h2>RED DEAD REDEMPTION 2</h2>
                        <p>Red Dead Redemption 2 — грандиозная сага от Rockstar Games и самая высокооцененная игра на текущем поколении консолей, выходит на ПК.</p>
                        <a href="#" class="btn-link">Читать далее</a>
                    </div>
                    <div class="promo-footer">
                        <span class="rating">10 из 10!</span>
                    </div>
                </div>
            <?php endif; ?>
            <?php get_template_part( 'template-parts/promo', 'accessories' ); ?>
        </div>
    </section>

    <!-- Pre-orders -->
    <section class="games-section" id="pre-orders">
        <div class="section-header">
            <h2>Предзаказы</h2>
        </div>
        <div class="games-grid">
            <?php
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 5,
                'meta_query'     => array(
                    array(
                        'key'     => '_is_preorder',
                        'value'   => 'yes',
                        'compare' => '='
                    )
                )
            );
            $loop = new WP_Query( $args );
            if ( $loop->have_posts() ) {
                while ( $loop->have_posts() ) : $loop->the_post();
                    global $product;
                    ?>
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
                    <?php
                endwhile;
            } else {
                echo '<p>Предзаказы не найдены</p>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>

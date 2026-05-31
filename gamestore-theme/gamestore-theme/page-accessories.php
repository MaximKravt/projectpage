<?php
/**
 * Template Name: Страница аксессуаров
 * Description: Каталог аксессуаров с фильтрацией по категориям и брендам.
 */
get_header();

$root_id     = gamestore_get_accessories_root_category_id();
$filters     = gamestore_get_accessories_filters_from_request();
$brand_tax   = gamestore_get_accessories_brand_taxonomy();
$subcats     = gamestore_get_accessories_filter_categories( $root_id );
$brands      = gamestore_get_accessories_brand_terms( $root_id, $brand_tax );
$query_args  = gamestore_build_accessories_query_args( $filters );
$loop        = new WP_Query( $query_args );
$filter_base = gamestore_get_accessories_page_url() ? gamestore_get_accessories_page_url() : get_permalink();
?>

<main class="container">
    <section class="games-section accessories-archive">
        <div class="section-header">
            <h1><?php the_title(); ?></h1>
        </div>

        <?php if ( ! $root_id ) : ?>
            <p class="accessories-notice">
                <?php
                printf(
                    wp_kses(
                        __( 'Укажите категорию «игровые аксесуары» в <a href="%s">настройках темы</a> (Внешний вид → Аксессуары на главной).', 'gamestore' ),
                        array( 'a' => array( 'href' => array() ) )
                    ),
                    esc_url( admin_url( 'themes.php?page=gamestore-accessories-promo' ) )
                );
                ?>
            </p>
        <?php endif; ?>

        <div class="accessories-layout">
            <div class="accessories-main">
                <?php
                if ( $root_id && current_user_can( 'manage_options' ) ) :
                    $root_term = get_term( $root_id, 'product_cat' );
                    $count     = gamestore_count_accessories_category_products( $root_id );
                    if ( $root_term && ! is_wp_error( $root_term ) ) :
                        ?>
                        <p class="accessories-admin-hint">
                            <?php
                            printf(
                                esc_html__( 'Каталог: категория «%1$s» (ID %2$d), товаров: %3$d.', 'gamestore' ),
                                $root_term->name,
                                $root_id,
                                $count
                            );
                            ?>
                        </p>
                        <?php
                    endif;
                endif;
                ?>

                <div class="games-grid accessories-grid">
                    <?php
                    if ( $loop->have_posts() ) :
                        while ( $loop->have_posts() ) :
                            $loop->the_post();
                            gamestore_render_product_card();
                        endwhile;
                    else :
                        ?>
                        <p class="accessories-empty"><?php esc_html_e( 'Товары не найдены. Измените фильтры или добавьте товары в выбранную категорию.', 'gamestore' ); ?></p>
                        <?php
                    endif;
                    ?>
                </div>

                <?php
                wp_reset_postdata();

                if ( $loop->max_num_pages > 1 ) :
                    ?>
                    <div class="pagination">
                        <?php
                        echo paginate_links(
                            array(
                                'total'     => $loop->max_num_pages,
                                'current'   => max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
                                'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                                'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                                'type'      => 'list',
                                'add_args'  => array_filter(
                                    array(
                                        'filter_cat'   => $filters['cat'],
                                        'filter_brand' => $filters['brand'],
                                        'orderby'      => 'title' !== $filters['orderby'] ? $filters['orderby'] : '',
                                    )
                                ),
                            )
                        );
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( $root_id ) : ?>
                <aside class="accessories-sidebar">
                    <form class="accessories-filters" method="get" action="<?php echo esc_url( $filter_base ); ?>">
                        <div class="accessories-filters__head">
                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                            <h2><?php esc_html_e( 'Фильтры', 'gamestore' ); ?></h2>
                        </div>

                        <div class="accessories-filters__body">
                            <label class="accessories-filters__field">
                                <span class="accessories-filters__label"><?php esc_html_e( 'Категория', 'gamestore' ); ?></span>
                                <select name="filter_cat">
                                    <option value=""><?php esc_html_e( 'Все категории', 'gamestore' ); ?></option>
                                    <?php foreach ( $subcats as $term ) : ?>
                                        <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $filters['cat'], $term->slug ); ?>>
                                            <?php echo esc_html( $term->name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <?php if ( $brand_tax && ! empty( $brands ) ) : ?>
                                <label class="accessories-filters__field">
                                    <span class="accessories-filters__label"><?php esc_html_e( 'Бренд', 'gamestore' ); ?></span>
                                    <select name="filter_brand">
                                        <option value=""><?php esc_html_e( 'Все бренды', 'gamestore' ); ?></option>
                                        <?php foreach ( $brands as $term ) : ?>
                                            <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $filters['brand'], $term->slug ); ?>>
                                                <?php echo esc_html( $term->name ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            <?php elseif ( ! $brand_tax ) : ?>
                                <p class="accessories-filters__hint">
                                    <?php esc_html_e( 'Для фильтра по брендам создайте атрибут «Бренд» в WooCommerce.', 'gamestore' ); ?>
                                </p>
                            <?php endif; ?>

                            <label class="accessories-filters__field">
                                <span class="accessories-filters__label"><?php esc_html_e( 'Сортировка', 'gamestore' ); ?></span>
                                <select name="orderby">
                                    <option value="title" <?php selected( $filters['orderby'], 'title' ); ?>><?php esc_html_e( 'По названию (А–Я)', 'gamestore' ); ?></option>
                                    <option value="date" <?php selected( $filters['orderby'], 'date' ); ?>><?php esc_html_e( 'Сначала новые', 'gamestore' ); ?></option>
                                    <option value="price" <?php selected( $filters['orderby'], 'price' ); ?>><?php esc_html_e( 'Цена: по возрастанию', 'gamestore' ); ?></option>
                                    <option value="price-desc" <?php selected( $filters['orderby'], 'price-desc' ); ?>><?php esc_html_e( 'Цена: по убыванию', 'gamestore' ); ?></option>
                                </select>
                            </label>
                        </div>

                        <div class="accessories-filters__actions">
                            <button type="submit" class="btn btn-primary accessories-filters__submit"><?php esc_html_e( 'Применить', 'gamestore' ); ?></button>
                            <?php if ( $filters['cat'] || $filters['brand'] || 'title' !== $filters['orderby'] ) : ?>
                                <a href="<?php echo esc_url( $filter_base ); ?>" class="btn btn-secondary accessories-filters__reset"><?php esc_html_e( 'Сбросить', 'gamestore' ); ?></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </aside>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>

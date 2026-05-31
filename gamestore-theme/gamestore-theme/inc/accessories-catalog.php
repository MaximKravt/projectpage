<?php
/**
 * Catalog helpers for the accessories page (filters, queries).
 */

/**
 * Whether the given (or current) page is the accessories catalog page.
 *
 * @param int $post_id Optional page ID.
 * @return bool
 */
function gamestore_is_accessories_page( $post_id = 0 ) {
    $post_id = $post_id ? absint( $post_id ) : (int) get_queried_object_id();
    if ( ! $post_id ) {
        return false;
    }

    $post = get_post( $post_id );
    if ( ! $post || 'page' !== $post->post_type ) {
        return false;
    }

    $settings = gamestore_get_accessories_promo_settings();
    if ( ! empty( $settings['page_id'] ) && (int) $settings['page_id'] === $post_id ) {
        return true;
    }

    if ( 'page-accessories.php' === get_page_template_slug( $post_id ) ) {
        return true;
    }

    $slug_matches = array(
        'aksessuary-dlya-igrovyh-konsoley',
        'aksessuary-dlya-igrovyh-konsolej',
        'аксессуары-для-игровых-консолей',
        'aksessuary-dlya-igrovyh-konsolei',
    );
    if ( in_array( $post->post_name, $slug_matches, true ) ) {
        return true;
    }

    $title_lower = mb_strtolower( $post->post_title, 'UTF-8' );
    if (
        false !== mb_strpos( $title_lower, 'аксессуар', 0, 'UTF-8' ) &&
        false !== mb_strpos( $title_lower, 'консол', 0, 'UTF-8' )
    ) {
        return true;
    }

    return false;
}

add_filter( 'template_include', 'gamestore_accessories_force_template', 99 );
/**
 * Use the accessories catalog template even if not selected in the page editor.
 *
 * @param string $template Current template path.
 * @return string
 */
function gamestore_accessories_force_template( $template ) {
    if ( ! is_page() || ! gamestore_is_accessories_page() ) {
        return $template;
    }

    $catalog_template = get_template_directory() . '/page-accessories.php';
    if ( file_exists( $catalog_template ) ) {
        return $catalog_template;
    }

    return $template;
}

/**
 * Assign the accessories template to a page in the database.
 *
 * @param int $page_id Page ID.
 */
function gamestore_assign_accessories_page_template( $page_id ) {
    $page_id = absint( $page_id );
    if ( ! $page_id ) {
        return;
    }

    update_post_meta( $page_id, '_wp_page_template', 'page-accessories.php' );
}

/**
 * WooCommerce attribute taxonomy used as "brand" on the accessories page.
 *
 * @return string
 */
function gamestore_get_accessories_brand_taxonomy() {
    $settings = gamestore_get_accessories_promo_settings();

    if ( ! empty( $settings['brand_taxonomy'] ) && taxonomy_exists( $settings['brand_taxonomy'] ) ) {
        return $settings['brand_taxonomy'];
    }

    foreach ( array( 'pa_brand', 'pa_brend', 'pa_proizvoditel', 'pa_manufacturer' ) as $taxonomy ) {
        if ( taxonomy_exists( $taxonomy ) ) {
            return $taxonomy;
        }
    }

    return '';
}

/**
 * Permalink of the accessories landing page.
 *
 * @return string
 */
function gamestore_get_accessories_page_url() {
    $settings = gamestore_get_accessories_promo_settings();

    if ( ! empty( $settings['page_id'] ) ) {
        $url = get_permalink( (int) $settings['page_id'] );
        if ( $url ) {
            return $url;
        }
    }

    $template_pages = get_posts(
        array(
            'post_type'      => 'page',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_key'       => '_wp_page_template',
            'meta_value'     => 'page-accessories.php',
        )
    );
    if ( ! empty( $template_pages ) ) {
        return get_permalink( $template_pages[0]->ID );
    }

    $slug_candidates = array(
        'аксессуары-для-игровых-консолей',
        'aksessuary-dlya-igrovyh-konsoley',
        'aksessuary-dlya-igrovyh-konsolej',
    );
    $page = null;
    foreach ( $slug_candidates as $slug ) {
        $found = get_page_by_path( $slug );
        if ( $found ) {
            $page = $found;
            break;
        }
    }
    if ( ! $page ) {
        $pages = get_posts(
            array(
                'post_type'      => 'page',
                'title'          => 'Аксессуары для игровых консолей!',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
            )
        );
        $page = ! empty( $pages ) ? $pages[0] : null;
    }
    if ( $page ) {
        return get_permalink( $page->ID );
    }

    return '';
}

/**
 * Try to find the accessories product category by slug or name.
 *
 * @return int Term ID or 0.
 */
function gamestore_detect_accessories_category_id() {
    $slug_candidates = array(
        'igrovye-aksessuary',
        'igrovye-aksesuary',
        'igrovye-igrovye-aksesuary',
        'igrovye-aksessuary',
        'aksessuary',
        'aksesuary',
    );

    foreach ( $slug_candidates as $slug ) {
        $term = get_term_by( 'slug', $slug, 'product_cat' );
        if ( $term && ! is_wp_error( $term ) ) {
            return (int) $term->term_id;
        }
    }

    $terms = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        )
    );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return 0;
    }

    $fallback = 0;

    foreach ( $terms as $term ) {
        $name = mb_strtolower( $term->name, 'UTF-8' );

        if ( false !== mb_strpos( $name, 'аксес', 0, 'UTF-8' ) || false !== strpos( $name, 'accessori' ) ) {
            if ( false !== mb_strpos( $name, 'игров', 0, 'UTF-8' ) ) {
                return (int) $term->term_id;
            }
            if ( ! $fallback ) {
                $fallback = (int) $term->term_id;
            }
        }
    }

    return $fallback;
}

/**
 * Count published products in a category (including children).
 *
 * @param int $category_id Term ID.
 * @return int
 */
function gamestore_count_accessories_category_products( $category_id ) {
    $category_id = absint( $category_id );
    if ( ! $category_id ) {
        return 0;
    }

    $query = new WP_Query(
        array(
            'post_type'              => 'product',
            'post_status'            => 'publish',
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'no_found_rows'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'tax_query'              => gamestore_accessories_apply_wc_tax_query(
                array(
                    array(
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => $category_id,
                        'include_children' => true,
                    ),
                )
            ),
        )
    );

    return (int) $query->found_posts;
}

/**
 * Root WooCommerce category for accessories catalog.
 *
 * @return int
 */
function gamestore_get_accessories_root_category_id() {
    $settings_id = (int) gamestore_get_accessories_promo_settings()['category_id'];

    if ( $settings_id ) {
        $term = get_term( $settings_id, 'product_cat' );
        if ( $term && ! is_wp_error( $term ) ) {
            return $settings_id;
        }
    }

    return gamestore_detect_accessories_category_id();
}

/**
 * Append WooCommerce catalog visibility rules to a tax query.
 *
 * @param array $tax_query Tax query clauses (without relation).
 * @return array
 */
function gamestore_accessories_apply_wc_tax_query( $tax_query ) {
    if ( ! is_array( $tax_query ) ) {
        $tax_query = array();
    }

    if ( taxonomy_exists( 'product_visibility' ) ) {
        $tax_query[] = array(
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => array( 'exclude-from-catalog' ),
            'operator' => 'NOT IN',
        );
    }

    if ( count( $tax_query ) > 1 ) {
        return array_merge( array( 'relation' => 'AND' ), $tax_query );
    }

    return $tax_query;
}

/**
 * Active filter values from the request.
 *
 * @return array{cat: string, brand: string, orderby: string}
 */
function gamestore_get_accessories_filters_from_request() {
    $orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'title';
    $allowed = array( 'title', 'date', 'price', 'price-desc' );
    if ( ! in_array( $orderby, $allowed, true ) ) {
        $orderby = 'title';
    }

    return array(
        'cat'     => isset( $_GET['filter_cat'] ) ? sanitize_title( wp_unslash( $_GET['filter_cat'] ) ) : '',
        'brand'   => isset( $_GET['filter_brand'] ) ? sanitize_title( wp_unslash( $_GET['filter_brand'] ) ) : '',
        'orderby' => $orderby,
    );
}

/**
 * Child product categories for the filter dropdown.
 *
 * @param int $parent_id Root category term ID.
 * @return WP_Term[]
 */
function gamestore_get_accessories_subcategories( $parent_id ) {
    $parent_id = absint( $parent_id );
    if ( ! $parent_id ) {
        return array();
    }

    $terms = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'parent'     => $parent_id,
            'hide_empty' => true,
        )
    );

    if ( is_wp_error( $terms ) ) {
        return array();
    }

    if ( ! empty( $terms ) ) {
        return $terms;
    }

    $root = get_term( $parent_id, 'product_cat' );
    if ( ! $root || is_wp_error( $root ) ) {
        return array();
    }

    return array( $root );
}

/**
 * All categories that belong to the accessories catalog (root + children).
 *
 * @param int $root_id Root category term ID.
 * @return WP_Term[]
 */
function gamestore_get_accessories_filter_categories( $root_id ) {
    $root_id = absint( $root_id );
    if ( ! $root_id ) {
        return array();
    }

    $children = gamestore_get_accessories_subcategories( $root_id );
    $root     = get_term( $root_id, 'product_cat' );

    if ( ! $root || is_wp_error( $root ) ) {
        return $children;
    }

    $merged = array( $root->term_id => $root );
    foreach ( $children as $child ) {
        $merged[ $child->term_id ] = $child;
    }

    return array_values( $merged );
}

/**
 * Brand terms used by products in the accessories root category.
 *
 * @param int    $root_category_id Root category ID.
 * @param string $brand_taxonomy   Attribute taxonomy.
 * @return WP_Term[]
 */
function gamestore_get_accessories_brand_terms( $root_category_id, $brand_taxonomy ) {
    if ( ! $brand_taxonomy || ! $root_category_id ) {
        return array();
    }

    $product_ids = get_posts(
        array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => gamestore_accessories_apply_wc_tax_query(
                array(
                    array(
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => $root_category_id,
                        'include_children' => true,
                    ),
                )
            ),
        )
    );

    if ( empty( $product_ids ) ) {
        return array();
    }

    $terms = wp_get_object_terms( $product_ids, $brand_taxonomy, array( 'orderby' => 'name' ) );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return array();
    }

    $unique = array();
    foreach ( $terms as $term ) {
        $unique[ $term->term_id ] = $term;
    }

    return array_values( $unique );
}

/**
 * Build WP_Query args for the accessories catalog.
 *
 * @param array $filters From gamestore_get_accessories_filters_from_request().
 * @return array
 */
function gamestore_build_accessories_query_args( $filters ) {
    $root_id = gamestore_get_accessories_root_category_id();
    $paged   = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

    if ( ! $root_id ) {
        return array(
            'post_type'      => 'product',
            'posts_per_page' => 24,
            'paged'          => $paged,
            'post__in'       => array( 0 ),
        );
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 24,
        'paged'          => $paged,
        'post_status'    => 'publish',
    );

    switch ( $filters['orderby'] ) {
        case 'date':
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
        case 'price':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'ASC';
            break;
        case 'price-desc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'DESC';
            break;
        default:
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
    }

    $cat_clauses = array();

    if ( $root_id ) {
        if ( $filters['cat'] ) {
            $term = get_term_by( 'slug', $filters['cat'], 'product_cat' );
            if ( $term && ! is_wp_error( $term ) ) {
                $ancestors = get_ancestors( $term->term_id, 'product_cat' );
                if ( (int) $term->term_id === $root_id || in_array( $root_id, $ancestors, true ) ) {
                    $cat_clauses[] = array(
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => $term->term_id,
                        'include_children' => true,
                    );
                } else {
                    $cat_clauses[] = array(
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => $root_id,
                        'include_children' => true,
                    );
                }
            } else {
                $cat_clauses[] = array(
                    'taxonomy'         => 'product_cat',
                    'field'            => 'term_id',
                    'terms'            => $root_id,
                    'include_children' => true,
                );
            }
        } else {
            $cat_clauses[] = array(
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => $root_id,
                'include_children' => true,
            );
        }
    }

    $brand_tax = gamestore_get_accessories_brand_taxonomy();
    if ( $filters['brand'] && $brand_tax ) {
        $cat_clauses[] = array(
            'taxonomy' => $brand_tax,
            'field'    => 'slug',
            'terms'    => $filters['brand'],
        );
    }

    if ( ! empty( $cat_clauses ) ) {
        $args['tax_query'] = gamestore_accessories_apply_wc_tax_query( $cat_clauses );
    }

    return $args;
}

/**
 * URL for the accessories page with optional filter query args.
 *
 * @param array $params Query arguments.
 * @return string
 */
function gamestore_accessories_filter_url( $params = array() ) {
    $base = gamestore_get_accessories_page_url();
    if ( ! $base ) {
        $base = home_url( '/' );
    }

    $current = gamestore_get_accessories_filters_from_request();
    $merged  = array_merge(
        array(
            'filter_cat'   => $current['cat'],
            'filter_brand' => $current['brand'],
            'orderby'      => $current['orderby'],
        ),
        $params
    );

    foreach ( $merged as $key => $value ) {
        if ( '' === $value || null === $value ) {
            unset( $merged[ $key ] );
        }
    }

    return add_query_arg( $merged, $base );
}

/**
 * Render a single product card (shared markup).
 *
 * @param WC_Product|null $product Product object.
 */
function gamestore_render_product_card( $product = null ) {
    if ( ! $product ) {
        global $product;
    }
    ?>
    <div class="game-card">
        <?php if ( function_exists( 'gamestore_render_wishlist_button' ) ) { gamestore_render_wishlist_button( get_the_ID() ); } ?>
        <a href="<?php the_permalink(); ?>" class="game-link">
            <div class="game-img">
                <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'medium' ); ?>
                <?php else : ?>
                    <img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" alt="<?php the_title_attribute(); ?>">
                <?php endif; ?>
            </div>
            <div class="game-info">
                <h3><?php the_title(); ?></h3>
                <p class="developer">
                    <?php
                    $brand_tax = gamestore_get_accessories_brand_taxonomy();
                    $brand     = '';
                    if ( $brand_tax ) {
                        $brand_terms = get_the_terms( get_the_ID(), $brand_tax );
                        if ( $brand_terms && ! is_wp_error( $brand_terms ) ) {
                            $brand = $brand_terms[0]->name;
                        }
                    }
                    if ( ! $brand ) {
                        $developer = get_post_meta( get_the_ID(), '_developer', true );
                        if ( $developer ) {
                            $brand = $developer;
                        } else {
                            $dev_terms = get_the_terms( get_the_ID(), 'pa_developer' );
                            if ( $dev_terms && ! is_wp_error( $dev_terms ) ) {
                                $brand = $dev_terms[0]->name;
                            }
                        }
                    }
                    echo $brand ? esc_html( $brand ) : '&nbsp;';
                    ?>
                </p>
                <p class="price"><?php echo $product ? $product->get_price_html() : ''; ?></p>
            </div>
        </a>
    </div>
    <?php
}

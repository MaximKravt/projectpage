<?php
/**
 * GameStore theme functions and definitions
 */

if ( ! function_exists( 'gamestore_setup' ) ) :
    function gamestore_setup() {
        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        // Let WordPress manage the document title.
        add_theme_support( 'title-tag' );

        // Enable support for Post Thumbnails on posts and pages.
        add_theme_support( 'post-thumbnails' );

        // Add WooCommerce support
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );

        // This theme uses wp_nav_menu() in several locations.
        register_nav_menus( array(
            'main-menu'       => esc_html__( 'Main Menu', 'gamestore' ),
            'platforms-menu'  => esc_html__( 'Platforms Menu', 'gamestore' ),
            'categories-menu' => esc_html__( 'Categories Menu', 'gamestore' ),
            'footer-menu'     => esc_html__( 'Footer Menu', 'gamestore' ),
        ) );

        // Switch default core markup for search form, comment form, and comments to output valid HTML5.
        add_theme_support( 'html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ) );
    }
endif;
add_action( 'after_setup_theme', 'gamestore_setup' );

require get_template_directory() . '/inc/accessories-promo-settings.php';
require get_template_directory() . '/inc/accessories-catalog.php';
require get_template_directory() . '/inc/social-links-settings.php';
require get_template_directory() . '/inc/wishlist.php';

/**
 * Enqueue scripts and styles.
 */
function gamestore_scripts() {
    // Fonts
    wp_enqueue_style( 'gamestore-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), null );
    
    // Font Awesome
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0' );

    // Theme stylesheet
    wp_enqueue_style( 'gamestore-style', get_stylesheet_uri(), array(), '1.0.0' );

    // Theme script
    wp_enqueue_script( 'gamestore-script', get_template_directory_uri() . '/js/script.js', array(), '1.0.0', true );

    // AJAX Search
    wp_localize_script(
        'gamestore-script',
        'gamestoreAjax',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'gamestore_search_nonce' ),
        )
    );

    // Wishlist
    wp_enqueue_script(
        'gamestore-wishlist',
        get_template_directory_uri() . '/js/wishlist.js',
        array(),
        '1.0.0',
        true
    );
    wp_localize_script(
        'gamestore-wishlist',
        'gamestoreWishlist',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'gamestore_wishlist' ),
        )
    );

    if ( function_exists( 'gamestore_is_accessories_page' ) && gamestore_is_accessories_page() ) {
        wp_enqueue_script(
            'gamestore-accessories-filter',
            get_template_directory_uri() . '/js/accessories-filter.js',
            array(),
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'gamestore_scripts' );

/**
 * Fallback for Main Menu
 */
function gamestore_main_menu_fallback() {
    $news_cat = get_category_by_slug('news');
    $news_link = $news_cat ? get_category_link($news_cat->term_id) : home_url('/news/');
    
    $library_page = get_page_by_path('library');
    $library_link = $library_page ? get_permalink($library_page->ID) : home_url('/library/');
    ?>
    <ul class="nav-links">
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Магазин</a></li>
        <li><a href="<?php echo esc_url( $library_link ); ?>">Библиотека</a></li>
        <li><a href="<?php echo esc_url( $news_link ); ?>">Новости</a></li>
        <li><a href="#">Как купить?</a></li>
        <li><a href="#">Доставка</a></li>
        <li><a href="#">Оплата</a></li>
        <li><a href="#">Контакты</a></li>
    </ul>
    <?php
}

/**
 * Custom Breadcrumbs
 */
function gamestore_breadcrumbs() {
    if ( is_front_page() ) {
        return;
    }

    echo '<nav class="breadcrumbs container" aria-label="Breadcrumb">';
    echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Главная', 'gamestore' ) . '</a>';

    if ( is_category() || is_single() ) {
        echo '<span class="sep">/</span>';
        if ( is_category() ) {
            single_cat_title();
        } elseif ( is_singular( 'product' ) ) {
            $terms = get_the_terms( get_the_ID(), 'product_cat' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                echo '<a href="' . esc_url( get_term_link( $terms[0]->term_id, 'product_cat' ) ) . '">' . esc_html( $terms[0]->name ) . '</a>';
                echo '<span class="sep">/</span>';
            }
            the_title();
        } elseif ( is_single() ) {
            the_category( ', ' );
            echo '<span class="sep">/</span>';
            the_title();
        }
    } elseif ( is_page() ) {
        echo '<span class="sep">/</span>';
        the_title();
    } elseif ( is_search() ) {
        echo '<span class="sep">/</span>';
        printf( esc_html__( 'Результаты поиска: %s', 'gamestore' ), get_search_query() );
    }

    echo '</nav>';
}

/**
 * AJAX Search Handler
 */
add_action( 'wp_ajax_gamestore_ajax_search', 'gamestore_ajax_search_handler' );
add_action( 'wp_ajax_nopriv_gamestore_ajax_search', 'gamestore_ajax_search_handler' );
function gamestore_ajax_search_handler() {
    check_ajax_referer( 'gamestore_search_nonce', 'nonce' );

    $search_term = isset( $_POST['term'] ) ? sanitize_text_field( $_POST['term'] ) : '';

    if ( empty( $search_term ) ) {
        wp_send_json_success( array( 'html' => '' ) );
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 5,
        's'              => $search_term,
    );

    $query = new WP_Query( $args );
    $html = '';

    if ( $query->have_posts() ) {
        $html .= '<ul class="ajax-search-results">';
        while ( $query->have_posts() ) {
            $query->the_post();
            global $product;
            $html .= '<li>';
            $html .= '<a href="' . get_permalink() . '">';
            if ( has_post_thumbnail() ) {
                $html .= '<div class="result-img">' . get_the_post_thumbnail( get_the_ID(), array( 50, 50 ) ) . '</div>';
            }
            $html .= '<div class="result-info">';
            $html .= '<span class="result-title">' . get_the_title() . '</span>';
            $html .= '<span class="result-price">' . $product->get_price_html() . '</span>';
            $html .= '</div>';
            $html .= '</a>';
            $html .= '</li>';
        }
        $html .= '<li class="view-all"><a href="' . esc_url( home_url( '/?s=' . $search_term . '&post_type=product' ) ) . '">' . esc_html__( 'Показать все результаты', 'gamestore' ) . '</a></li>';
        $html .= '</ul>';
    } else {
        $html .= '<div class="no-results">' . esc_html__( 'Ничего не найдено', 'gamestore' ) . '</div>';
    }

    wp_reset_postdata();
    wp_send_json_success( array( 'html' => $html ) );
}

/**
 * Update mini cart fragments via AJAX
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'gamestore_cart_fragments' );
function gamestore_cart_fragments( $fragments ) {
    ob_start();
    ?>
    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'Корзина', 'gamestore' ); ?>" aria-label="<?php esc_attr_e( 'Перейти в корзину', 'gamestore' ); ?>" class="cart-contents">
        <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
        <?php if ( WC()->cart->get_cart_contents_count() > 0 ) : ?>
            <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
        <?php endif; ?>
    </a>
    <?php
    $fragments['a.cart-contents'] = ob_get_clean();

    return $fragments;
}

/**
 * Render platform icons for a product
 */
function gamestore_render_platform_icons( $product_id ) {
    $platforms = get_the_terms( $product_id, 'product_cat' ); // Можно заменить на pa_platform если есть атрибут
    if ( ! $platforms || is_wp_error( $platforms ) ) {
        return;
    }

    echo '<div class="platform-icons">';
    foreach ( $platforms as $platform ) {
        $slug = strtolower( $platform->slug );
        if ( strpos( $slug, 'win' ) !== false || strpos( $slug, 'pc' ) !== false ) {
            echo '<i class="fa-brands fa-windows" title="Windows"></i>';
        } elseif ( strpos( $slug, 'xbox' ) !== false ) {
            echo '<i class="fa-brands fa-xbox" title="Xbox"></i>';
        } elseif ( strpos( $slug, 'ps' ) !== false || strpos( $slug, 'playstation' ) !== false ) {
            echo '<i class="fa-brands fa-playstation" title="PlayStation"></i>';
        }
    }
    echo '</div>';
}

/**
 * WooCommerce wrappers
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

add_action('woocommerce_before_main_content', 'gamestore_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'gamestore_wrapper_end', 10);

function gamestore_wrapper_start() {
    $class = is_account_page() ? 'account-container' : 'games-section';
    echo '<main class="container"><section class="' . $class . '">';
}

function gamestore_wrapper_end() {
    echo '</section></main>';
}

/**
 * Enable registration on My Account page
 */
add_filter( 'woocommerce_checkout_registration_enabled', '__return_true' );
add_filter( 'woocommerce_can_customer_return_order', '__return_true' );


/**
 * Add Pre-order and Video URL fields to WooCommerce Product General Tab
 */
add_action( 'woocommerce_product_options_general_product_data', 'gamestore_add_custom_product_fields' );
function gamestore_add_custom_product_fields() {
    echo '<div class="options_group">';
    
    // Pre-order checkbox
    woocommerce_wp_checkbox( array(
        'id'            => '_is_preorder',
        'label'         => __( 'Предзаказ', 'gamestore' ),
        'description'   => __( 'Отметьте, если это товар по предзаказу', 'gamestore' ),
        'default'       => 'no',
    ) );

    // Daily Deal checkbox
    woocommerce_wp_checkbox( array(
        'id'            => '_is_daily_deal',
        'label'         => __( 'Скидка дня', 'gamestore' ),
        'description'   => __( 'Отобразить этот товар в баннере «Скидка дня» на главной', 'gamestore' ),
        'default'       => 'no',
    ) );

    // Video URL
    woocommerce_wp_text_input( array(
        'id'          => '_product_video_url',
        'label'       => __( 'Ссылка на видео (YouTube или MP4)', 'gamestore' ),
        'placeholder' => 'https://www.youtube.com/watch?v=...',
        'desc_tip'    => 'true',
        'description' => __( 'Введите ссылку на видео, которое будет отображаться вместо главного фото товара.', 'gamestore' ),
    ) );

    echo '</div>';
}

/**
 * Save custom product fields
 */
add_action( 'woocommerce_process_product_meta', 'gamestore_save_custom_product_fields' );
function gamestore_save_custom_product_fields( $post_id ) {
    // Nonce check for security
    if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
        return;
    }

    // Save Pre-order
    $is_preorder = isset( $_POST['_is_preorder'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_is_preorder', $is_preorder );

    // Save Daily Deal
    $is_daily_deal = isset( $_POST['_is_daily_deal'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_is_daily_deal', $is_daily_deal );

    // Save Video URL
    $video_url = isset( $_POST['_product_video_url'] ) ? $_POST['_product_video_url'] : '';
    if ( ! empty( $video_url ) ) {
        update_post_meta( $post_id, '_product_video_url', esc_url_raw( $video_url ) );
    } else {
        delete_post_meta( $post_id, '_product_video_url' );
    }
}

/**
 * Fallback for Platforms Menu
 */
function gamestore_platforms_fallback() {
    ?>
    <ul class="platform-links">
        <li><a href="#">Windows</a></li>
        <li><a href="#">Xbox One</a></li>
        <li><a href="#">PS4</a></li>
    </ul>
    <?php
}

/**
 * Fallback for Categories Menu
 */
function gamestore_categories_fallback() {
    $platforms = array(
        'ps'   => 'PS',
        'xbox' => 'XBOX',
        'pc'   => 'PC'
    );
    ?>
    <ul class="category-links">
        <?php foreach ( $platforms as $slug => $label ) : 
            $term = get_term_by( 'slug', $slug, 'product_cat' );
            if ( ! $term ) {
                // Try lowercase name if slug fails
                $term = get_term_by( 'name', $label, 'product_cat' );
            }
            $link = $term ? get_term_link( $term ) : home_url( '/product-category/' . $slug . '/' );
            ?>
            <li><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $label ); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php
}

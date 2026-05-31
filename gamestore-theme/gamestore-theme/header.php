<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header class="header">
        <div class="container">
            <nav class="main-nav">
                <div class="logo">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>">
                        <div class="logo-circle" aria-hidden="true"></div>
                        <span class="screen-reader-text"><?php bloginfo( 'name' ); ?></span>
                    </a>
                </div>

                <button class="mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Меню', 'gamestore' ); ?>" aria-expanded="false">
                    <span class="hamburger"></span>
                </button>

                <div class="main-menu-container">
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'main-menu',
                        'container'      => false,
                        'menu_class'     => 'nav-links',
                        'fallback_cb'    => 'gamestore_main_menu_fallback',
                    ) );
                    ?>
                </div>

                <div class="user-actions">
                    <div class="header-cart-wrapper">
                        <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'Корзина', 'gamestore' ); ?>" aria-label="<?php esc_attr_e( 'Перейти в корзину', 'gamestore' ); ?>" class="cart-contents">
                            <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
                            <?php if ( WC()->cart->get_cart_contents_count() > 0 ) : ?>
                                <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="widget_shopping_cart_content">
                            <?php woocommerce_mini_cart(); ?>
                        </div>
                    </div>
                    <?php
                    $wishlist_url   = function_exists( 'gamestore_get_wishlist_page_url' ) ? gamestore_get_wishlist_page_url() : '';
                    $wishlist_count = function_exists( 'gamestore_wishlist_get_ids' ) ? count( gamestore_wishlist_get_ids() ) : 0;
                    ?>
                    <a href="<?php echo esc_url( $wishlist_url ? $wishlist_url : home_url( '/' ) ); ?>" title="<?php esc_attr_e( 'Избранное', 'gamestore' ); ?>" class="wishlist-link" aria-label="<?php esc_attr_e( 'Перейти в избранное', 'gamestore' ); ?>">
                        <i class="fa-regular fa-heart" aria-hidden="true"></i>
                        <span class="wishlist-count" style="<?php echo $wishlist_count > 0 ? '' : 'display:none;'; ?>"><?php echo (int) $wishlist_count; ?></span>
                    </a>
                    <?php 
                    $account_link = get_permalink( get_option('woocommerce_myaccount_page_id') );
                    $login_class = ! is_user_logged_in() ? 'open-login-modal' : '';
                    ?>
                    <a href="<?php echo esc_url( $account_link ); ?>" class="<?php echo $login_class; ?>" title="<?php esc_attr_e( 'Мой аккаунт', 'gamestore' ); ?>" aria-label="<?php esc_attr_e( 'Мой аккаунт', 'gamestore' ); ?>">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <div class="sub-header">
        <div class="container">
            <div class="sub-nav-wrapper">
 
                <div class="categories">
                    <span class="label"><?php esc_html_e( 'По платформам:', 'gamestore' ); ?></span>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'categories-menu',
                        'container'      => false,
                        'menu_class'     => 'category-links',
                        'fallback_cb'    => 'gamestore_categories_fallback',
                    ) );
                    ?>
                </div>
                <div class="search-bar">
                    <form role="search" method="get" class="search-input" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <label for="game-search" class="label"><?php esc_html_e( 'Поиск по играм:', 'gamestore' ); ?></label>
                        <div class="search-input-wrapper">
                            <input type="text" id="game-search" name="s" placeholder="<?php esc_attr_e( 'Например: Battlefield 5', 'gamestore' ); ?>" value="<?php echo get_search_query(); ?>" autocomplete="off">
                            <input type="hidden" name="post_type" value="product" />
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <div id="search-results-container" class="ajax-search-container"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php 
    if ( function_exists( 'gamestore_breadcrumbs' ) ) {
        gamestore_breadcrumbs();
    }
    ?>

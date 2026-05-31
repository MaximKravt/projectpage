<?php
/**
 * Admin settings and helpers for the homepage accessories promo block.
 */

define( 'GAMESTORE_ACCESSORIES_OPTION', 'gamestore_accessories_promo' );

/**
 * Default settings for the accessories promo block.
 *
 * @return array
 */
function gamestore_accessories_promo_defaults() {
    return array(
        'enabled'          => '1',
        'badge'            => 'Новый раздел',
        'title'            => 'Аксессуары для игровых консолей!',
        'button_text'      => 'Перейти',
        'button_url'       => '',
        'page_id'          => 0,
        'category_id'      => 0,
        'products_count'   => 5,
        'background_id'    => 0,
        'brand_taxonomy'   => '',
    );
}

/**
 * @return array
 */
function gamestore_get_accessories_promo_settings() {
    $saved = get_option( GAMESTORE_ACCESSORIES_OPTION, array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }
    return wp_parse_args( $saved, gamestore_accessories_promo_defaults() );
}

/**
 * @param int $category_id Product category term ID.
 * @param int $limit       Max products.
 * @return WP_Post[]
 */
function gamestore_get_accessories_promo_products( $category_id, $limit = 5 ) {
    $category_id = absint( $category_id );
    if ( ! $category_id && function_exists( 'gamestore_get_accessories_root_category_id' ) ) {
        $category_id = gamestore_get_accessories_root_category_id();
    }
    $limit = max( 1, min( 20, absint( $limit ) ) );

    if ( ! $category_id || ! taxonomy_exists( 'product_cat' ) ) {
        return array();
    }

    $query = new WP_Query(
        array(
            'post_type'      => 'product',
            'posts_per_page' => $limit,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'tax_query'        => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_id,
                ),
            ),
        )
    );

    return $query->posts;
}

/**
 * Background style for the promo block.
 *
 * @param array $settings Promo settings.
 * @return string
 */
function gamestore_accessories_promo_background_style( $settings ) {
    $url = '';

    if ( ! empty( $settings['background_id'] ) ) {
        $url = wp_get_attachment_image_url( (int) $settings['background_id'], 'large' );
    }

    if ( $url ) {
        return sprintf(
            "background-image: linear-gradient(to right, rgba(0,0,0,0.7), transparent), url('%s');",
            esc_url( $url )
        );
    }

    return 'background-image: linear-gradient(to right, rgba(0,0,0,0.85), rgba(0,0,0,0.6));';
}

/**
 * Button URL: custom link, accessories page, or category archive.
 *
 * @param array $settings Promo settings.
 * @return string
 */
function gamestore_accessories_promo_button_url( $settings ) {
    if ( ! empty( $settings['button_url'] ) ) {
        return esc_url( $settings['button_url'] );
    }

    if ( function_exists( 'gamestore_get_accessories_page_url' ) ) {
        $page_url = gamestore_get_accessories_page_url();
        if ( $page_url ) {
            return esc_url( $page_url );
        }
    }

    $category_id = absint( $settings['category_id'] );
    if ( $category_id ) {
        $link = get_term_link( $category_id, 'product_cat' );
        if ( ! is_wp_error( $link ) ) {
            return esc_url( $link );
        }
    }

    return esc_url( home_url( '/' ) );
}

add_action( 'admin_menu', 'gamestore_accessories_promo_admin_menu' );
function gamestore_accessories_promo_admin_menu() {
    add_theme_page(
        __( 'Раздел «Аксессуары»', 'gamestore' ),
        __( 'Аксессуары на главной', 'gamestore' ),
        'manage_options',
        'gamestore-accessories-promo',
        'gamestore_accessories_promo_admin_page'
    );
}

add_action( 'admin_init', 'gamestore_accessories_promo_register_settings' );
add_action( 'admin_init', 'gamestore_accessories_maybe_assign_page_template' );
/**
 * Ensure the accessories page uses the catalog template after theme update.
 */
function gamestore_accessories_maybe_assign_page_template() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $settings = gamestore_get_accessories_promo_settings();
    if ( ! empty( $settings['page_id'] ) && function_exists( 'gamestore_assign_accessories_page_template' ) ) {
        gamestore_assign_accessories_page_template( (int) $settings['page_id'] );
        return;
    }

    if ( ! function_exists( 'gamestore_get_accessories_page_url' ) ) {
        return;
    }

    $url = gamestore_get_accessories_page_url();
    if ( ! $url ) {
        return;
    }

    $page_id = url_to_postid( $url );
    if ( $page_id ) {
        gamestore_assign_accessories_page_template( $page_id );
    }
}

function gamestore_accessories_promo_register_settings() {
    register_setting(
        'gamestore_accessories_promo_group',
        GAMESTORE_ACCESSORIES_OPTION,
        'gamestore_sanitize_accessories_promo_settings'
    );
}

/**
 * @param array $input Raw POST data.
 * @return array
 */
function gamestore_sanitize_accessories_promo_settings( $input ) {
    $defaults = gamestore_accessories_promo_defaults();
    $output   = array();

    $output['enabled'] = ! empty( $input['enabled'] ) ? '1' : '0';
    $output['badge']     = isset( $input['badge'] ) ? sanitize_text_field( $input['badge'] ) : $defaults['badge'];
    $output['title']     = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : $defaults['title'];
    $output['button_text'] = isset( $input['button_text'] ) ? sanitize_text_field( $input['button_text'] ) : $defaults['button_text'];
    $output['button_url']  = isset( $input['button_url'] ) ? esc_url_raw( $input['button_url'] ) : '';
    $output['category_id'] = isset( $input['category_id'] ) ? absint( $input['category_id'] ) : 0;

    $count = isset( $input['products_count'] ) ? absint( $input['products_count'] ) : 5;
    $output['products_count'] = max( 1, min( 20, $count ) );

    $output['background_id'] = isset( $input['background_id'] ) ? absint( $input['background_id'] ) : 0;
    $output['page_id'] = isset( $input['page_id'] ) ? absint( $input['page_id'] ) : 0;
    if ( $output['page_id'] && function_exists( 'gamestore_assign_accessories_page_template' ) ) {
        gamestore_assign_accessories_page_template( $output['page_id'] );
    }

    $brand_tax = isset( $input['brand_taxonomy'] ) ? sanitize_text_field( $input['brand_taxonomy'] ) : '';
    if ( $brand_tax && taxonomy_exists( $brand_tax ) ) {
        $output['brand_taxonomy'] = $brand_tax;
    } else {
        $output['brand_taxonomy'] = '';
    }

    return $output;
}

add_action( 'admin_enqueue_scripts', 'gamestore_accessories_promo_admin_assets' );
function gamestore_accessories_promo_admin_assets( $hook ) {
    if ( 'appearance_page_gamestore-accessories-promo' !== $hook ) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script(
        'gamestore-accessories-admin',
        get_template_directory_uri() . '/js/admin-accessories.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
}

function gamestore_accessories_promo_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $settings = gamestore_get_accessories_promo_settings();
    $bg_url   = ! empty( $settings['background_id'] )
        ? wp_get_attachment_image_url( (int) $settings['background_id'], 'medium' )
        : '';

    $categories = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        )
    );
    if ( is_wp_error( $categories ) ) {
        $categories = array();
    }

    $active_root_id = function_exists( 'gamestore_get_accessories_root_category_id' )
        ? gamestore_get_accessories_root_category_id()
        : (int) $settings['category_id'];
    $active_term    = $active_root_id ? get_term( $active_root_id, 'product_cat' ) : null;
    $product_count  = ( $active_root_id && function_exists( 'gamestore_count_accessories_category_products' ) )
        ? gamestore_count_accessories_category_products( $active_root_id )
        : 0;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Раздел «Аксессуары» на главной', 'gamestore' ); ?></h1>
        <p><?php esc_html_e( 'Настройки промо-блока «Аксессуары для игровых консолей» на главной странице.', 'gamestore' ); ?></p>

        <?php if ( $active_term && ! is_wp_error( $active_term ) ) : ?>
            <div class="notice notice-info">
                <p>
                    <?php
                    printf(
                        esc_html__( 'Сейчас используется категория: «%1$s» (ID %2$d). Опубликованных товаров в каталоге: %3$d.', 'gamestore' ),
                        esc_html( $active_term->name ),
                        (int) $active_root_id,
                        (int) $product_count
                    );
                    ?>
                </p>
            </div>
        <?php else : ?>
            <div class="notice notice-warning">
                <p><?php esc_html_e( 'Категория аксессуаров не выбрана. Выберите «игровые аксесуары» в списке ниже.', 'gamestore' ); ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'gamestore_accessories_promo_group' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Показывать блок', 'gamestore' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[enabled]" value="1" <?php checked( $settings['enabled'], '1' ); ?>>
                            <?php esc_html_e( 'Включить раздел на главной', 'gamestore' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="accessories-badge"><?php esc_html_e( 'Метка (badge)', 'gamestore' ); ?></label></th>
                    <td>
                        <input type="text" id="accessories-badge" class="regular-text" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[badge]" value="<?php echo esc_attr( $settings['badge'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="accessories-title"><?php esc_html_e( 'Заголовок', 'gamestore' ); ?></label></th>
                    <td>
                        <input type="text" id="accessories-title" class="large-text" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[title]" value="<?php echo esc_attr( $settings['title'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="accessories-category"><?php esc_html_e( 'Категория товаров', 'gamestore' ); ?></label></th>
                    <td>
                        <select id="accessories-category" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[category_id]">
                            <option value="0"><?php esc_html_e( '— Не выбрана —', 'gamestore' ); ?></option>
                            <?php foreach ( $categories as $term ) : ?>
                                <option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( (int) $settings['category_id'], (int) $term->term_id ); ?>>
                                    <?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Товары из этой категории WooCommerce отображаются в списке блока.', 'gamestore' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="accessories-count"><?php esc_html_e( 'Количество товаров', 'gamestore' ); ?></label></th>
                    <td>
                        <input type="number" id="accessories-count" min="1" max="20" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[products_count]" value="<?php echo esc_attr( $settings['products_count'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="accessories-page"><?php esc_html_e( 'Страница каталога', 'gamestore' ); ?></label></th>
                    <td>
                        <?php
                        wp_dropdown_pages(
                            array(
                                'name'              => GAMESTORE_ACCESSORIES_OPTION . '[page_id]',
                                'id'                => 'accessories-page',
                                'selected'          => (int) $settings['page_id'],
                                'show_option_none'  => __( '— Авто (по названию страницы) —', 'gamestore' ),
                                'option_none_value' => '0',
                            )
                        );
                        ?>
                        <p class="description"><?php esc_html_e( 'Страница с шаблоном «Страница аксессуаров». Кнопка «Перейти» на главной ведёт на неё.', 'gamestore' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="accessories-brand-tax"><?php esc_html_e( 'Атрибут «Бренд»', 'gamestore' ); ?></label></th>
                    <td>
                        <select id="accessories-brand-tax" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[brand_taxonomy]">
                            <option value=""><?php esc_html_e( '— Авто —', 'gamestore' ); ?></option>
                            <?php
                            if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
                                foreach ( wc_get_attribute_taxonomies() as $attribute ) {
                                    $taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
                                    if ( ! taxonomy_exists( $taxonomy ) ) {
                                        continue;
                                    }
                                    ?>
                                    <option value="<?php echo esc_attr( $taxonomy ); ?>" <?php selected( $settings['brand_taxonomy'], $taxonomy ); ?>>
                                        <?php echo esc_html( $attribute->attribute_label ); ?> (<?php echo esc_html( $taxonomy ); ?>)
                                    </option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Атрибут WooCommerce для фильтра по брендам на странице аксессуаров.', 'gamestore' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="accessories-button-text"><?php esc_html_e( 'Текст кнопки', 'gamestore' ); ?></label></th>
                    <td>
                        <input type="text" id="accessories-button-text" class="regular-text" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[button_text]" value="<?php echo esc_attr( $settings['button_text'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="accessories-button-url"><?php esc_html_e( 'Ссылка кнопки', 'gamestore' ); ?></label></th>
                    <td>
                        <input type="url" id="accessories-button-url" class="large-text" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[button_url]" value="<?php echo esc_attr( $settings['button_url'] ); ?>" placeholder="<?php esc_attr_e( 'По умолчанию — страница каталога аксессуаров', 'gamestore' ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Фоновое изображение', 'gamestore' ); ?></th>
                    <td>
                        <input type="hidden" id="accessories-bg-id" name="<?php echo esc_attr( GAMESTORE_ACCESSORIES_OPTION ); ?>[background_id]" value="<?php echo esc_attr( $settings['background_id'] ); ?>">
                        <div id="accessories-bg-preview" style="margin-bottom:10px;">
                            <?php if ( $bg_url ) : ?>
                                <img src="<?php echo esc_url( $bg_url ); ?>" alt="" style="max-width:320px;height:auto;border-radius:4px;">
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button" id="accessories-bg-upload"><?php esc_html_e( 'Выбрать изображение', 'gamestore' ); ?></button>
                        <button type="button" class="button" id="accessories-bg-remove" <?php echo $bg_url ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Удалить', 'gamestore' ); ?></button>
                        <p class="description"><?php esc_html_e( 'Фон промо-блока на главной странице. Рекомендуемый размер — не менее 600×350 px.', 'gamestore' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

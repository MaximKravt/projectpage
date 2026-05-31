<?php
/**
 * Homepage accessories promo block.
 */

$settings = gamestore_get_accessories_promo_settings();

if ( '1' !== $settings['enabled'] ) {
    return;
}

$products   = gamestore_get_accessories_promo_products( $settings['category_id'], $settings['products_count'] );
$bg_style   = gamestore_accessories_promo_background_style( $settings );
$button_url = gamestore_accessories_promo_button_url( $settings );
?>
<div class="promo-block accessories" id="accessories-promo" style="<?php echo esc_attr( $bg_style ); ?>">
    <div class="promo-content">
        <?php if ( $settings['badge'] ) : ?>
            <span class="badge"><?php echo esc_html( $settings['badge'] ); ?></span>
        <?php endif; ?>
        <?php if ( $settings['title'] ) : ?>
            <h2><?php echo esc_html( $settings['title'] ); ?></h2>
        <?php endif; ?>
        <?php if ( ! empty( $products ) ) : ?>
            <ul class="promo-list">
                <?php foreach ( $products as $product_post ) : ?>
                    <li>
                        <a href="<?php echo esc_url( get_permalink( $product_post->ID ) ); ?>">
                            <?php echo esc_html( get_the_title( $product_post->ID ) ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif ( (int) $settings['category_id'] ) : ?>
            <p class="promo-empty"><?php esc_html_e( 'В выбранной категории пока нет товаров.', 'gamestore' ); ?></p>
        <?php else : ?>
            <p class="promo-empty"><?php esc_html_e( 'Выберите категорию товаров в настройках темы.', 'gamestore' ); ?></p>
        <?php endif; ?>
        <?php if ( $settings['button_text'] ) : ?>
            <a href="<?php echo esc_url( $button_url ); ?>" class="btn btn-primary"><?php echo esc_html( $settings['button_text'] ); ?></a>
        <?php endif; ?>
    </div>
</div>

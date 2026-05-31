<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
		'woocommerce-product-gallery--columns-' . absint( $columns ),
		'images',
	)
);

// Получаем ссылку на видео из произвольного поля
$video_url = get_post_meta( get_the_ID(), '_product_video_url', true );
$attachment_ids = $product->get_gallery_image_ids();
?>
<div class="custom-product-media-container" style="opacity: 1;">
	
    <?php if ( $video_url ) : ?>
        <div class="product-video-wrapper">
            <?php
            if ( strpos( $video_url, 'youtube.com' ) !== false || strpos( $video_url, 'youtu.be' ) !== false ) {
                $video_id = '';
                if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match ) ) {
                    $video_id = $match[1];
                }
                if ( $video_id ) {
                    echo '<iframe width="100%" height="315" src="https://www.youtube.com/embed/' . esc_attr( $video_id ) . '?autoplay=1&mute=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                }
            } else {
                echo '<video width="100%" height="auto" controls autoplay muted loop>';
                echo '<source src="' . esc_url( $video_url ) . '" type="video/mp4">';
                echo '</video>';
            }
            ?>
        </div>
    <?php elseif ( $post_thumbnail_id ) : ?>
        <div class="product-main-image-wrapper">
            <?php echo get_the_post_thumbnail( get_the_ID(), 'full' ); ?>
        </div>
    <?php endif; ?>

    <?php if ( $attachment_ids ) : ?>
        <div class="custom-product-gallery">
            <div class="custom-gallery-list">
                <?php foreach ( $attachment_ids as $attachment_id ) : ?>
                    <div class="custom-gallery-item">
                        <a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" data-rel="prettyPhoto[product-gallery]">
                            <?php echo wp_get_attachment_image( $attachment_id, 'medium' ); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

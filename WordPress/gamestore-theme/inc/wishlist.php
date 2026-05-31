<?php
/**
 * Wishlist (favorites) implementation.
 *
 * - Logged-in users: stored in user meta `gamestore_wishlist`.
 * - Guests: stored in cookie `gamestore_wishlist` (JSON array).
 */

define( 'GAMESTORE_WISHLIST_META', 'gamestore_wishlist' );
define( 'GAMESTORE_WISHLIST_COOKIE', 'gamestore_wishlist' );

/**
 * @return int[]
 */
function gamestore_wishlist_get_ids() {
	$ids = array();

	if ( is_user_logged_in() ) {
		$saved = get_user_meta( get_current_user_id(), GAMESTORE_WISHLIST_META, true );
		if ( is_array( $saved ) ) {
			$ids = $saved;
		} elseif ( is_string( $saved ) && $saved !== '' ) {
			$decoded = json_decode( $saved, true );
			if ( is_array( $decoded ) ) {
				$ids = $decoded;
			}
		}
	} else {
		if ( ! empty( $_COOKIE[ GAMESTORE_WISHLIST_COOKIE ] ) ) {
			$decoded = json_decode( wp_unslash( $_COOKIE[ GAMESTORE_WISHLIST_COOKIE ] ), true );
			if ( is_array( $decoded ) ) {
				$ids = $decoded;
			}
		}
	}

	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
	return $ids;
}

/**
 * @param int[] $ids
 */
function gamestore_wishlist_save_ids( $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), GAMESTORE_WISHLIST_META, $ids );
		return;
	}

	// Guest: cookie for 30 days.
	$value   = wp_json_encode( $ids );
	$expires = time() + 30 * DAY_IN_SECONDS;

	setcookie(
		GAMESTORE_WISHLIST_COOKIE,
		$value,
		array(
			'expires'  => $expires,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		)
	);

	// Keep available during this request too.
	$_COOKIE[ GAMESTORE_WISHLIST_COOKIE ] = $value;
}

/**
 * @param int $product_id
 * @return bool
 */
function gamestore_wishlist_has( $product_id ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return false;
	}
	return in_array( $product_id, gamestore_wishlist_get_ids(), true );
}

/**
 * @param int $product_id
 * @return array{ids:int[], added:bool}
 */
function gamestore_wishlist_toggle( $product_id ) {
	$product_id = absint( $product_id );
	$ids        = gamestore_wishlist_get_ids();

	$added = false;
	if ( in_array( $product_id, $ids, true ) ) {
		$ids = array_values( array_diff( $ids, array( $product_id ) ) );
	} else {
		$ids[] = $product_id;
		$added = true;
	}

	gamestore_wishlist_save_ids( $ids );

	return array(
		'ids'   => $ids,
		'added' => $added,
	);
}

add_action( 'wp_ajax_gamestore_wishlist_toggle', 'gamestore_ajax_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_gamestore_wishlist_toggle', 'gamestore_ajax_wishlist_toggle' );
function gamestore_ajax_wishlist_toggle() {
	check_ajax_referer( 'gamestore_wishlist', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid product' ), 400 );
	}

	$result = gamestore_wishlist_toggle( $product_id );

	wp_send_json_success(
		array(
			'count' => count( $result['ids'] ),
			'added' => (bool) $result['added'],
			'ids'   => $result['ids'],
		)
	);
}

/**
 * Best-effort resolve wishlist page URL.
 *
 * @return string
 */
function gamestore_get_wishlist_page_url() {
	// First: any page that uses our template.
	$template_pages = get_posts(
		array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-wishlist.php',
		)
	);
	if ( ! empty( $template_pages ) ) {
		return get_permalink( $template_pages[0]->ID );
	}

	// Fallback by slug.
	foreach ( array( 'wishlist', 'favorites', 'izbrannoe', 'izbrannoee', 'izbrannoe-2', 'izbrannoe-tovary' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			return get_permalink( $page->ID );
		}
	}

	// Fallback by title (RU).
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'title'          => 'Избранное',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		)
	);
	if ( ! empty( $pages ) ) {
		return get_permalink( $pages[0]->ID );
	}

	return '';
}

/**
 * Render wishlist toggle button for a product.
 *
 * @param int $product_id
 */
function gamestore_render_wishlist_button( $product_id ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return;
	}

	$is_active = gamestore_wishlist_has( $product_id );
	?>
	<button
		type="button"
		class="wishlist-btn <?php echo $is_active ? 'is-active' : ''; ?>"
		data-product-id="<?php echo esc_attr( $product_id ); ?>"
		aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
		aria-label="<?php echo esc_attr( $is_active ? __( 'Убрать из избранного', 'gamestore' ) : __( 'Добавить в избранное', 'gamestore' ) ); ?>"
	>
		<i class="<?php echo $is_active ? 'fa-solid fa-heart' : 'fa-regular fa-heart'; ?>" aria-hidden="true"></i>
	</button>
	<?php
}

add_shortcode( 'gamestore_wishlist', 'gamestore_wishlist_shortcode' );
function gamestore_wishlist_shortcode() {
	$ids = gamestore_wishlist_get_ids();
	if ( empty( $ids ) ) {
		return '<p class="wishlist-empty">' . esc_html__( 'В избранном пока пусто.', 'gamestore' ) . '</p>';
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 24,
			'post__in'       => $ids,
			'orderby'        => 'post__in',
		)
	);

	ob_start();
	?>
	<div class="games-grid wishlist-grid">
		<?php
		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) :
				$query->the_post();
				global $product;
				?>
				<div class="game-card">
					<?php gamestore_render_wishlist_button( get_the_ID() ); ?>
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
							<p class="price"><?php echo $product ? $product->get_price_html() : ''; ?></p>
						</div>
					</a>
				</div>
				<?php
			endwhile;
		else :
			?>
			<p class="wishlist-empty"><?php esc_html_e( 'В избранном пока пусто.', 'gamestore' ); ?></p>
			<?php
		endif;
		wp_reset_postdata();
		?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Wishlist button on single product page.
 */
add_action( 'woocommerce_single_product_summary', 'gamestore_render_single_product_wishlist', 31 );
function gamestore_render_single_product_wishlist() {
	if ( ! function_exists( 'gamestore_render_wishlist_button' ) ) {
		return;
	}

	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	?>
	<div class="single-wishlist">
		<span class="single-wishlist__label"><?php esc_html_e( 'Избранное:', 'gamestore' ); ?></span>
		<?php gamestore_render_wishlist_button( $product->get_id() ); ?>
	</div>
	<?php
}


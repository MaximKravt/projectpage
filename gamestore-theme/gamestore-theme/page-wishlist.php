<?php
/**
 * Template Name: Избранное
 * Description: Список избранных товаров.
 */
get_header();
?>

<main class="container">
	<section class="games-section wishlist-page">
		<div class="section-header">
			<h1><?php the_title(); ?></h1>
		</div>

		<?php echo do_shortcode( '[gamestore_wishlist]' ); ?>
	</section>
</main>

<?php get_footer(); ?>


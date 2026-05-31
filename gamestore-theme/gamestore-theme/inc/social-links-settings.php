<?php
/**
 * Footer social links settings (admin) + helpers.
 */

define( 'GAMESTORE_SOCIAL_OPTION', 'gamestore_social_links' );

/**
 * Default social links set (matches footer icons).
 *
 * @return array<string, array{label:string, icon:string, url:string, enabled:string}>
 */
function gamestore_social_links_defaults() {
	return array(
		'twitter'  => array(
			'label'   => 'Twitter / X',
			'icon'    => 'fa-brands fa-twitter',
			'url'     => '',
			'enabled' => '1',
		),
		'dribbble' => array(
			'label'   => 'Dribbble',
			'icon'    => 'fa-solid fa-basketball',
			'url'     => '',
			'enabled' => '1',
		),
		'linkedin' => array(
			'label'   => 'LinkedIn',
			'icon'    => 'fa-brands fa-linkedin-in',
			'url'     => '',
			'enabled' => '1',
		),
		'facebook' => array(
			'label'   => 'Facebook',
			'icon'    => 'fa-brands fa-facebook-f',
			'url'     => '',
			'enabled' => '1',
		),
		'vk'       => array(
			'label'   => 'VK',
			'icon'    => 'fa-brands fa-vk',
			'url'     => '',
			'enabled' => '1',
		),
	);
}

/**
 * Get social links settings merged with defaults.
 *
 * @return array<string, array{label:string, icon:string, url:string, enabled:string}>
 */
function gamestore_get_social_links() {
	$saved = get_option( GAMESTORE_SOCIAL_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$defaults = gamestore_social_links_defaults();

	$merged = array();
	foreach ( $defaults as $key => $def ) {
		$item = isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ? $saved[ $key ] : array();
		$merged[ $key ] = wp_parse_args( $item, $def );
	}

	return $merged;
}

/**
 * Sanitize icon class for Font Awesome.
 *
 * @param string $class Raw input.
 * @return string
 */
function gamestore_sanitize_fa_class( $class ) {
	$class = trim( (string) $class );
	// Keep only safe characters: letters, digits, spaces and dashes.
	$class = preg_replace( '/[^a-z0-9\\-\\s]/i', '', $class );
	$class = preg_replace( '/\\s+/', ' ', $class );
	return trim( $class );
}

add_action( 'admin_menu', 'gamestore_social_links_admin_menu' );
function gamestore_social_links_admin_menu() {
	add_theme_page(
		__( 'Соцсети в футере', 'gamestore' ),
		__( 'Соцсети (футер)', 'gamestore' ),
		'manage_options',
		'gamestore-social-links',
		'gamestore_social_links_admin_page'
	);
}

add_action( 'admin_init', 'gamestore_social_links_register_settings' );
function gamestore_social_links_register_settings() {
	register_setting(
		'gamestore_social_links_group',
		GAMESTORE_SOCIAL_OPTION,
		'gamestore_sanitize_social_links_settings'
	);
}

/**
 * @param mixed $input Raw POST.
 * @return array
 */
function gamestore_sanitize_social_links_settings( $input ) {
	$defaults = gamestore_social_links_defaults();
	$output   = array();

	if ( ! is_array( $input ) ) {
		return $output;
	}

	foreach ( $defaults as $key => $def ) {
		$raw = isset( $input[ $key ] ) && is_array( $input[ $key ] ) ? $input[ $key ] : array();

		$enabled = ! empty( $raw['enabled'] ) ? '1' : '0';
		$url     = isset( $raw['url'] ) ? esc_url_raw( $raw['url'] ) : '';
		$icon    = isset( $raw['icon'] ) ? gamestore_sanitize_fa_class( $raw['icon'] ) : $def['icon'];
		$label   = isset( $raw['label'] ) ? sanitize_text_field( $raw['label'] ) : $def['label'];

		$output[ $key ] = array(
			'label'   => $label ? $label : $def['label'],
			'icon'    => $icon ? $icon : $def['icon'],
			'url'     => $url,
			'enabled' => $enabled,
		);
	}

	return $output;
}

function gamestore_social_links_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$links = gamestore_get_social_links();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Социальные сети в футере', 'gamestore' ); ?></h1>
		<p><?php esc_html_e( 'Укажите ссылки и при необходимости классы иконок Font Awesome.', 'gamestore' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'gamestore_social_links_group' ); ?>

			<table class="widefat striped" style="max-width: 980px;">
				<thead>
					<tr>
						<th style="width:70px;"><?php esc_html_e( 'Показывать', 'gamestore' ); ?></th>
						<th><?php esc_html_e( 'Название', 'gamestore' ); ?></th>
						<th style="width:260px;"><?php esc_html_e( 'URL', 'gamestore' ); ?></th>
						<th style="width:260px;"><?php esc_html_e( 'Класс иконки', 'gamestore' ); ?></th>
						<th style="width:90px;"><?php esc_html_e( 'Превью', 'gamestore' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $links as $key => $item ) : ?>
						<tr>
							<td>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( GAMESTORE_SOCIAL_OPTION ); ?>[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $item['enabled'], '1' ); ?>>
								</label>
							</td>
							<td>
								<input type="text" class="regular-text" name="<?php echo esc_attr( GAMESTORE_SOCIAL_OPTION ); ?>[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $item['label'] ); ?>">
							</td>
							<td>
								<input type="url" class="regular-text" name="<?php echo esc_attr( GAMESTORE_SOCIAL_OPTION ); ?>[<?php echo esc_attr( $key ); ?>][url]" value="<?php echo esc_attr( $item['url'] ); ?>" placeholder="https://...">
							</td>
							<td>
								<input type="text" class="regular-text" name="<?php echo esc_attr( GAMESTORE_SOCIAL_OPTION ); ?>[<?php echo esc_attr( $key ); ?>][icon]" value="<?php echo esc_attr( $item['icon'] ); ?>" placeholder="fa-brands fa-twitter">
							</td>
							<td style="font-size: 18px;">
								<i class="<?php echo esc_attr( $item['icon'] ); ?>"></i>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}


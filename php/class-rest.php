<?php
/**
 * Class REST
 *
 * @since 0.1.0
 *
 * @package wcltd\woocommerce_typeshow
 */

namespace wcltd\woocommerce_typeshow;

/**
 * REST API Endpoints.
 */
class REST {

	const REST_VERSION        = '1';
	const REST_NAMESPACE      = 'wcltd/woocommerce-typeshow/v' . self::REST_VERSION;
	const TRANSIENT_KEY_FONTS = WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX . '_fonts';

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {}

	/**
	 * Do Work.
	 *
	 * @since 0.1.0
	 */
	public function run() {
		add_action( 'rest_api_init', [ $this, 'register_setup' ] );
		add_action( 'rest_api_init', [ $this, 'register_fonts' ] );
		add_action( 'save_post', [ $this, 'remove_transient' ], 10, 3 );
	}

	/**
	 * Register Setup.
	 *
	 * @return void
	 */
	public function register_setup() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/setup/',
			array(
				'methods'  => 'GET',
				'callback' => function ( $request ) {
					$json          = [];
					$json['fonts'] = $this->get_fonts();

					$json['themes'][] = [
						'name' => 'Default',
					];

					$json['settings'] = [
						'dimensions' => [
							'width'  => '1200',
							'height' => '400',
						],
						'defaults' => [
							'theme' => 'Default',
						],
						'infolink' => [
							'display'     => '',
							'description' => 'Learn more about %font% by %designer%',
						],
					];

					$json['pangrams'][] = [
						'name' => 'Loading...',
						'text' => 'Loading...',
					];

					return $json;
				},
			)
		);
	}

	/**
	 * Register Fonts.
	 *
	 * @return void
	 */
	public function register_fonts() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/fonts/',
			array(
				'methods'  => 'GET',
				'callback' => function ( $request ) {
					$json = [];
					$json['fonts'] = $this->get_fonts();

					return $json;
				},
			)
		);
	}

	/**
	 * Get Font JSON
	 *
	 * @return void
	 */
	public function get_fonts() {
		$fonts = get_transient( self::TRANSIENT_KEY_FONTS );

		if ( $fonts ) {
			return $fonts;
		}

		$fonts      = [];
		$font_query = new \WP_Query(
			[
				'meta_query' => [
					'relation' => 'AND',
					[
						'key'     => '_virtual',
						'value'   => 'yes',
						'compare' => '=',
					],
					[
						'key'     => '_downloadable',
						'value'   => 'yes',
						'compare' => '=',
					],
				],
				'post_type'      => 'product',
				'posts_per_page' => -1,
			]
		);

		if ( $font_query->have_posts() ) {
			foreach ( $font_query->posts as $font_post ) {
				$product   = wc_get_product( $font_post->ID );
				$files     = $product->get_files();
				$font_name = '';
				$font_file = '';
				$is_font   = false;

				if ( ! empty( $files ) ) {
					foreach ( $files as $file ) {
						$file_info = pathinfo( $file['file'] );
						$extension = $file_info['extension'];
						if ( 'otf' === $extension || 'ttf' === $extension ) {
							$font_name = $file['name'];
							$font_file = $file['file'];
							$is_font   = true;
							break;
						}
					}
				}

				if ( $is_font ) {
					$fonts[] = [
						'name'         => $font_post->post_title,
						'designer'     => $product->get_attribute( 'designer' ),
						'foundry'      => $product->get_attribute( 'foundry' ),
						'url'          => $product->get_attribute( 'url' ),       // Author URL.
						'hasLigatures' => $product->get_attribute( 'ligatures' ), // Y or N.
						'styles'       => [
							[
								'fontfile' => basename( $font_file ),
								'name'     => $product->get_attribute( 'style' ),
							],
						],
					];
				}
			}
		}

		set_transient( self::TRANSIENT_KEY_FONTS, $fonts, 12 * HOUR_IN_SECONDS );

		return $fonts;
	}

	/**
	 * Delete transient on product update.
	 *
	 * @param int $post_id.
	 * @param object $post.
	 * @param bool $update.
	 * @return void
	 */
	public function remove_transient( $post_id, $post, $update ) {
		if ( 'product' === $post->post_type ) {
			delete_transient( self::TRANSIENT_KEY_FONTS );
		}
	}
};


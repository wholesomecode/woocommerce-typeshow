<?php
/**
 * Class Assets
 *
 * @since 0.1.0
 *
 * @package wcltd\woocommerce_typeshow
 */

namespace wcltd\woocommerce_typeshow;

use wcltd\woocommerce_typeshow\Meta;
use wcltd\woocommerce_typeshow\REST;

/**
 * Register assets for the plugin.
 */
class Assets {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {}

	/**
	 * Do Work
	 *
	 * @since 0.1.0
	 */
	public function run() {
		add_action( 'wp_enqueue_scripts', [ $this, 'typeshow_scripts' ], 10 );
		add_action( 'wp_enqueue_scripts', [ $this, 'typeshow_load_fonts' ], 10 );
	}

	/**
	 * Enqueue TypeShow Scripts
	 *
	 * @return void
	 */
	public function typeshow_scripts() {

		global $post;

		if ( ! $post || 'product' !== $post->post_type || empty( $this->get_product_fonts( $post ) ) ) {
			return;
		}

		$css_file_name = 'typeshow.css';
		$css_url       = plugins_url( 'typeshow/css/' . $css_file_name, WCLTD_WOOCOMMERCE_TYPESHOW_ROOT );
		$css_path      = dirname( WCLTD_WOOCOMMERCE_TYPESHOW_ROOT ) . '/typeshow/css/' . $css_file_name;
		wp_enqueue_style(
			WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX . '_typeshow_css',
			$css_url,
			[],
			filemtime( $css_path )
		);

		wp_deregister_script( 'jquery' );
		wp_enqueue_script(
			'jquery',
			plugins_url( 'typeshow/js/jquery.js', WCLTD_WOOCOMMERCE_TYPESHOW_ROOT ),
			[],
			'1.4.2',
			false
		);

		$js_file_name = 'jquery.typeshow.js';
		$js_url       = plugins_url( 'typeshow/js/' . $js_file_name, WCLTD_WOOCOMMERCE_TYPESHOW_ROOT );
		$js_path      = dirname( WCLTD_WOOCOMMERCE_TYPESHOW_ROOT ) . '/typeshow/js/' . $js_file_name;
		wp_enqueue_script(
			WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX . '_typeshow_js',
			$js_url,
			[ 'jquery' ],
			filemtime( $js_path ),
			false
		);
	}

	public function typeshow_load_fonts() {
		global $post;

		if ( ! $post || 'product' !== $post->post_type || is_admin() ) {
			return;
		}

		$font_array = $this->get_product_fonts( $post );

		if ( empty( $font_array ) ) {
			return;
		}

		ob_start();

		?>
		<script>
		var hide_typeshow_dropdown = <?php echo count( $font_array ) > 1 ? 'false' : 'true'; ?>;
		jQuery(document).ready(function( $ ) {
			$( '#typeshow' ).typeshow( {
				"folder" : "<?php echo esc_url( plugins_url( '/typeshow', WCLTD_WOOCOMMERCE_TYPESHOW_ROOT ) ); ?>",
				"fonts"  : [
					<?php
						foreach ( $font_array as $font ) {
						?>
						{
							"name":"<?php echo esc_html( $font['name'] ); ?>",
							"styles":[
								{
									"name":"<?php echo esc_html( $font['styles'][0]['name'] ); ?>"
								}
							]
						},
						<?php
						}
					?>

				]
			} );
		});
		</script>
		<?php

		$script = ob_get_contents();
		ob_end_clean();

		// Remove tags from script.
		$script = str_replace( '<script>', '', $script );
		$script = str_replace( '</script>', '', $script );

		wp_add_inline_script(
			WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX . '_typeshow_js',
			$script
		);

		// Get the font name.
		$font_name = $post->post_title;

		wp_localize_script(
			WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX . '_typeshow_js',
			'woocommerce_typescript',
			[
				'font_name' => $font_name,
			]
		);
	}

	public function get_product_fonts( $post ) {
		$rest         = new REST();
		$fonts        = $rest->get_fonts();
		$font_array   = [];
		$hide_fonts   = get_post_meta( $post->ID, Meta::META_BOX_FONTS_HIDE, true );
		$select_fonts = get_post_meta( $post->ID, Meta::META_BOX_FONTS_SELECT, true );

		if ( $hide_fonts ) {
			return $font_array;
		}

		if ( empty( $select_fonts ) ) {
			foreach ( $fonts as $font ) {
				if ( $post->post_title === $font['name'] ) {
					$font_array[] = $font;
					break;
				}
			}
		} else {
			foreach ( $select_fonts as $selected_font ) {
				foreach ( $fonts as $font ) {
					if ( $selected_font === $font['name'] ) {
						$font_array[] = $font;
						break;
					}
				}
			}
		}
		return $font_array;
	}
};

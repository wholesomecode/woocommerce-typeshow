<?php
/**
 * Class Image
 *
 * @since 0.1.0
 *
 * @package wcltd\woocommerce_typeshow
 */

namespace wcltd\woocommerce_typeshow;

/**
 * Generate Images for the plugin.
 */
class Image {

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
		add_action( 'wp', [ $this, 'generate_image' ], 10 );
	}

	public function generate_image() {
		global $post;

		if ( ! $post || has_post_thumbnail( $post ) || 'product' !== $post->post_type ) {
			return;
		}

		$process_font = false;

		$product = wc_get_product( $post->ID );
		$files   = $product->get_downloads();

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				$file_info = pathinfo( $file['file'] );
				$extension = $file_info['extension'];
				if ( 'otf' === $extension || 'ttf' === $extension ) {
					$process_font = true;
					break;
				}
			}
		}

		if ( ! $process_font ) {
			return;
		}

		$context_options = [
			'ssl' => [
				'verify_peer'      => false,
				'verify_peer_name' => false,
			],
		];

		$image_url = plugin_dir_url( WCLTD_WOOCOMMERCE_TYPESHOW_ROOT ) . 'typeshow/';
		$image_url = add_query_arg(
			[
				's'      => $post->post_title,
				'font'   => $post->post_title,
				'square' => 600,
			],
			$image_url
		);

		$attachment_id = $this->attach_image( esc_url( $image_url ), $context_options );
		if ( $attachment_id ) {
			set_post_thumbnail( $post->ID, $attachment_id );
		}
	}

	public function attach_image( $url, $context_options = [] ) {
		include_once ABSPATH . 'wp-admin/includes/image.php';

		$upload_dir = wp_upload_dir();
		$file_name  = 'preview.png';
		$file       = $upload_dir['path'] . '/' . $file_name;

		$file_headers = @get_headers( $url );
		if ( ! $file_headers || $file_headers[0] !== 'HTTP/1.1 404 Not Found' ) {
			$contents  = file_get_contents( $url, false, stream_context_create( $context_options ) );
			$file_save = fopen( $file, 'w' );

			fwrite( $file_save, $contents );
			fclose( $file_save );

			$wp_filetype = wp_check_filetype( basename( $file_name ), null );
			$attachment  = [
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => $file_name,
				'post_content'   => '',
				'post_status'    => 'inherit',
			];

			$attachment_id   = wp_insert_attachment( $attachment, $file );
			$attachment      = get_attached_file( $attachment_id );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $attachment );

			wp_update_attachment_metadata( $attachment_id, $attachment_data );
			return $attachment_id;
		}
		return false;
	}
};

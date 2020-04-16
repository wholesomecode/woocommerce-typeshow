<?php
/**
 * Class Mime
 *
 * @since 0.1.0
 *
 * @package wcltd\woocommerce_typeshow
 */

namespace wcltd\woocommerce_typeshow;

/**
 * Mime Type support for the plugin.
 */
class Mime {

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
		add_filter( 'upload_mimes', [ $this, 'supported_mime_types' ], 99999 );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'supported_mime_types_fix' ], 99999 );
	}

	public function supported_mime_types( $mimes ) {
		$mimes['otf']  = 'application/x-font-opentype';
		$mimes['ttf']  = 'application/x-font-ttf';
		$mimes['woff'] = 'application/font-woff';

		return $mimes;
	}

	public function supported_mime_types_fix( $data, $file, $filename, $mimes ) {
		$wp_filetype     = wp_check_filetype( $filename, $mimes );
		$ext             = $wp_filetype['ext'];
		$type            = $wp_filetype['type'];
		$proper_filename = $data['proper_filename'];

		return compact( 'ext', 'type', 'proper_filename' );
	}
};

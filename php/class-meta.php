<?php
/**
 * Class Meta
 *
 * @since 0.1.0
 *
 * @package wcltd\woocommerce_typeshow
 */

namespace wcltd\woocommerce_typeshow;

use wcltd\woocommerce_typeshow\REST;

/**
 * Register meta boxes for the plugin.
 */
class Meta {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {}

	const META_BOX_KEY_FONTS    = WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX . '_fonts';
	const META_BOX_FONTS_HIDE   = '_' . WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX . '_fonts_hide';
	const META_BOX_FONTS_SELECT = '_' . WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX . '_fonts';

	/**
	 * Do Work
	 *
	 * @since 0.1.0
	 */
	public function run() {
		add_action( 'cmb2_admin_init', [ $this, 'register_cmb2_meta_boxes' ], 10 );
	}

	public function register_cmb2_meta_boxes() {

		$rest         = new REST();
		$font_options = [];

		foreach ( $rest->get_fonts() as $font ) {
			$font_options[ $font['name'] ] = $font['name'];
		}

		$cmb2_box = new_cmb2_box(
			[
				'id'           => self::META_BOX_KEY_FONTS,
				'title'        => __( 'TypeShow Fonts', 'woocommerce-typeshow' ),
				'object_types' => [ 'product' ],
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true,
			]
		);

		$cmb2_box->add_field(
			[
				'name' => __( 'Hide Fonts', 'woocommerce-typeshow' ),
				'id'   => self::META_BOX_FONTS_HIDE,
				'desc' => __( 'Check this box if you do not want to display any fonts on this product page.', 'woocommerce-typeshow' ),
				'type' => 'checkbox',
			]
		);

		$cmb2_box->add_field(
			[
				'name'    => __( 'Display Fonts', 'woocommerce-typeshow' ),
				'id'      => self::META_BOX_FONTS_SELECT,
				'desc'    => __( 'Choose the fonts that you want to display on this product (this will override the default font if present). Drag and Drop the fonts into the order you wish to display them.', 'woocommerce-typeshow' ),
				'type'    => 'pw_multiselect',
				'options' => $font_options,
				'attributes' => array(
					'placeholder' => __( 'Select Fonts', 'woocommerce-typeshow' ),
				),
			]
		);
	}
};

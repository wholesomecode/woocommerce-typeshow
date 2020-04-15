<?php

/**
 * Typeshow for WooCommerce
 *
 * @link              https://github.com/wholesomecode/woocommerce-typeshow
 * @package           wcltd\woocommerce_typeshow
 *
 * Plugin Name:       Typeshow for WooCommerce
 * Plugin URI:        https://github.com/wholesomecode/woocommerce-typeshow
 * Description:       Enable font previews in WooCommerce using the TypeShow widget (http://typeshow.net).
 * Version:           0.1.0
 * Author:            Wholesome Code <hello@wholesomecode.ltd>
 * Author URI:        https://wholesomecode.ltd
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-typeshow
 * Domain Path:       /languages
 */

/**
 * Copyright (C) 2020 Wholesome Code
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Abort if this file is called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WCLTD_WOOCOMMERCE_TYPESHOW_ROOT', __FILE__ );
define( 'WCLTD_WOOCOMMERCE_TYPESHOW_NAME', 'Typeshow for WooCommerce' );
define( 'WCLTD_WOOCOMMERCE_TYPESHOW_PREFIX', 'wcltd_woocommerce_typeshow' );

load_plugin_textdomain(
	'woocommerce-typeshow',
	false,
	WCLTD_WOOCOMMERCE_TYPESHOW_ROOT . '\languages'
);

require_once 'vendor/cmb2/init.php';
require_once 'vendor/cmb-field-select2/cmb-field-select2.php';
require_once 'php/class-assets.php';
require_once 'php/class-image.php';
require_once 'php/class-meta.php';
require_once 'php/class-rest.php';

use wcltd\woocommerce_typeshow\Assets;
use wcltd\woocommerce_typeshow\Image;
use wcltd\woocommerce_typeshow\Meta;
use wcltd\woocommerce_typeshow\REST;

$assets = new Assets();
$image  = new Image();
$meta   = new Meta();
$rest   = new REST();

$assets->run();
$image->run();
$meta->run();
$rest->run();

/**
 * Functions.
 */

if ( ! function_exists( 'wcltd_woocommerce_typeshow' ) ) {
	function wcltd_woocommerce_typeshow() {
		global $post, $assets;

		if ( ! $post ) {
			return;
		}

		$fonts = $assets->get_product_fonts( $post );
		if ( empty( $fonts ) ) {
			return;
		}

		?>
		<div id="typeshow"></div>
		<?php
	}
}
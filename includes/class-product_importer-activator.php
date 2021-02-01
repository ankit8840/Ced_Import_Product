<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cedcoss.com/
 * @since      1.0.0
 *
 * @package    Product_importer
 * @subpackage Product_importer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Product_importer
 * @subpackage Product_importer/includes
 * @author     cedcommerce <woocommerce@cedcoss.com>
 */
class Product_importer_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . 'json_upload/';
		if ( ! is_dir( $upload_dir ) ) {
			mkdir( $upload_dir, 0777 );
		}
	}

}

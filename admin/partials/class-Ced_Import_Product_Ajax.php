<?php
class Ced_Import_Product_Ajax {
	/**
	 * Insert_json_products
	 * This is a function for create product
	 *
	 * @since 1.0.0
	 * @param  mixed $value
	 * @return void
	 */
	public function insert_json_products( $value ) {
		$user_id = get_current_user_id();

				$post = array(
					'post_author'  => $user_id,
					'post_content' => $value['item']['description'],
					'post_status'  => 'publish',
					'post_title'   => $value['item']['name'],
					'post_parent'  => '',
					'post_type'    => 'product',
				);

				$post_id = wp_insert_post( $post, $wp_error );
				if ( $post_id ) {
					$attach_id = get_post_meta( $post_id, 'json_products', true );
					add_post_meta( $post_id, 'json_products', $attach_id );
					echo 'sucess';
				} else {
					esc_html( $wp_error );
				}

				if ( $value['item']['has_variation'] ) {

					$this->ced_add_attributes_variable_product( $value, $post_id );
					$this->insert_featured_image( $value['item']['images'][0], $post_id );

					wp_set_object_terms( $post_id, 'variable', 'product_type' );

				} else {
					$this->insert_update_postmeta( $value, $post_id );
					$this->insert_featured_image( $value['item']['images'][0], $post_id );
					$this->add_product_attributes( $post_id, $value );
					wp_set_object_terms( $post_id, 'simple', 'product_type' );
				}

	}

	/**
	 * Insert_update_postmeta
	 * This is a function for update post_meta for product
	 *
	 * @param  mixed $value
	 * @param  mixed $post_id
	 * @return void
	 */
	public function insert_update_postmeta( $value, $post_id ) {

				update_post_meta( $post_id, '_visibility', 'visible' );
				update_post_meta( $post_id, '_stock_status', 'instock' );
				update_post_meta( $post_id, 'total_sales', '0' );
				update_post_meta( $post_id, '_downloadable', 'no' );
				update_post_meta( $post_id, '_virtual', 'no' );
				update_post_meta( $post_id, '_regular_price', $value['item']['price'] );
				update_post_meta( $post_id, '_sale_price', '' );
				update_post_meta( $post_id, '_purchase_note', '' );
				update_post_meta( $post_id, '_featured', 'no' );
				update_post_meta( $post_id, '_weight', $value['item']['weight'] );
				update_post_meta( $post_id, '_length', '' );
				update_post_meta( $post_id, '_width', '' );
				update_post_meta( $post_id, '_height', '' );
				update_post_meta( $post_id, '_sku', $value['item']['item_sku'] );
				update_post_meta( $post_id, '_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_sale_price_dates_to', '' );
				update_post_meta( $post_id, '_price', $value['item']['price'] );
				update_post_meta( $post_id, '_sold_individually', '' );
				update_post_meta( $post_id, '_manage_stock', 'no' );
				update_post_meta( $post_id, '_backorders', 'no' );
				update_post_meta( $post_id, '_stock', $value['item']['stock'] );
	}

	/**
	 * Insert_featured_image
	 * This is a function for import featured image
	 *
	 * @param  mixed $image_name_url
	 * @param  mixed $post_id
	 * @return void
	 */
	public function insert_featured_image( $image_name_url, $post_id ) {

		  $image_url                  = $image_name_url; // Define the image URL here
					$imagename        = basename( $image_url );
					$upload_dir       = wp_upload_dir(); // Set upload folder
					$image_data       = file_get_contents( $image_url ); // Get image data
					$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
					$filename         = basename( $unique_file_name ); // Create image file name

					// Check folder permission and define file location
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

					// Create the image  file on the server
					file_put_contents( $file, $image_data );

					// Check image file type
					$wp_filetype = wp_check_filetype( $filename, null );

					// Set attachment data
					$attachment = array(
						'post_mime_type' => 'image/jpeg',
						'post_title'     => sanitize_file_name( $filename ),
						'post_content'   => '',
						'post_status'    => 'publish',
					);

					// Create the attachment
					$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

					// Include image.php
					require_once ABSPATH . 'wp-admin/includes/image.php';

					// Define attachment metadata
					$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

					// Assign metadata to attachment
					wp_update_attachment_metadata( $attach_id, $attach_data );

					// And finally assign featured image to post
					set_post_thumbnail( $post_id, $attach_id );
	}



	/**
	 * Add_product_attributes
	 *
	 * @param  mixed $post_id
	 * @param  mixed $value
	 * @return void
	 */
	public function add_product_attributes( $post_id, $value ) {
		foreach ( $value['item']['attributes'] as $key ) {

			$attribute[ $key['attribute_name'] ] = array(
				'name'         => $key['attribute_name'],
				'value'        => $key['attribute_value'],
				'is_visible'   => '1',
				'is_variation' => '0',
				'is_taxonomy'  => '0',
			);
			update_post_meta( $post_id, '_product_attributes', $attribute );
		}

	}
	/**
	 * Ced_add_attributes_variable_product
	 *
	 * @param  mixed $value
	 * @param  mixed $post_id
	 * @return void
	 */
	public function ced_add_attributes_variable_product( $value, $post_id ) {
		$attribute_values = '';
		foreach ( $value['tier_variation'] as $key => $catch ) {

			$attribute_values = implode( '|', $catch['options'] );

		}
		$attribute[ $catch['name'] ] = array(
			'name'         => $catch['name'],
			'value'        => $attribute_values,
			'is_visible'   => '1',
			'is_variation' => '1',
			'is_taxonomy'  => '0',
		);

		update_post_meta( $post_id, '_product_attributes', $attribute );
		$this->ced_create_variable_product( $post_id, $value, $attribute );

	}

	/**
	 * Ced_create_variable_product
	 *
	 * @param  mixed $post_id
	 * @param  mixed $value
	 * @param  mixed $attribute
	 * @return void
	 */
	public function ced_create_variable_product( $post_id, $value, $attribute ) {

		foreach ( $attribute as $key ) {
			$attr_val = ( explode( '|', $key['value'] ) );
		}
		echo '<pre>.previous vaule';
		print_r( $attr_val );
		foreach ( $attr_val as $key3 => $name ) {
			$variation    = array(
				'post_title'   => $value['name'] . '-' . $name,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_parent'  => $post_id,
				'post_type'    => 'product_variation',
			);
			$variation_id = wp_insert_post( $variation );

			foreach ( $value['tier_variation'] as $key => $val ) {

				foreach ( $val['images_url'] as $key4 => $imagename ) {
					if ( $key3 == $key4 ) {
						$this->insert_featured_image( $imagename, $variation_id );
					}
				}
			}

			$this->ced_update_post_meta_variable_product( $variation_id, $name, $post_id, $value );

		}

	}

	/**
	 * Ced_update_post_meta_variable_product
	 *
	 * @param  mixed $variation_id
	 * @param  mixed $variation_name
	 * @param  mixed $post_id
	 * @param  mixed $value
	 * @return void
	 */
	public function ced_update_post_meta_variable_product( $variation_id, $variation_name, $post_id, $value ) {

		foreach ( $value['item']['variations'] as $all_variation_names ) {
			if ( $all_variation_names['name'] == $variation_name ) {

				$attribute_name = '';
				foreach ( $value['tier_variation'] as $key => $val ) {
					$attribute_name = $val['name'];
				}

				update_post_meta( $variation_id, 'attribute_' . strtolower( $attribute_name ), $variation_name );
				update_post_meta( $variation_id, '_visibility', 'visible' );
				update_post_meta( $variation_id, '_stock_status', 'instock' );
				update_post_meta( $variation_id, 'total_sales', '0' );
				update_post_meta( $variation_id, '_downloadable', 'no' );
				update_post_meta( $variation_id, '_virtual', 'no' );
				update_post_meta( $variation_id, '_regular_price', $all_variation_names['price'] );
				update_post_meta( $variation_id, '_sale_price', '' );
				update_post_meta( $variation_id, '_purchase_note', '' );
				update_post_meta( $variation_id, '_featured', 'no' );
				update_post_meta( $variation_id, '_weight', '' );
				update_post_meta( $variation_id, '_length', '' );
				update_post_meta( $variation_id, '_width', '' );
				update_post_meta( $variation_id, '_height', '' );
				update_post_meta( $variation_id, '_sku', $all_variation_names['variation_sku'] );
				update_post_meta( $variation_id, '_sale_price_dates_from', '' );
				update_post_meta( $variation_id, '_sale_price_dates_to', '' );
				update_post_meta( $variation_id, '_price', $all_variation_names['price'] );
				update_post_meta( $variation_id, '_sold_individually', '' );
				update_post_meta( $variation_id, '_manage_stock', 'no' );
				update_post_meta( $variation_id, '_backorders', 'no' );
				update_post_meta( $variation_id, '_stock', $all_variation_names['stock'] );
			}
		}

	}

}


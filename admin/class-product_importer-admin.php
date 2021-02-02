<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cedcoss.com/
 * @since      1.0.0
 *
 * @package    Product_importer
 * @subpackage Product_importer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Product_importer
 * @subpackage Product_importer/admin
 * author     cedcommerce <woocommerce@cedcoss.com>
 */
class Product_Importer_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Product_importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Product_importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/product_importer-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Product_importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Product_importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/product_importer-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'frontendajax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'name'    => 'Ankit',
				'nonce'   => wp_create_nonce( 'check_nonce_for_ajax' ),
			)
		);

	}


	/**
	 * Add_ced_import_product_menu
	 * This is a function for add admin menu for product Importer
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function add_ced_import_product_menu() {
		add_menu_page(
			' ced_Import Product', // menu_title
			'Import_Product', // menu name
			'manage_options', // capability
			'ced_boiler_slug', // slug
			array( $this, 'add_content' ),
			'',
			25
		);
	}


	/**
	 * Add_content
	 * This is a callback function for admin menu which contain file upload system
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_content() {
		$json_uploded_files = array();
		if ( isset( $_POST['nonce_files'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce_files'], 'nonce_files' ) ) ) {
			if ( isset( $_POST['upload_json'] ) ) {
				$filename   = isset( $_FILES['uploadfile']['name'] ) ? sanitize_text_field( $_FILES['uploadfile']['name'] ) : false;
				$extention  = pathinfo( $filename, PATHINFO_EXTENSION );
				$filetype   = isset( $_FILES['uploadfile']['type'] ) ? sanitize_text_field( $_FILES['uploadfile']['type'] ) : false;
				$filesize   = isset( $_FILES['uploadfile']['size'] ) ? sanitize_text_field( $_FILES['uploadfile']['size'] ) : false;
				$filetemp   = isset( $_FILES['uploadfile']['tmp_name'] ) ? sanitize_text_field( $_FILES['uploadfile']['tmp_name'] ) : false;
				$upload     = wp_upload_dir();
				$upload_dir = $upload['basedir'];
				$filestore  = $upload_dir . '/json_upload/' . $filename . '';

				if ( 'json' == $extention ) {
					$json_uploded_files = get_option( 'json_files' );
					move_uploaded_file( $filetemp, $filestore );
					if ( ! empty( $json_uploded_files ) ) {
						$json_uploded_files[] = array(
							'filename' => $filename,
							'filepath' => $filestore,
						);
					} else {
						$json_uploded_files[0] = array(
							'filename' => $filename,
							'filepath' => $filestore,
						);
					}
					// array($filename=>$filestore);
					update_option( 'json_files', $json_uploded_files );
				}
			}
		}

		?>
		<div>
			<form  method="POST" enctype="multipart/form-data">
				<h2>Import Products</h2>
				<label>Upload File</label><input type="file" name="uploadfile">
				<input type="hidden" name="nonce_files" value="<?php esc_html( wp_create_nonce( 'nonce_for_files' ) ); ?>"/>
				<input type="submit" name="upload_json" class="button button-primary button-large" value="Upload_Files">
			</form>
		</div>

		<div>
			<h3>Choose Your JSON Files</h3>
			<select id="json_filesnames">
			<option>Choose Files Here</option>
			<?php
			$filesdata = get_option( 'json_files', 1 );
			foreach ( $filesdata as $key ) {
				?>
					<option><?php  echo $key['filename'] ; ?></option>
				  <?php } ?>
			</select>
			<div id="showdata">
				
			</div>
		</div>


		<?php
			$this->ced_order_import();
	}





	
	/**
	 * ced_order_import
	 *
	 * @return void
	 */
	function ced_order_import(){
		//if ( isset( $_POST['nonce_order_files'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce_order_files'], 'nonce_order_files' ) ) ) {
			if (isset($_POST['upload_order_json'])){


				$filename   = isset( $_FILES['upload_order_file']['name'] ) ? sanitize_text_field( $_FILES['upload_order_file']['name'] ) : false;
				$extention  = pathinfo( $filename, PATHINFO_EXTENSION );
				$filetype   = isset( $_FILES['upload_order_file']['type'] ) ? sanitize_text_field( $_FILES['upload_order_file']['type'] ) : false;
				$filesize   = isset( $_FILES['upload_order_file']['size'] ) ? sanitize_text_field( $_FILES['upload_order_file']['size'] ) : false;
				$filetemp   = isset( $_FILES['upload_order_file']['tmp_name'] ) ? sanitize_text_field( $_FILES['upload_order_file']['tmp_name'] ) : false;
				$upload     = wp_upload_dir();
				$upload_dir = $upload['basedir'];
				$filestore  = $upload_dir . '/json_upload/' . $filename . '';

				if ( 'json' == $extention ) {
					$json_uploded_files = get_option( 'json_order_files' );
					move_uploaded_file( $filetemp, $filestore );
					if ( ! empty( $json_uploded_files ) ) {
						$json_uploded_files[] = array(
							'filename' => $filename,
							'filepath' => $filestore,
						);
					} else {
						$json_uploded_files[0] = array(
							'filename' => $filename,
							'filepath' => $filestore,
						);
					}
					// array($filename=>$filestore);
					update_option( 'json_order_files', $json_uploded_files );
				}
				
			}



		//}
		
		
		
		
		
		?>
		<div>
			<form  method="POST" enctype="multipart/form-data">
				<h2>Import Orders</h2>
				<label>Upload File</label><input type="file" name="upload_order_file">
				<input type="hidden" name="nonce_order_files" value="<?php echo ( wp_create_nonce( 'nonce_for_order_files' ) ); ?>"/>
				<input type="submit" name="upload_order_json" class="button button-primary button-large" value="Upload_Files">
			</form>
		</div>


		<div>
			<h3>Choose Your JSON Files</h3>
			<select id="json_order_file">
			<option>Choose Files Here</option>
			<?php
			$filesdata = get_option( 'json_order_files', 1 );
			foreach ( $filesdata as $key ) {
				?>
					<option><?php  echo $key['filename'] ; ?></option>
				  <?php } ?>
			</select>
			<div id="showdata">
				
			</div>
		</div>
	<?php }


	
	/**
	 * ced_import_orders
	 *
	 * @return void
	 */
	public function ced_import_orders(){
		$product_file_name=$_POST['product_filename'];
		//echo $product_file_name;
		$order_file_name=$_POST['order_filename'];
		$data     = wp_get_upload_dir();
		$filepath_product = $data['basedir'] . "/json_upload/$product_file_name";
		//echo $filepath;
		$productfile = file_get_contents( $filepath_product );
		$productfile = json_decode( $productfile, true );
		$order_file_path = $data['basedir'] . "/json_upload/$order_file_name";
		//echo $order_file_name;
		$orderfile = file_get_contents( $order_file_path );
		$orderfile = json_decode( $orderfile, true );
		foreach($orderfile as $key1=>$value1){
			foreach($value1['Order'] as $key2=>$value2){
				$orderId=$value2['OrderID'];
				$order_status=$value2['OrderStatus'];
				$shipping_Address_name=$value2['ShippingAddress']['Name'];
				$shipping_Address_street1=$value2['ShippingAddress']['Street1'];
				$shipping_Address_street2=$value2['ShippingAddress']['Street2'];
				$shipping_Address_cityname=$value2['ShippingAddress']['CityName'];
				$shipping_Address_country=$value2['ShippingAddress']['Country'];
				$shipping_Address_countryname=$value2['ShippingAddress']['CountryName'];
				$shipping_Address_phone=$value2['ShippingAddress']['Phone'];
				$shipping_Address_postalcode=$value2['ShippingAddress']['PostalCode'];
				$shipping_Address_id=$value2['ShippingAddress']['AddressID'];
				$shipping_Address_owner=$value2['ShippingAddress']['AddressOwner'];
				$shipping_title=$value2['ShippingServiceSelected']['ShippingService'];
				$shipping_value=$value2['Subtotal']['value'];
				//echo $shipping_value;

			}
		}
		foreach($orderfile['OrderArray'] as $key1=>$value1){
			// echo '<pre>';
			// print_r($key1);
			foreach($value1 as $key2=>$value2){
				
				foreach($value2['TransactionArray'] as $key3=>$value3){
					
					foreach($value3 as $key4=>$value4){
						echo '<pre>';
						$tax_value=$value4['Taxes']['TotalTaxAmount']['value'];
						echo '<pre>';
						print_r($tax_value);
						var_dump($tax_value);
						$order_product_sku=$value4['Item']['SKU'];

					}
				}
			}
		}
		foreach($productfile as $key1=>$product_data){
			
			if($product_data['item']['item_sku']==$order_product_sku){
				 //echo '<pre>';
				// print_r($value1);
				$item_sku=$product_data['item']['item_sku'];
				//echo $item_sku;
				global $wpdb;
				//$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $item_sku ) );
				global $woocommerce;
				$product_id=wc_get_product_id_by_sku($item_sku);
				//var_dump($product_id);
				$address = array(
					'first_name' => $shipping_Address_name,
					'last_name'  => '',
					'company'    => 'cedcommerce',
					'email'      => 'no@spam.com',
					'phone'      => $shipping_Address_phone,
					'address_1'  => $shipping_Address_street1,
					'address_2'  => $shipping_Address_street2,
					'city'       => $shipping_Address_cityname,
					'state'      => '',
					'postcode'   => $shipping_Address_postalcode,
					'country'    => $shipping_Address_country
				);
				
				// Now we create the order
				$order = wc_create_order();
				$product=wc_get_product($product_id, 1);
				// echo '<pre>';
				// print_r($product);
				
				// The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
				$order->add_product( wc_get_product($product_id), 1); // Use the product IDs to add
				
				// Set addresses
				$order->set_address( $address, 'billing' );
				$order->set_address( $address, 'shipping' );
				
				// Set payment gateway
				$payment_gateways = WC()->payment_gateways->payment_gateways();
				$order->set_payment_method( $payment_gateways['bacs'] );
				
				// Calculate totals
				$order->calculate_totals();
				$order->update_status( 'completed', 'Order created dynamically - ', TRUE);


				$item = new WC_Order_Item_Shipping();

				$item->set_method_title( $shipping_title );
				$item->set_total( $shipping_value );
				$order->add_item( $item );
				$order->calculate_totals();



				//Add taxes in Orders

				
				// Set the array for tax calculations
				$calculate_tax_for = array(
					'country' => $shipping_Address_country, 
					'state' => '', 
					'postcode' =>  $shipping_Address_postalcode, 
					'city' => $shipping_Address_cityname
				);

				// Get a new instance of the WC_Order_Item_Fee Object
				$item_fee = new WC_Order_Item_Fee();

				$item_fee->set_name( "Fee" ); // Generic fee name
				$item_fee->set_amount( $tax_value ); // Fee amount
				$item_fee->set_tax_class( '' ); // default for ''
				$item_fee->set_tax_status( 'taxable' ); // or 'none'
				$item_fee->set_total( $tax_value ); // Fee amount

				// Calculating Fee taxes
				$item_fee->calculate_taxes( $calculate_tax_for );

				// Add Fee item to the order
				$order->add_item( $item_fee );

				## ----------------------------------------------- ##

				$order->calculate_totals();
			}
		}
		wp_die();
	}
	/**
	 * Add_json_products
	 * This is ajax function which use for show all content of json file
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_json_products() {
		//if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'], 'check_nonce_for_ajax' ) ) ) {
			$filename = isset( $_POST['filename'] ) ? sanitize_text_field( $_POST['filename'] ) : false;
			$data     = wp_get_upload_dir();
			$filepath = $data['basedir'] . "/json_upload/$filename";
			$content  = file_get_contents( $filepath );
			$content  = json_decode( $content, true );
			require_once plugin_dir_path( __FILE__ ) . 'partials/class-Display_json_product.php';
			$product_obj        = new Display_Json_Product();
			$product_obj->items = $content;
			$product_obj->prepare_items();
			$product_obj->display();
			
		//}
		wp_die();
	}

	/**
	 * Ced_import_products
	 * This is an ajax function which use for Import product
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ced_import_products() {
		//if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'], 'check_nonce_for_ajax' ) ) ) {
			$filename = isset( $_POST['filename'] ) ? sanitize_text_field( $_POST['filename'] ) : false;
			$item_id  = isset( $_POST['item_id'] ) ? sanitize_text_field( $_POST['item_id'] ) : false;
			$data     = wp_get_upload_dir();
			$filepath = $data['basedir'] . "/json_upload/$filename";
			$content  = file_get_contents( $filepath );
			$content  = json_decode( $content, true );
			foreach ( $content as $key => $value ) {
				if ( $value['item']['item_id'] == $item_id ) {
					require_once plugin_dir_path( __FILE__ ) . 'partials/class-Ced_Import_Product_Ajax.php';
					$product_obj = new Ced_Import_Product_Ajax();
					$product_obj->insert_json_products( $value );
					require_once plugin_dir_path( __FILE__ ) . 'partials/class-Display_json_product.php';
					$product_obj        = new Display_Json_Product();
					$product_obj->items = $content;
					$product_obj->prepare_items();
					$product_obj->display();

				}
			}

			wp_die();
		}
	//}

	/**
	 * Ced_bulk_import_products
	 * This is a ajax call function which is use for bulk import products
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ced_bulk_import_products() {
		//if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce'], 'check_nonce_for_ajax' ) ) ) {
			$bulk_option_name = isset( $_POST['bulkoption'] ) ? sanitize_text_field( $_POST['bulkoption'] ) : false;

			$bulkcheckboxes = isset( $_POST['bulkcheckboxes'] ) ? sanitize_text_field( $_POST['bulkcheckboxes'] ) : false;
			$filename       = isset( $_POST['selected_file'] ) ? sanitize_text_field( $_POST['selected_file'] ) : false;
			$data           = wp_get_upload_dir();
			$filepath       = $data['basedir'] . "/json_upload/$filename";
			$content        = file_get_contents( $filepath );
			$content        = json_decode( $content, true );
			foreach ( $content as $key => $value ) {
				foreach ( $bulkcheckboxes as $bulk_id ) {
					if ( $value['item']['item_id'] == $bulk_id ) {
						require_once plugin_dir_path( __FILE__ ) . 'partials/class-Ced_Import_Product_Ajax.php';
						$product_obj = new Ced_Import_Product_Ajax();
						$product_obj->insert_json_products( $value );
					}
				}
			}
			wp_die();
		//}
	}


}

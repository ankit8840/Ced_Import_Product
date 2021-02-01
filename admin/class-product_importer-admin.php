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
				'nonce' => wp_create_nonce('check_nonce_for_ajax'),
			)
		);

	}


	/**
	 * Add_ced_import_product_menu
	 * This is a function for add admin menu for product Importer  
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
	 * @since 1.0.0
	 * @return void
	 */
	public function add_content() {
		$json_uploded_files = array();
		if ( isset( $_POST['nonce_files'] ) && wp_verify_nonce( sanitize_text_field( $_POST['nonce_files'], 'nonce_files' ) ) ) {
		if ( isset( $_POST['upload_json'] ) ) {
			$filename   = isset($_FILES['uploadfile']['name']) ? sanitize_text_field($_FILES['uploadfile']['name']):false;
			$extention  = pathinfo( $filename, PATHINFO_EXTENSION );
			$filetype   = isset($_FILES['uploadfile']['type']) ? sanitize_text_filed($_FILES['uploadfile']['type']):false;
			$filesize   = $_FILES['uploadfile']['size'];
			$filetemp   = $_FILES['uploadfile']['tmp_name'];
			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$filestore  = $upload_dir . '/json_upload/' . $filename . '';

			if ( $extention == 'json' ) {
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
				<input type="hidden" name="nonce_files" value="<?php echo wp_create_nonce('nonce_for_files'); ?>"/>
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
					<option><?php echo $key['filename']; ?></option>
				  <?php } ?>
			</select>
			<div id="showdata">
				
			</div>
		</div>

		<?php

	}
	
	/**
	 * Add_json_products
	 * This is ajax function which use for show all content of json file
	 * @since 1.0.0
	 * @return void
	 */
	public function add_json_products() {
		if ( wp_verify_nonce( $_POST['nonce'], 'check_nonce_for_ajax' ) ) {
		$filename = $_POST['filename'];
		$data     = wp_get_upload_dir();
		$filepath = $data['basedir'] . "/json_upload/$filename";
		$content  = file_get_contents( $filepath );
		$content  = json_decode( $content, true );
		require_once plugin_dir_path( __FILE__ ) . 'partials/class-Display_json_product.php';
		$product_obj        = new Display_Json_Product();
		$product_obj->items = $content;
		$product_obj->prepare_items();
		$product_obj->display();
		wp_die();
	}
}
		
	/**
	 * Ced_import_products
	 * This is an ajax function which use for Import product 
	 * @since 1.0.0
	 * @return void
	 */
	public function ced_import_products() {
		$filename = $_POST['filename'];
		$item_id  = $_POST['item_id'];
		$data     = wp_get_upload_dir();
		$filepath = $data['basedir'] . "/json_upload/$filename";
		$content = file_get_contents( $filepath );
		$content = json_decode( $content, true );
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
	
	/**
	 * Ced_bulk_import_products
	 * This is a ajax call function which is use for bulk import products
	 * @since 1.0.0
	 * @return void
	 */
	public function ced_bulk_import_products() {
		$bulk_option_name = $_POST['bulkoption'];
		$bulkcheckboxes   = $_POST['bulkcheckboxes'];
		$filename         = $_POST['selected_file'];
		$data             = wp_get_upload_dir();
		$filepath         = $data['basedir'] . "/json_upload/$filename";
		$content          = file_get_contents( $filepath );
		$content          = json_decode( $content, true );
		foreach ( $content as $key => $value ) {
			foreach ( $bulkcheckboxes as $bulk_id ) {
				if ( $value['item']['item_id'] == $bulk_id ) {
					require_once plugin_dir_path( __FILE__ ) . 'partials/class-Ced_Import_Product_Ajax.php';
					$product_obj = new Ced_Import_Product_Ajax();
					$product_obj->insert_json_products( $value );
					echo 'datainserted ' . $bulk_id;
				}
			}
		}
		wp_die();
	}



}

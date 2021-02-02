<?php
class Display_Json_Product extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Customer', 'sp' ), // singular name of the listed records
				'plural'   => __( 'Customers', 'sp' ), // plural name of the listed records
				'ajax'     => false, // should this table support ajax?

			)
		);

	}

	// column name function
	// function column_name( $item ) {
	// print_r($item);
	// create a nonce
	// $delete_nonce = wp_create_nonce( 'sp_delete_customer' );

	// $title = '<strong>' . $item['name'] . '</strong>';

	// $actions = [
	// 'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
	// ];

	// return $title . $this->row_actions( $actions );
	// }

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array  $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		// print_r($item);
		switch ( $column_name ) {
			case 'name':
				return $item['item'][ $column_name ];
			case 'item_sku':
				return $item['item'][ $column_name ];
			case 'price':
				return $item['item'][ $column_name ];
			case 'type':
				if ( $item['item']['has_variation'] ) {
					return 'variable';
				} else {
					return 'simple';
				}
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item['item']['item_id']
		);
	}
	public function column_images( $item ) {

		return sprintf(
			'<img src="%s" style="width:90px; height:50px;"/>',
			$item['item']['images'][0]
		);
		// $address = isset($item['item']['images'][0]) ? $item['item']['images'][0] : '';
		// return $address;
	}
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		$address = isset( $item['item']['name'] ) ? $item['item']['name'] : '';
		return $address;
	}
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_sku( $item ) {
		$address = isset( $item['item_sku'] ) ? $item['item_sku'] : '';
		return $address;
	}
	public function column_price( $item ) {
		$address = isset( $item['item']['price'] ) ? $item['item']['price'] : '';
		return $address;
	}
	public function column_action( $item ) {
		$id = $item['item']['item_sku'];
		global $wpdb;
		$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1", $id ) );
		if ( $product_id ) {
			return sprintf(
				// '<input type="hidden" class="item_id" value='.$item['item']['item_id'].'>

				'<input type="button" disabled class="button button-primary import_product" name=' . $item['item']['item_id'] . ' value="Imported" />',
				$item['id']
			);
		} else {
			return sprintf(
				// '<input type="hidden" class="item_id" value='.$item['item']['item_id'].'>

				'<input type="button" class="button button-primary import_product" name=' . $item['item']['item_id'] . ' value="Import" />',
				$item['id']
			);      }

	}
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'images'   => __( 'Image', 'sp' ),
			'name'     => __( 'Title', 'sp' ),
			'item_sku' => __( 'SKU', 'sp' ),
			'price'    => __( 'Price', 'sp' ),
			'type'     => __( 'Type', 'sp' ),
			'action'   => __( 'Action', 'sp' ),
		);

		return $columns;
	}
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name'     => array( 'name', true ),
			'item_sku' => array( 'item_sku', true ),
			'price'    => array( 'price', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		// $this->items = $this->example_data;

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'customers_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();
		// $column= self::get_columns();
		// print_r($column);
		// die();
		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page'    => $per_page, // WE have to determine how many items to show on a page
			)
		);

		// $this->items =$this->get_subscribe( $per_page, $current_page );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = array(
			'label'   => 'Customers',
			'default' => 5,
			'option'  => 'customers_per_page',
		);

		add_screen_option( $option, $args );

		// $this->customers_obj = new Customers_List();
	}

	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_bulk_actions() {

		return array(
			'delete' => __( 'Delete', 'your-textdomain' ),
			'import' => __( 'Import', 'your-textdomain' ),
			// 'save'   => __( 'Save', 'your-textdomain' ),
		);

	}




}


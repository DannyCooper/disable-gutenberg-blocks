<?php
/**
 * Extend the core List Table class.
 *
 * @package    disable-gutenberg-blocks
 * @copyright  Copyright (c) 2018, Danny Cooper
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Extend the core list table class.
 */
class DGB_List_Table extends WP_List_Table {

	/**
	 * Array of blocks.
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * [__construct description]
	 */
	public function __construct() {

		global $status, $page;
		parent::__construct(
			array(
				'singular' => __( 'Block', 'disable-gutenberg-blocks' ),
				'plural'   => __( 'Blocks', 'disable-gutenberg-blocks' ),
				'ajax'     => false,
			)
		);
		$this->set_data();

	}

	/**
	 * [set_data description]
	 */
	public function set_data() {

		$this->data = array(
			array(
				'name'        => 'Example Block',
				'id'          => '0',
				'description' => 'Description',
				'category'    => 'example',
			),
		);

	}

	/**
	 * [column_default description]
	 *
	 * @param array  $item an array of block data.
	 * @param string $column_name Name of current column.
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'name':
			case 'id':
			case 'description':
			case 'category':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
		}

	}

	/**
	 * [get_columns description]
	 */
	public function get_columns() {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'        => __( 'Name', 'disable-gutenberg-blocks' ),
			'id'          => __( 'ID', 'disable-gutenberg-blocks' ),
			'description' => __( 'Description', 'disable-gutenberg-blocks' ),
			'category'    => __( 'Category', 'disable-gutenberg-blocks' ),
		);
		return $columns;

	}

	/**
	 * [prepare_items description]
	 */
	public function prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->data;

	}

	/**
	 * Output number of blocks.
	 */
	public function get_row_count() {

		return count( $this->data );

	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of block data.
	 */
	public function column_name( $item ) {

		// Create a nonce.
		$dgb_nonce = wp_create_nonce( 'dgb_nonce' );

		$title = '<strong>' . $item['name'] . '</strong>';

		if ( $this->is_block_disabled( $item['name'] ) ) {

			$actions = array(
				'enable' => sprintf( '<a href="?page=%s&action=%s&block=%s&_wpnonce=%s">%s</a>', 'disable-blocks', 'enable', esc_attr( $item['name'] ), $dgb_nonce, esc_html__( 'Enable', 'disable-gutenberg-blocks' ) ),
			);

		} else {

			$actions = array(
				'disable' => sprintf( '<a href="?page=%s&action=%s&block=%s&_wpnonce=%s">%s</a>', 'disable-blocks', 'disable', esc_attr( $item['name'] ), $dgb_nonce, esc_html__( 'Disable', 'disable-gutenberg-blocks' ) ),
			);

		}

		return $title . $this->row_actions( $actions );

	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item an array of block data.
	 */
	public function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="bulk-change[]" value="%s" />',
			$item['name']
		);

	}

	/**
	 * Returns an associative array containing the bulk action
	 */
	public function get_bulk_actions() {

		$actions = array(
			'bulk-enable'  => esc_html__( 'Enable', 'disable-gutenberg-blocks' ),
			'bulk-disable' => esc_html__( 'Disable', 'disable-gutenberg-blocks' ),
		);

		return $actions;

	}

	/**
	 * [disable_block description]
	 *
	 * @param string $name Name of block to check if disabled.
	 */
	public function is_block_disabled( $name ) {

		$disabled_blocks = (array) get_option( 'dgb_disabled_blocks', array() );

		if ( in_array( $name, $disabled_blocks, true ) ) {
			return true;

		}
		return false;

	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item.
	 */
	public function single_row( $item ) {

		echo '<tr class="example-block">';
		$this->single_row_columns( $item );
		echo '</tr>';

	}

}

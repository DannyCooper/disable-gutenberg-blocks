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
		$this->data = DGF()->get_blocks();
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
			'description' => __( 'Description', 'disable-gutenberg-blocks' ),
			'category'    => __( 'Category', 'disable-gutenberg-blocks' ),
		);
		return $columns;
	}

	/**
	 * [prepare_items description]
	 */
	public function prepare_items() {

		// Process bulk action.
		$this->process_bulk_action();

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->data;

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
				'enable' => sprintf( '<a href="?page=%s&action=%s&block=%s&_wpnonce=%s">Enable</a>', 'disable-blocks', 'enable', esc_attr( $item['name'] ), $dgb_nonce ),
			);

		} else {

			$actions = array(
				'disable' => sprintf( '<a href="?page=%s&action=%s&block=%s&_wpnonce=%s">Disable</a>', 'disable-blocks', 'disable', esc_attr( $item['name'] ), $dgb_nonce ),
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
		$actions = [
			'bulk-enable'  => esc_html__( 'Enable', 'disable-google-fonts' ),
			'bulk-disable' => esc_html__( 'Disable', 'disable-google-fonts' ),
		];

		return $actions;
	}

	/**
	 * [process_bulk_action description]
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered...
		if ( 'disable' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			$block = ( isset( $_GET['block'] ) ? sanitize_text_field( wp_unslash( $_GET['block'] ) ) : '' );

			if ( ! wp_verify_nonce( $nonce, 'dgb_nonce' ) ) {
				die( 'Not today.' );
			} else {
				$this->disable_block( $block );
				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url.
				wp_safe_redirect( admin_url( 'admin.php?page=disable-blocks' ) );
				exit();
			}
		}

		// Detect when a bulk action is being triggered...
		if ( 'enable' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			$block = ( isset( $_GET['block'] ) ? sanitize_text_field( wp_unslash( $_GET['block'] ) ) : '' );

			if ( ! wp_verify_nonce( $nonce, 'dgb_nonce' ) ) {
				die( 'Not today.' );
			} else {
				$this->enable_block( $block );
			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
			// add_query_arg() return the current url.
			wp_safe_redirect( admin_url( 'admin.php?page=disable-blocks' ) );
			exit();
		}

		// If the disable enable action is triggered.
		if ( ( isset( $_POST['action'] ) && 'bulk-enable' === $_POST['action'] )
		|| ( isset( $_POST['action2'] ) && 'bulk-enable' === $_POST['action2'] )
		) {

			$bulk_change_ids = ( isset( $_POST['bulk-change'] ) ?  $_POST['bulk-change'] : array() );

			// loop over the array of record IDs and enable them.
			foreach ( $bulk_change_ids as $id ) {
				$this->enable_block( $id );
			}
		}

		// If the disable bulk action is triggered.
		if ( ( isset( $_POST['action'] ) && 'bulk-disable' === $_POST['action'] )
		|| ( isset( $_POST['action2'] ) && 'bulk-disable' === $_POST['action2'] )
		) {

			$bulk_change_ids = ( isset( $_POST['bulk-change'] ) ? $_POST['bulk-change'] : array() );

			// loop over the array of record IDs and disable them.
			foreach ( $bulk_change_ids as $id ) {
				$this->disable_block( $id );
			}
		}
	}

	/**
	 * [disable_block description]
	 *
	 * @param string $name Name of block to disable.
	 */
	public function disable_block( $name ) {
		$blocks = get_option( 'dgb_disabled_blocks', array() );
		if ( ! in_array( $name, (array) $blocks, true ) ) {
			$blocks[] = $name;
		}
		update_option( 'dgb_disabled_blocks', $blocks );
	}

	/**
	 * [disable_block description]
	 *
	 * @param string $name Name of block to enable.
	 */
	public function enable_block( $name ) {
		$blocks     = get_option( 'dgb_disabled_blocks', array() );
		$new_blocks = array();
		if ( in_array( $name, (array) $blocks, true ) ) {
			$new_blocks = array_diff( $blocks, array( $name ) );
		}
		update_option( 'dgb_disabled_blocks', $new_blocks );
	}

	/**
	 * [disable_block description]
	 *
	 * @param string $name Name of block to check if disabled.
	 */
	public function is_block_disabled( $name ) {
		$disabled_blocks = get_option( 'dgb_disabled_blocks', array() );

		if ( in_array( $name, (array) $disabled_blocks, true ) ) {
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

		if ( $this->is_block_disabled( $item['name'] ) ) {
			echo '<tr class="disabled">';
		} else {
			echo '<tr>';
		}

		$this->single_row_columns( $item );
		echo '</tr>';
	}

}

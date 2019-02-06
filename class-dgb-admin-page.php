<?php
/**
 * Extend the core List Table class.
 *
 * @package    disable-gutenberg-blocks
 * @copyright  Copyright (c) 2018, Danny Cooper
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Get the list table class.
require_once DGB_PLUGIN_DIR_PATH . 'class-dgb-list-table.php';

/**
 * [DGB_ADMIN_MENU description]
 */
class DGB_Admin_Page {

	/**
	 * Autoload method
	 */
	public function __construct() {

		// Register the submenu.
		add_action( 'load-settings_page_disable-blocks', array( $this, 'process_bulk_action' ) );
		add_action( 'admin_menu', array( $this, 'register_sub_menu' ), 50 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

	}

	/**
	 * Enqueue the scripts and styles.
	 *
	 * @param string $hook The current page ID.
	 */
	public function enqueue( $hook ) {

		if ( 'settings_page_disable-blocks' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'dgb-admin', plugins_url( 'css/style.css', __FILE__ ), array(), '1.0.0' );

		$block_categories = array();
		if ( function_exists( 'gutenberg_get_block_categories' ) ) {
				$block_categories = gutenberg_get_block_categories( get_post() );
		} elseif ( function_exists( 'get_block_categories' ) ) {
				$block_categories = get_block_categories( get_post() );
		}

		wp_add_inline_script(
			'wp-blocks',
			sprintf( 'wp.blocks.setCategories( %s );', wp_json_encode( $block_categories ) ),
			'after'
		);

		do_action( 'enqueue_block_editor_assets' );
		wp_dequeue_script( 'disable-gutenberg-blocks' );

		$local_arr = array(
			'disabledBlocks' => get_option( 'dgb_disabled_blocks', array() ),
			'nonce'          => wp_create_nonce( 'dgb_nonce' ),
		);

		$block_registry = WP_Block_Type_Registry::get_instance();
		foreach ( $block_registry->get_all_registered() as $block_name => $block_type ) {
			// Front-end script.
			if ( ! empty( $block_type->editor_script ) ) {
				wp_enqueue_script( $block_type->editor_script );
			}
		}

		wp_enqueue_script( 'dgb-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'wp-blocks', 'wp-element', 'wp-data', 'wp-components', 'wp-block-library' ), '1.1.0' );
		wp_localize_script( 'dgb-admin', 'dgb_object', $local_arr );
		wp_localize_script(
			'dgb-admin',
			'dgb_strings',
			array(
				'enable'  => __( 'Enable', 'disable-gutenberg-blocks' ),
				'disable' => __( 'Disable', 'disable-gutenberg-blocks' ),
			)
		);

	}

	/**
	 * [disable_gutenberg_blocks_add_menu description]
	 */
	public function register_sub_menu() {

		add_submenu_page(
			'options-general.php',
			esc_html__( 'Disable Blocks', 'disable-gutenberg-blocks' ),
			esc_html__( 'Disable Blocks', 'disable-gutenberg-blocks' ),
			'activate_plugins',
			'disable-blocks',
			array( $this, 'submenu_page_callback' )
		);

	}

	/**
	 * [admin description]
	 */
	public function submenu_page_callback() {

		$table = new DGB_List_Table();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Disable Gutenberg Blocks', 'disable-gutenberg-blocks' ); ?></h2>
			<p><?php printf( __( 'You currently have %s blocks installed.', 'disable-gutenberg-blocks' ), '<span class="block-count"></span>' ); ?></p>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php $table->display(); ?>
							</form>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
			<?php

	}

	/**
	 * [process_bulk_action description]
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered...
		if ( isset( $_GET['action'] ) && 'disable' === $_GET['action'] ) {

			// In our file that handles the request, verify the nonce.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			$block = ( isset( $_GET['block'] ) ? sanitize_text_field( wp_unslash( $_GET['block'] ) ) : '' );

			if ( ! wp_verify_nonce( $nonce, 'dgb_nonce' ) ) {
				die( 'Not today.' );
			} else {
				$this->disable_block( $block );
				wp_safe_redirect( admin_url( 'admin.php?page=disable-blocks' ) );
				exit();
			}
		}

		// Detect when a bulk action is being triggered...
		if ( isset( $_GET['action'] ) && 'enable' === $_GET['action'] ) {

			// In our file that handles the request, verify the nonce.
			$nonce = ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			$block = ( isset( $_GET['block'] ) ? sanitize_text_field( wp_unslash( $_GET['block'] ) ) : '' );

			if ( ! wp_verify_nonce( $nonce, 'dgb_nonce' ) ) {
				die( 'Not today.' );
			} else {
				$this->enable_block( $block );
			}

			wp_safe_redirect( admin_url( 'admin.php?page=disable-blocks' ) );
			exit();
		}

		// If the disable enable action is triggered.
		if ( ( isset( $_POST['action'] ) && 'bulk-enable' === $_POST['action'] )
		|| ( isset( $_POST['action2'] ) && 'bulk-enable' === $_POST['action2'] )
		) {

			$bulk_change_ids = ( isset( $_POST['bulk-change'] ) ? $_POST['bulk-change'] : array() );

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

		$blocks = (array) get_option( 'dgb_disabled_blocks', array() );
		if ( ! in_array( $name, $blocks, true ) ) {
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

		$blocks     = (array) get_option( 'dgb_disabled_blocks', array() );
		$new_blocks = array();
		if ( in_array( $name, $blocks, true ) ) {
			$new_blocks = array_diff( $blocks, array( $name ) );
		}
		update_option( 'dgb_disabled_blocks', $new_blocks );

	}

}

new DGB_Admin_Page();

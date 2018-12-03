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
		add_action( 'admin_menu', array( $this, 'register_sub_menu' ), 50 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 10 );
	}

	/**
	 * Enqueue the scripts and styles.
	 *
	 * @param string $hook The current page ID.
	 */
	public function enqueue( $hook ) {

		wp_enqueue_style( 'dgb-admin', plugins_url( 'css/style.css', __FILE__ ), array(), '1.0.0' );
		wp_add_inline_script(
			'wp-blocks',
			sprintf( 'wp.blocks.setCategories( %s );', wp_json_encode( gutenberg_get_block_categories( get_post() ) ) ),
			'after'
		);
		do_action( 'enqueue_block_editor_assets' );
		wp_dequeue_script( 'disable-gutenberg-blocks' );

		$local_arr = array(
			'disabledBlocks' => get_option( 'dgb_disabled_blocks', array() ),
			'nonce'          => wp_create_nonce( 'dgb_nonce' ),
		);

		wp_enqueue_script( 'dgf-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'wp-blocks', 'wp-element', 'wp-data', 'wp-components', 'wp-block-library', 'wp-editor' ), '1.0.0', true );
		wp_localize_script( 'dgf-admin', 'dgb_object', $local_arr );
	}

	/**
	 * [disable_gutenberg_blocks_add_menu description]
	 */
	public function register_sub_menu() {
		add_submenu_page(
			'gutenberg',
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
		$count = $table->get_row_count();
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

}

new DGB_Admin_Page();

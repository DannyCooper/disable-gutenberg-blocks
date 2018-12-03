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
		add_action( 'admin_enqueue_scripts', array( $this, 'stylesheet' ) );
	}

	/**
	 * Enqueue the stylesheet.
	 *
	 * @param string $hook The current page ID.
	 */
	public function stylesheet( $hook ) {
		if ( 'gutenberg_page_disable-blocks' !== $hook ) {
				return;
		}
		wp_enqueue_style( 'dgf-admin-css', plugins_url( 'css/style.css', __FILE__ ), array(), '1.0.0' );
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
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Disable Gutenberg Blocks', 'disable-gutenberg-blocks' ); ?></h2>
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

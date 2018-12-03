<?php
/**
 * Disable Gutenberg Blocks
 *
 * Plugin Name: Disable Gutenberg Blocks
 * Plugin URI:  https://wordpress.org/plugins/disable-gutenberg-blocks/
 * Description: Remove unwanted blocks from Gutenberg editor.
 * Version:     1.0.1
 * Author:      Danny Cooper
 * Author URI:  https://editorblocks.com/disable-gutenberg-blocks
 * Text Domain: disable-gutenberg-blocks
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package   disable-gutenberg-blocks
 * @copyright Copyright (c) 2018, Danny Cooper
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DGB_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Main Disable_Gutenberg_Blocks Class.
 */
class Disable_Gutenberg_Blocks {

	/**
	 * Array of blocks.
	 *
	 * @var Disable_Gutenberg_Blocks The one instance.
	 */
	private static $instance = null;

	/**
	 * The object is created from within the class itself
	 * only if the class has no instance.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Disable_Gutenberg_Blocks();
		}

		return self::$instance;
	}

	/**
	 * Initialize plugin.
	 */
	private function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ) );
		require_once DGB_PLUGIN_DIR_PATH . 'class-dgb-admin-page.php';
	}

	/**
	 * Enqueue the scripts.
	 */
	public function enqueue() {
		wp_enqueue_script( 'disable-gutenberg-blocks', plugins_url( 'js/scripts.js', __FILE__ ), array( 'wp-edit-post' ), '1.0.0', false );
		wp_localize_script( 'disable-gutenberg-blocks', 'dgb_blocks', $this->get_disabled_blocks() );
	}

	/**
	 * Get all blocks.
	 */
	public function get_disabled_blocks() {
		return (array) get_option( 'dgb_disabled_blocks', array() );
	}
}

/**
 * The main function for that returns Disable_Gutenberg_Blocks.
 */
function DGF() {
	return Disable_Gutenberg_Blocks::instance();
}

/**
 * Detect plugin. For use on Front End only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
	DGF();
}

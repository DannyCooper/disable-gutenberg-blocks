<?php
/**
 * Disable Gutenberg Blocks - Block Manager
 *
 * Plugin Name: Disable Gutenberg Blocks - Block Manager
 * Plugin URI:  https://wordpress.org/plugins/disable-gutenberg-blocks/
 * Description: Remove unwanted blocks from the Gutenberg Block Inserter.
 * Version:     1.0.8
 * Author:      Danny Cooper
 * Author URI:  https://editorblockswp.com
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
		add_filter( 'plugin_action_links_' . plugin_basename( DGB_PLUGIN_DIR_PATH . 'class-disable-gutenberg-blocks.php' ), array( $this, 'links' ) );

	}

	/**
	 * Enqueue the scripts.
	 */
	public function enqueue() {

		if ( function_exists( 'gutenberg_get_block_categories' ) ) {
				$scripts = 'js/scripts-old.js';
		} elseif ( function_exists( 'get_block_categories' ) ) {
				$scripts = 'js/scripts.js';
		}

		wp_enqueue_script( 'disable-gutenberg-blocks', plugins_url( $scripts, __FILE__ ), array( 'wp-edit-post' ), '1.0.0', false );
		wp_localize_script( 'disable-gutenberg-blocks', 'dgb_blocks', $this->get_disabled_blocks() );

	}

	/**
	 * Get all blocks.
	 */
	public function get_disabled_blocks() {

		return (array) get_option( 'dgb_disabled_blocks', array() );

	}

	/**
	 * Add custom links to plugin settings page.
	 *
	 * @param array $links Current links array.
	 */
	public function links( $links ) {

		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=disable-blocks' ) ) . '">' . esc_html__( 'Disable Blocks', 'disable-gutenberg-blocks' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;

	}

}

/**
 * The main function for that returns Disable_Gutenberg_Blocks.
 */
function DGB() {

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( is_plugin_active( 'gutenberg/gutenberg.php' ) || version_compare( get_bloginfo( 'version' ), '4.9.9', '>' ) ) {
		Disable_Gutenberg_Blocks::instance();
	}

}

add_action( 'plugins_loaded', 'DGB', 100 );

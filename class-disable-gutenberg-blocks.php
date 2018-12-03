<?php
/**
 * Disable Gutenberg Blocks
 *
 * Plugin Name: Disable Gutenberg Blocks
 * Plugin URI:  https://wordpress.org/plugins/disable-gutenberg-blocks/
 * Description: Remove unwanted blocks from Gutenberg editor.
 * Version:     1.0.0
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
	 * Array of blocks.
	 *
	 * @var array
	 */
	public $blocks;

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
	}

	/**
	 * Enqueue the scripts.
	 */
	public function enqueue() {
		wp_enqueue_script( 'disable-gutenberg-blocks', plugins_url( 'js/scripts.js', __FILE__ ), array( 'wp-edit-post' ), '1.0.0', false );
		wp_localize_script( 'disable-gutenberg-blocks', 'dgb_blocks', $this->get_disabled_blocks() );
	}

	/**
	 * Add blocks to the array.
	 *
	 * @param string $name Name of json file to get.
	 */
	public function add_blocks( $name ) {
		$blocks_json = file_get_contents( DGB_PLUGIN_DIR_PATH . "json/${name}.json" );
		$array       = json_decode( $blocks_json );

		foreach ( $array as $block ) {
			$new['name']        = ( isset( $block->name ) ? $block->name : '' );
			$new['title']       = ( isset( $block->title ) ? $block->title : '' );
			$new['category']    = ( isset( $block->category ) ? $block->category : '' );
			$new['description'] = ( isset( $block->description ) ? $block->description : '' );

			$this->blocks[] = $new;
		}

	}

	/**
	 * Get all blocks.
	 */
	public function get_blocks() {
		return $this->blocks;
	}

	/**
	 * Get all blocks.
	 */
	public function get_disabled_blocks() {
		return (array) get_option( 'dgb_disabled_blocks', array() );
	}
}

/**
 * The main function for that returns Easy_Digital_Downloads
 */
function DGF() {
	return Disable_Gutenberg_Blocks::instance();
}
DGF();

/**
 * Remove all Gutenberg Blocks.
 */
function gdb_add_all_blocks() {

	DGF()->add_blocks( 'core' );

	$block_plugins = array(
		'advanced-gutenberg'        => 'advanced-gutenberg/advanced-gutenberg.php',
		'advanced-gutenberg-blocks' => 'advanced-gutenberg-blocks/plugin.php',
		'atomic-blocks'             => 'atomic-blocks/atomicblocks.php',
		'blockgallery'              => 'block-gallery/class-block-gallery.php',
		'bokez'                     => 'bokez-awesome-gutenberg-blocks/plugin.php',
		'caxton'                    => 'caxton/caxton.php',
		'coblocks'                  => 'coblocks/class-coblocks.php',
		'editor-blocks'             => 'editor-blocks/plugin.php',
		'ghostkit'                  => 'ghostkit/class-ghost-kit.php',
		'kadence'                   => 'kadence-blocks/kadence-blocks.php',
		'matt-watson'               => 'secure-blocks-for-gutenberg/plugin.php',
		'sgb'                       => 'stag-blocks/stag-blocks.php',
		'themeisle-blocks'          => 'themeisle-companion/themeisle-companion.php',
		'ugb'                       => 'stackable-ultimate-gutenberg-blocks/plugin.php',
		'wpzoom-recipe-card'        => 'recipe-card-blocks-by-wpzoom/wpzoom-recipe-card.php',
		'woocommerce'               => 'woo-gutenberg-products-block/woocommerce-gutenberg-products-block.php',
	);

	foreach ( $block_plugins as $name => $file ) {
		if ( is_plugin_active( $file ) ) {
			DGF()->add_blocks( $name );
		}
	}

}
add_action( 'admin_init', 'gdb_add_all_blocks' );

require_once DGB_PLUGIN_DIR_PATH . 'class-dgb-admin-page.php';

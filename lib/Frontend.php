<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/s3rgiosan/wpjaco/
 * @since      1.0.0
 *
 * @package    Jaco
 * @subpackage Jaco/lib
 */

namespace s3rgiosan\Jaco;

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Jaco
 * @subpackage Jaco/lib
 * @author     Sérgio Santos <me@s3rgiosan.com>
 */
class Frontend {

	/**
	 * The plugin's instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Plugin
	 */
	private $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Add custom javascript within head section.
	 *
	 * @since 1.0.0
	 */
	public function add_snippet() {

		if ( \is_admin() ) {
			return;
		}

		if ( \is_feed() ) {
			return;
		}

		if ( \is_robots() ) {
			return;
		}

		if ( \is_trackback() ) {
			return;
		}

		$disable_recording = boolval( \get_post_meta( \get_the_id(), 'jaco_disable_rec', true ) );

		// Disable recording for this content type.
		if ( $disable_recording ) {
			return;
		}

		$snippet = trim( \get_option( 'jaco_snippet' ) );

		if ( empty( $snippet ) ) {
			return;
		}

		echo $snippet;
	}
}

<?php
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       https://github.com/s3rgiosan/wpjaco/
 * @since      1.0.0
 *
 * @package    Jaco
 * @subpackage Jaco/lib
 */

namespace s3rgiosan\Jaco;

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @package    Jaco
 * @subpackage Jaco/lib
 * @author     Sérgio Santos <me@s3rgiosan.com>
 */
class Admin {

	/**
	 * The plugin's instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Plugin
	 */
	private $plugin;

	/**
	 * The unique identifier of this plugin settings group name.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $settings_name = 'jaco_settings';

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
	 * The settings group name.
	 *
	 * @since  1.0.0
	 * @return string The settings group name.
	 */
	public function get_settings_name() {
		return $this->settings_name;
	}

	/**
	 * Add sub menu page to the Settings menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_settings_menu() {

		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		\add_options_page(
			\__( 'Jaco', 'wpjaco' ),
			\__( 'Jaco', 'wpjaco' ),
			'manage_options',
			'jaco',
			array( $this, 'display_options_page' )
		);

	}

	/**
	 * Output the content of the settings page.
	 *
	 * @since 1.0.0
	 */
	public function display_options_page() {
	?>
		<div class="wrap">
			<h1><?php \_e( 'Jaco Settings', 'wpjaco' ); ?></h1>
			<form action='options.php' method='post'>
			<?php
				\settings_fields( $this->get_settings_name() );
				\do_settings_sections( $this->get_settings_name() );
				\submit_button();
			?>
			</form>
		</div>
	<?php
	}

	/**
	 * Register groups of settings and their fields.
	 *
	 * @since 1.0.0
	 */
	public function admin_settings_init() {
		$this->register_settings_sections();
		$this->register_settings_fields();
	}

	/**
	 * Register groups of settings.
	 *
	 * @since 1.0.0
	 */
	public function register_settings_sections() {

		\add_settings_section(
			'jaco_settings_section',
			'',
			null,
			$this->get_settings_name()
		);

	}

	/**
	 * Register settings fields.
	 *
	 * @since 1.0.0
	 */
	public function register_settings_fields() {
		$this->register_snippet_field();
	}

	/**
	 * Register the snippet field.
	 *
	 * @since 1.0.0
	 */
	public function register_snippet_field() {

		\register_setting(
			$this->get_settings_name(),
			'jaco_snippet',
			''
		);

		\add_settings_field(
			'jaco_snippet',
			\__( 'Snippet Code', 'wpjaco' ),
			array( $this, 'display_snippet_field' ),
			$this->get_settings_name(),
			'jaco_settings_section',
			array(
				'label_for' => 'jaco_snippet',
			)
		);

	}

	/**
	 * Output the snippet field.
	 *
	 * @since 1.0.0
	 */
	public function display_snippet_field() {

		printf(
			'<textarea rows="10" id="%1$s" name="%1$s" class="widefat" style="font-family: Courier New;">%2$s</textarea>',
			'jaco_snippet',
			\get_option( 'jaco_snippet' )
		);

		printf(
			'<p class="description">%s</p>',
			\__( 'This code is going to be embedded into your website between tags &lt;head&gt; and &lt;/head&gt;', 'wpjaco' )
		);

	}
	/**
	 * Register settings.
	 *
	 * @since 1.1.0
	 */
	public function register_settings() {

		if ( ! \current_user_can( 'edit_posts' ) ) {
			return;
		}

		$post_types = \wp_cache_get( 'wpjaco_post_types', $this->plugin->get_name() );

		if ( ! $post_types ) {

			$post_types = \get_post_types( array( 'public' => true ) );

			/**
			 * Filter the available post type(s).
			 *
			 * @see https://codex.wordpress.org/Post_Type
			 * @see https://codex.wordpress.org/Post_Types#Custom_Types
			 *
			 * @since  1.0.0
			 * @param  array Name(s) of the post type(s).
			 * @return array Possibly-modified name(s) of the post type(s).
			 */
			$post_types = \apply_filters( 'wpjaco_post_types', \get_post_types( array(
				'public' => true,
			) ) );

			\wp_cache_set( 'wpjaco_post_types', $post_types, $this->plugin->get_name(), 600 );
		}

		foreach ( $post_types as $post_type ) {
			\add_meta_box(
				'wpjaco_settings',
				\__( 'Jaco Settings', 'wpjaco' ),
				array( $this, 'display_settings' ),
				$post_type
			);
		}
	}

	/**
	 * Output the settings meta box.
	 *
	 * @since 1.1.0
	 * @param \WP_Post $post Current post object.
	 */
	public function display_settings( $post ) {

		\wp_nonce_field( \plugin_basename( __FILE__ ), 'jaco_settings_meta_box_nonce' );

		echo '<table class="form-table"><tbody>';
		$this->display_disable_fields( $post );
		echo '</tbody></table>';
	}

	/**
	 * Output the disable fields.
	 *
	 * @since 1.1.0
	 * @param \WP_Post $post Current post object.
	 */
	public function display_disable_fields( $post ) {
		echo '<tr>';
		printf(
			'<th scope="row"><label for="%s">%s:</label></th>',
			\esc_attr( 'jaco_disable_rec' ),
			\__( 'Disable Recording', 'wpjaco' )
		);

		printf(
			'<td><input type="checkbox" id="%1$s" name="%1$s" value="1"%2$s></td>',
			'jaco_disable_rec',
			\checked( \get_post_meta( $post->ID, 'jaco_disable_rec', true ), 1, false )
		);
		echo '</tr>';
	}

	/**
	 * Save settings.
	 *
	 * @since 1.1.0
	 * @param int $post_id The post ID.
	 */
	public function save_settings( $post_id ) {

		// Verify meta box nonce
		if ( ! isset( $_POST['jaco_settings_meta_box_nonce'] ) ||
			! \wp_verify_nonce( $_POST['jaco_settings_meta_box_nonce'], \plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// Bail out if post is an autosave
		if ( \wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Bail out if post is a revision
		if ( \wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Bail out if current user can't edit posts
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Update/delete the analytics tag
		if ( ! empty( $_POST['jaco_disable_rec'] ) ) {
			\update_post_meta( $post_id, 'jaco_disable_rec', boolval( $_POST['jaco_disable_rec'] ) );
		} else {
			\delete_post_meta( $post_id, 'jaco_disable_rec' );
		}
	}
}

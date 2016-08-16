<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpthemes.com
 * @since      1.0.0
 *
 * @package    WP_Call_To_Action
 * @subpackage WP_Call_To_Action/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Call_To_Action
 * @subpackage WP_Call_To_Action/admin
 * @author     Pea <pea@misfist.com>
 */
class WP_Call_To_Action_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		$screen = get_current_screen();
		if ( WP_CALL_TO_ACTION_POST_TYPE_CTA === $screen->id ) {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin' . $min . '.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Manage column head in admin listing.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns An array of column names.
	 * @return array Modified array of column names.
	 */
	function usage_column_head( $columns ) {

		$new_columns['cb']     = '<input type="checkbox" />';
		$new_columns['title']  = $columns['title'];
		$new_columns['id']     = _x( 'ID', 'column name', 'wp-call-to-action' );
		$new_columns['usage']  = __( 'Usage', 'wp-call-to-action' );
		$new_columns['date']   = $columns['date'];
		return $new_columns;

	}

	/**
	 * Content for extra column in admin listing.
	 *
	 * @since    1.0.0
	 *
	 * @param array $column_name The name of the column to display.
	 * @param array $post_id The current post ID.
	 */
	function usage_column_content( $column_name, $post_id ) {

		switch ( $column_name ) {
			case 'id':
				echo $post_id;
			break;

			case 'usage':
				echo '<code>[wp_cta id="' . $post_id . '"]</code>';
			break;

			default:
			break;
		}

	}

	/**
	 * Hide publishing actions in edit page.
	 *
	 * @since    1.0.0
	 */
	public function hide_publishing_actions() {
		global $post;
		if ( WP_CALL_TO_ACTION_POST_TYPE_CTA !== $post->post_type ) {
			return;
		}
	?>
    <style type="text/css">
    #misc-publishing-actions,#minor-publishing-actions{
      display:none;
    }
    </style>
    <?php
	return;
	}

	/**
	 * Customize post row actions.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $actions An array of row action links.
	 * @param WP_Post $post The post object.
	 */
	function customize_row_actions( $actions, $post ) {

		if ( WP_CALL_TO_ACTION_POST_TYPE_CTA === $post->post_type ) {

			unset( $actions['inline hide-if-no-js'] );

		}

		return $actions;

	}

	/**
	 * Add meta boxes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type Post type.
	 */
	function add_cta_meta_boxes( $post_type ) {

		// Bail if not our post type.
		if ( WP_CALL_TO_ACTION_POST_TYPE_CTA !== $post_type ) {
			return;
		}

		$screens = array( WP_CALL_TO_ACTION_POST_TYPE_CTA );

		foreach ( $screens as $screen ) {

			add_meta_box(
				'wp_call_to_action_detail_content_id',
				__( 'Call To Action Info', 'wp-call-to-action' ),
				array( $this,'cta_meta_box_callback' ),
				$screen,
				'side',
				'high'
			);
			add_meta_box(
				'wp_call_to_action_usage_content_id',
				__( 'Usage', 'wp-call-to-action' ),
				array( $this, 'usage_meta_box_callback' ),
				$screen,
				'side'
			);
			add_meta_box(
				'wp_call_to_action_style_content_id',
				__( 'Call To Action Design', 'wp-call-to-action' ),
				array( $this, 'cta_design_meta_box_callback' ),
				$screen,
				'normal',
				'high'
			);

		}

	}

	/**
	 * Callback for CTA design metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	function cta_design_meta_box_callback( $post ) {

		$cta_custom_class = get_post_meta( $post->ID, '_cta_custom_class', true );
		$cta_theme     = get_post_meta( $post->ID, '_cta_theme', true );

	?>

    <?php wp_nonce_field( plugin_basename( __FILE__ ), 'wp_cta_design_nonce' ); ?>

    <div id="main-cta-detail-wrap">
      <div class="field-row">
        <div class="field-label">
			<?php _e( 'Theme', 'wp-call-to-action' ); ?>
        </div><!-- .field-label -->
        <div class="field-content">
          <select name="_cta_theme">
			<option value="no-style"><?php _e( 'No Style', 'wp-call-to-action' ); ?></option>
            <option value="default" <?php selected( $cta_theme, 'default' ); ?>><?php _e( 'Default', 'wp-call-to-action' ); ?></option>
          </select>
        </div><!-- .field-content -->
      </div><!-- .field-row -->
      <div class="field-row">
        <div class="field-label">
			<?php _e( 'Custom Class', 'wp-call-to-action' ); ?>
        </div><!-- .field-label -->
        <div class="field-content">
          <input type="text" name="_cta_custom_class" value="<?php echo esc_attr( $cta_custom_class ) ?>" />
          <br/><em><?php _e( 'This class will be added in the wrapper HTML tag of the Call To Action.', 'wp-call-to-action' ); ?></em>
        </div><!-- .field-content -->
      </div><!-- .field-row -->
    </div><!-- #main-cta-detail-wrap -->
    <?php

	}

	/**
	 * Callback for CTA detail metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	function cta_meta_box_callback( $post ) {

		$cta_button_text            = get_post_meta( $post->ID, '_cta_button_text', true );
		$cta_button_url             = get_post_meta( $post->ID, '_cta_button_url', true );
		$cta_button_open_new_window = get_post_meta( $post->ID, '_cta_button_open_new_window', true );
	?>

    <?php wp_nonce_field( plugin_basename( __FILE__ ), 'wp_cta_detail_nonce' ); ?>

    <div id="main-cta-detail-wrap">
      <div class="field-row">
        <div class="field-label">
			<?php _e( 'Button Text', 'wp-call-to-action' ); ?>
        </div><!-- .field-label -->
        <div class="field-content">
          <input type="text" name="_cta_button_text" value="<?php echo esc_attr( $cta_button_text ) ?>" />
          <br/><em><?php _e( 'Enter button text', 'wp-call-to-action' ); ?></em>
        </div><!-- .field-content -->
      </div><!-- .field-row -->
      <div class="field-row">
        <div class="field-label">
			<?php _e( 'Button URL', 'wp-call-to-action' ); ?>
        </div><!-- .field-label -->
        <div class="field-content">
          <input type="text" name="_cta_button_url" value="<?php echo esc_url( $cta_button_url ) ?>" />
          <br/><em><?php _e( 'Enter full URL', 'wp-call-to-action' ); ?></em>
        </div><!-- .field-content -->
      </div><!-- .field-row -->
      <div class="field-row">
        <div class="field-label">
			<?php _e( 'Open in New Window', 'wp-call-to-action' ); ?>
        </div><!-- .field-label -->
        <div class="field-content">
          <input type="hidden" name="_cta_button_open_new_window" value="0" />
          <input type="checkbox" name="_cta_button_open_new_window" value="1" <?php checked( $cta_button_open_new_window, 1 ); ?> />
			<?php _e( 'Check to enable', 'wp-call-to-action' ); ?>
        </div><!-- .field-content -->
      </div><!-- .field-row -->

    </div><!-- #main-cta-detail-wrap -->
    <?php

	}

	/**
	 * Save CTA detail metabox fields.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Current Post ID.
	 */
	function save_cta_detail_meta_box( $post_id ) {

		if ( WP_CALL_TO_ACTION_POST_TYPE_CTA !== get_post_type( $post_id ) ) {
			return $post_id;
		}

		// Bail if we're doing an auto save.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// If our nonce isn't there, or we can't verify it, bail.
		if ( ! isset( $_POST['wp_cta_detail_nonce'] ) || ! wp_verify_nonce( $_POST['wp_cta_detail_nonce'], plugin_basename( __FILE__ ) ) ) {
			return $post_id;
		}

		// If our current user can't edit this post, bail.
		if ( ! current_user_can( 'edit_post' , $post_id ) ) {
			return $post_id;
		}

		// Get posted data.
		$cta_button_text            = sanitize_text_field( $_POST['_cta_button_text'] );
		$cta_button_url             = esc_url_raw( $_POST['_cta_button_url'] );
		$cta_button_open_new_window = esc_attr( $_POST['_cta_button_open_new_window'] );

		// Save now.
		update_post_meta( $post_id, '_cta_button_text', $cta_button_text );
		update_post_meta( $post_id, '_cta_button_url', $cta_button_url );
		update_post_meta( $post_id, '_cta_button_open_new_window', $cta_button_open_new_window );

		return $post_id;

	}
	/**
	 * Save CTA design metabox fields.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Current Post ID.
	 */
	function save_cta_design_meta_box( $post_id ) {

		if ( WP_CALL_TO_ACTION_POST_TYPE_CTA !== get_post_type( $post_id ) ) {
			return $post_id;
		}

		// Bail if we're doing an auto save.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// If our nonce isn't there, or we can't verify it, bail.
		if ( ! isset( $_POST['wp_cta_design_nonce'] ) || ! wp_verify_nonce( $_POST['wp_cta_design_nonce'], plugin_basename( __FILE__ ) ) ) {
			return $post_id;
		}

		// If our current user can't edit this post, bail.
		if ( ! current_user_can( 'edit_post' , $post_id ) ) {
			return $post_id;
		}

		// Get posted data.
		$cta_custom_class = sanitize_key( $_POST['_cta_custom_class'] );
		$cta_theme     = sanitize_key( $_POST['_cta_theme'] );

		// Save now.
		update_post_meta( $post_id, '_cta_custom_class', $cta_custom_class );
		update_post_meta( $post_id, '_cta_theme', $cta_theme );

		return $post_id;

	}


	/**
	 * Callback for usage metabox.
	 *
	 * @since    1.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	function usage_meta_box_callback( $post ) {

	?>
    <h4><?php _e( 'Shortcode', 'wp-call-to-action' ); ?></h4>
    <p><?php _e( 'Copy and paste this shortcode directly into any WordPress post or page.', 'wp-call-to-action' ); ?></p>
    <textarea class="large-text code" readonly="readonly"><?php echo '[wp_cta id="'.$post->ID.'"]'; ?></textarea>

    <h4><?php _e( 'Template Include', 'wp-call-to-action' ); ?></h4>
    <p><?php _e( 'Copy and paste this code into a template file to include the slider within your theme.', 'wp-call-to-action' ); ?></p>
    <textarea class="large-text code" readonly="readonly">&lt;?php echo do_shortcode("[wp_cta id='<?php echo $post->ID; ?>']"); ?&gt;</textarea>
    <?php

	}
}

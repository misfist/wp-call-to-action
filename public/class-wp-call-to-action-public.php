<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpthemes.com
 * @since      1.0.0
 *
 * @package    WP_Call_To_Action
 * @subpackage WP_Call_To_Action/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Call_To_Action
 * @subpackage WP_Call_To_Action/public
 * @author     Pea <pea@misfist.com>
 */
class WP_Call_To_Action_Public {

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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/public' . $min . '.css', array(), $this->version, 'all' );

	}

	/**
	 * Register custom post type.
	 *
	 * @since    1.0.0
	 */
	public function custom_post_types() {

		// Register Call To Action Post Type.
		$labels = array(
			'name'               => _x( 'Call To Actions', 'post type general name', 'wp-call-to-action' ),
			'singular_name'      => _x( 'Call To Action', 'post type singular name', 'wp-call-to-action' ),
			'menu_name'          => _x( 'Call To Action', 'admin menu', 'wp-call-to-action' ),
			'name_admin_bar'     => _x( 'Call To Action', 'add new on admin bar', 'wp-call-to-action' ),
			'add_new'            => _x( 'Add New', 'wp_cta', 'wp-call-to-action' ),
			'add_new_item'       => __( 'Add New Call To Action', 'wp-call-to-action' ),
			'new_item'           => __( 'New Call To Action', 'wp-call-to-action' ),
			'edit_item'          => __( 'Edit Call To Action', 'wp-call-to-action' ),
			'view_item'          => __( 'View Call To Action', 'wp-call-to-action' ),
			'all_items'          => __( 'All Call To Actions', 'wp-call-to-action' ),
			'search_items'       => __( 'Search Call To Actions', 'wp-call-to-action' ),
			'parent_item_colon'  => __( 'Parent Call To Actions:', 'wp-call-to-action' ),
			'not_found'          => __( 'No call to actions found.', 'wp-call-to-action' ),
			'not_found_in_trash' => __( 'No call to actions found in Trash.', 'wp-call-to-action' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-megaphone',
			'show_in_rest'       => true,
	  		'rest_base'          => 'wp-cta',
	  		'rest_controller_class' => 'WP_REST_Posts_Controller',
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
		);

		register_post_type( WP_CALL_TO_ACTION_POST_TYPE_CTA, $args );

	}

	/**
	 * Callback function of shortcode `wp_cta`.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Attributes.
	 * @return string Shortcode output.
	 */
	public function shortcode_cb_wp_cta( $atts ) {

		$atts = shortcode_atts( array(
			'id' => '',
		), $atts, 'WLS' );

		$atts['id'] = absint( $atts['id'] );

		$is_valid_cta = $this->check_if_valid_cta( $atts );

		if ( ! $is_valid_cta ) {
			return __( 'Call To Action not found.', 'wp-call-to-action' );
		}

		// Fetch default template.
		$cta_theme = $this->get_default_cta_theme();
		$cta_theme = apply_filters( 'wp_call_to_action_filter_cta_theme', $cta_theme, $atts['id'] );

		// Bail if template is empty.
		if ( empty( $cta_theme ) ) {
			return;
		}

		ob_start();

		$content_display = $this->replace_cta_placeholders( $cta_theme, $atts );

		echo $content_display;

		$output = ob_get_contents();
		ob_end_clean();
		return $output;

	}

	/**
	 * Return default CTA template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template CTA template.
	 * @param array  $args     Arguments.
	 * @return string Modified CTA template.
	 */
	function replace_cta_placeholders( $template, $args ) {

		$post_id = $args['id'];
		$post_obj = get_post( $post_id );

		if ( null === $post_obj ) {
			return $template;
		}

		// Meta.
		if ( has_post_thumbnail( $post_id ) ) {
			$cta_image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
			$cta_image = $cta_image_array[0];
		}
		$cta_button_text = get_post_meta( $post_id, '_cta_button_text', true );
		$cta_button_url = get_post_meta( $post_id, '_cta_button_url', true );
		$cta_button_open_new_window = get_post_meta( $post_id, '_cta_button_open_new_window', true );
		$cta_custom_class = get_post_meta( $post_id, '_cta_custom_class', true );

		// Prepare classes array.
		$custom_class_array = array(
			'wp-cta-widget'
			);
		if ( ! empty( $cta_custom_class ) ) {
			$custom_class_array[] = $cta_custom_class;
		}
		$cta_custom_class = apply_filters( 'wp_call_to_action_filter_custom_class', $custom_class_array, $post_id );

		$custom_class_text = '';
		if ( ! empty( $cta_custom_class ) ) {
			array_walk( $cta_custom_class, 'sanitize_key' );
			$custom_class_text = implode( ' ', $cta_custom_class );
		}

		// Preparing search replace array.
		$search_array = array();
		$replace_array = array();

		// Title.
		$search_array[] = '{{title}}';
		$title_content = '';
		if ( ! empty( $post_obj->post_title ) ) {
			$title_content = '<h4 class="wp-cta-title">' . esc_html( $post_obj->post_title ) . '</h4><!-- .wp-cta-title -->';
		}
		$replace_array[] = $title_content;

		// Description.
		$search_array[] = '{{description}}';
		$description_content = '';
		if ( ! empty( $post_obj->post_content ) ) {
			$description_content = '<div class="wp-cta-content">' . apply_filters( 'the_content', $post_obj->post_content ) . '</div><!-- .wp-cta-content -->';
		}
		$replace_array[] = $description_content;

		//Image
		$search_array[] = '{{image}}';

		//Prepare image.
		$image_content = '';
		if( $cta_image ) {
			$image_content .= '<div class="wp-cta-image">';
			$image_content .= '<img src="';
			$image_content .= esc_url( $cta_image );
			$image_content .= '">';
			$image_content .= '</div>';
		}

		$replace_array[] = $image_content;

		// Button.
		$search_array[] = '{{button}}';

		// Preparing button.
		$button_content = '';
		if ( ! empty( $cta_button_text ) && ! empty( $cta_button_url ) ) {
			$button_content = '';
			$button_content .= '<a class="wp-cta-button btn" href="' . esc_url( $cta_button_url ) . '"';
			if ( 1 === absint( $cta_button_open_new_window ) ) {
				$button_content .= ' target="_blank" ';
			}
			$button_content .= '>';
			$button_content .= esc_html( $cta_button_text );
			$button_content .= '</a>';
		}
		$replace_array[] = $button_content;

		// Custom class.
		$search_array[] = '{{custom_class}}';
		$replace_array[] = $custom_class_text;

		// Custom ID.
		$search_array[] = '{{custom_id}}';
		$replace_array[] = 'wp-cta-' . $post_obj->ID;

		$output = '';
		$output = str_replace( $search_array, $replace_array, $template );

		return $output;

	}

	/**
	 * Return default CTA template.
	 *
	 * @since 1.0.0
	 */
	function get_default_cta_theme() {

		$output = '';

		$output .= '<div id="{{custom_id}}" class="{{custom_class}}">';
		$output .= '{{title}}';
		$output .= '{{description}}';
		$output .= '{{image}}';
		$output .= '{{button}}';
		$output .= '</div>';

		$output = apply_filters( 'wp_call_to_action_filter_default_cta_theme', $output );

		return $output;

	}

	/**
	 * Add extra custom class in CTA wrap.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes The array of classes.
	 * @param int   $post_id The Post ID.
	 * @return array  Modified array of classes.
	 */
	function add_extra_custom_class( $classes, $post_id ) {

		$cta_theme = get_post_meta( $post_id, '_cta_theme', true );

		if ( ! empty( $cta_theme ) && 'no-style' !== $cta_theme ) {

			$classes[] = 'wp-cta-template-' . esc_attr( $cta_theme );

		}

		return $classes;

	}

	/**
	 * Check if given id is valid CTA.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments.
	 * @return bool True if CTA is valid, otherwise false.
	 */
	private function check_if_valid_cta( $args ) {

		$output = false;

		if ( isset( $args['id'] ) && intval( $args['id'] ) > 0  ) {

			$cta = get_post( intval( $args['id'] ) );

			if ( ! empty( $cta ) && 'publish' === $cta->post_status && WP_CALL_TO_ACTION_POST_TYPE_CTA === $cta->post_type ) {
				$output = true;
			}
		}
		return $output;

	}
}

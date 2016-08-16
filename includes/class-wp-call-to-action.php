<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wpthemes.com
 * @since      1.0.0
 *
 * @package    WP_Call_To_Action
 * @subpackage WP_Call_To_Action/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Call_To_Action
 * @subpackage WP_Call_To_Action/includes
 * @author     Pea <pea@misfist.com>
 */
class WP_Call_To_Action {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Call_To_Action_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Instance of plugin admin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_admin    The object of plugin admin class.
	 */
	protected $plugin_admin;

	/**
	 * Instance of plugin public.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_public    The object of plugin public class.
	 */
	protected $plugin_public;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wp-call-to-action';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->plugin_admin  = new WP_Call_To_Action_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->plugin_public = new WP_Call_To_Action_Public( $this->get_plugin_name(), $this->get_version() );
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_short_codes();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Call_To_Action_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Call_To_Action_i18n. Defines internationalization functionality.
	 * - WP_Call_To_Action_Admin. Defines all hooks for the admin area.
	 * - WP_Call_To_Action_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-call-to-action-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-call-to-action-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-call-to-action-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-call-to-action-public.php';

		$this->loader = new WP_Call_To_Action_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Call_To_Action_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Call_To_Action_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the shortcodes.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_short_codes() {

		add_shortcode( 'wp_cta', array( $this->plugin_public, 'shortcode_cb_wp_cta' ) );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = $this->plugin_admin;

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		// Add Admin column.
		$this->loader->add_filter( 'manage_'.WP_CALL_TO_ACTION_POST_TYPE_CTA.'_posts_columns', $plugin_admin, 'usage_column_head' );
		$this->loader->add_action( 'manage_'.WP_CALL_TO_ACTION_POST_TYPE_CTA.'_posts_custom_column', $plugin_admin, 'usage_column_content', 10, 2 );

		// Add metaboxes.
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_cta_meta_boxes' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_cta_detail_meta_box' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_cta_design_meta_box' );

		// Row actions.
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'customize_row_actions', 10, 2 );

		// Hide publishing actions.
		$this->loader->add_action( 'admin_head-post.php', $plugin_admin, 'hide_publishing_actions' );
		$this->loader->add_action( 'admin_head-post-new.php', $plugin_admin, 'hide_publishing_actions' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = $this->plugin_public;

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

		// Register custom post type.
		$this->loader->add_filter( 'init', $plugin_public, 'custom_post_types' );

		// Add default class.
		$this->loader->add_filter( 'wp_call_to_action_filter_custom_class', $plugin_public, 'add_extra_custom_class', 10, 2 );

		// Enable shortcode in Text widget.
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WP_Call_To_Action_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}

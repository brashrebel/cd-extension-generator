<?php
/*
Plugin Name: {plugin_name}
Description: {plugin_description}
Version: 0.1.0
Author: {author_name}
{if:author_uri}
Author URI: {author_uri}
{endif}
*/

if ( ! function_exists( '{plugin_ID}_wrapper' ) ) {

	/**
	 * The function to launch our plugin.
	 *
	 * This entire class is wrapped in this function because we have to ensure that Client Dash has been loaded before our
	 * extension.
	 */
	function {plugin_ID}_wrapper() {
		if ( ! class_exists( 'ClientDash' ) ) {

			// Change me! Change me to the name of the notice function at the bottom
			add_action( 'admin_notices', '_{plugin_ID}_notice' );

			return;
		}

		/**
		 * Class MyCDExtension
		 *
		 * The main class for the extension. Be sure to rename this class something that is unique to your extension.
		 * Duplicate classes will break PHP.
		 */
		class {plugin_class} extends ClientDash {

			/**
			 * Your unique ID.
			 *
			 * This will be prefixed on many things throughout the plugin.
			 */
			public static $ID = '{plugin_ID}';

			/**
			 * This is the page that you want your new tab to reside in.
			 */
			private static $page = '{page}';

			/**
			 * Your tab name.
			 *
			 * This is the name of the tab that your plugin's content section will reside in.
			 */
			private static $tab = '{tab}';

{if:do_settings}
			/**
			 * This is the settings tab name.
			 *
			 * All of your plugin settings will reside here. This may also be the name of an existing tab.
			 */
			public static $settings_tab = '{settings_tab}';

{endif}
			/**
			 * This is the section name of your boilerplate.
			 *
			 * This will be the display name of the content section that this plugin's content resides in. If there is only
			 * one content section within the tab, the name will not show.
			 */
			private static $section_name = '{section_name}';

			/**
			 * This is the current version of your plugin. Keep it up to do date!
			 */
			public static $extension_version = '0.1.0';

			/**
			 * This is the path to the plugin.
			 *
			 * Private.
			 */
			public $_path;

			/**
			 * This is the url to the plugin.
			 *
			 * Private.
			 */
			public $_url;

			/**
			 * This constructor function sets up what happens when the plugin is activated. It is where you'll place all your
			 * actions, filters and other setup components.
			 */
			public function __construct() {

				// Register our styles
				add_action( 'admin_init', array( $this, 'register_styles' ) );

				// Add our styles conditionally
				add_action( 'admin_enqueue_scripts', array( $this, 'add_styles' ) );

				// Add our new content section
				$this->add_content_section(
					array(
						'name'     => self::$section_name,
						'tab'      => self::$tab,
						'page'     => self::$page,
						'callback' => array( $this, 'section_output' )
					)
				);

				// Set the plugin path
				$this->_path = plugin_dir_path( __FILE__ );

				// Set the plugin url
				$this->_url = plugins_url( '', __FILE__ );
			}

			/**
			 * Register our styles.
			 */
			public function register_styles() {

				wp_register_style(
					self::$ID . '-style',
					$this->_url . '/style.css',
					null,
					self::$extension_version
				);
			}

			/**
			 * Add our styles.
			 *
			 * If you want the styles to show up on the entire back-end, simply remove all but:
			 * wp_enqueue_style( "$this->$ID-style" );
			 */
			public function add_styles() {

				$page_ID         = self::translate_name_to_id( self::$page );
				$tab_ID          = self::translate_name_to_id( self::$tab );
				$settings_tab_ID = self::translate_name_to_id( self::$settings_tab );

				// Only add style if on extension tab or on extension settings tab
				if ( self::is_cd_page( $page_ID, $tab_ID ) || self::is_cd_page( 'cd_settings', $settings_tab_ID ) ) {
					wp_enqueue_style( self::$ID . '-style' );
				}
			}

			/**
			 * Our section output.
			 *
			 * This is where all of the content section content goes! Add anything you like to this function.
			 */
			public function section_output() {

				// CHANGE THIS
				echo 'This is where your new content section\'s content goes.';
			}
		}

		// Instantiate the class
		${plugin_class} = new {plugin_class}();
{if:do_settings}
		// Include the file for your plugin settings.
		include_once( ${plugin_class}->_path . 'inc/settings.php' );
{endif}
{if:do_widgets}
		// Include the file for your plugin widget.
		include_once( ${plugin_class}->_path . 'inc/widgets.php' );
{endif}
{if:do_menus}
		// Include the file for your plugin menus.
		include_once( ${plugin_class}->_path . 'inc/menus.php' );
{endif}
	}

	add_action( 'plugins_loaded', '{plugin_ID}_wrapper' );
}

if ( ! function_exists( '_{plugin_ID}_notice' ) ) {
	/**
	 * Notices for if CD is not active.
	 */
	function _{plugin_ID}_notice() {

		?>
		<div class="error">
			<p>You have activated a plugin that requires <a href="http://w.org/plugins/client-dash">Client Dash</a>
				version 1.6 or greater.
				Please install and activate <strong>Client Dash</strong> to continue using.</p>
		</div>
	<?php
	}
}
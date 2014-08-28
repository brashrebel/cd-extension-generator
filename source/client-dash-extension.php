<?php
/*
Plugin Name: {plugin}
Description: {description}
Version: 0.1
Author: {name}
Author URI: {site}
*/

/**
 * The function to launch our plugin.
 *
 * This entire class is wrapped in this function because we have
 * to ensure that Client Dash has been loaded before our extension.
 *
 */
function cd_{plugin_u} () {
	if ( ! class_exists( 'ClientDash' ) ) {
		add_action( 'admin_notices', '{dotorgun}_notice' );

		return;
	}

	/**
	 * Class {class}
	 *
	 * The main class for the extension.
	 */
	class {class} extends ClientDash {

		/*
		* These variables you can change
		*/
		// Define the plugin name
		public $plugin = '{plugin}';

		// Setup your prefix
		public $pre = '{dotorgun}';

		// Set this to be name of your content section
		private $section_name = '{section}';

		// Set the tab name
		// NOTE: This tab name can be a settings tab that already
		// exists. It will then just add your settings to that tab
		// (also applies to settings_tab)
		private $tab = '{tab}';

		// Settings tab name (keep even if no settings)
		public $settings_tab = '{plugin}';

		// Set this to the page you want your tab to appear on (Account, Reports, Help, and Webmaster exist in Client Dash)
		private $page = '{page}';

		// The version of your extension. Keep this up to date!
		public $version = '0.1';

		/**
		 * This constructor function sets up what happens when the plugin
		 * is activated. It is where you'll place all your actions, filters
		 * and other setup components.
		 */
		public function __construct() {

			// Register our styles
			add_action( 'admin_init', array( $this, 'register_styles' ) );

			// Add our styles conditionally
			add_action( 'admin_enqueue_scripts', array( $this, 'add_styles' ) );

			// Add our new content section
			$this->add_content_section(
				array(
					'name'     => $this->section_name,
					'page'     => $this->page,
					'tab'      => $this->tab,
					'callback' => array( $this, 'section_output' )
				)
			);
		}

		/**
		 * Register our styles.
		 */
		public function register_styles() {

			wp_register_style(
				$this->pre,
				plugin_dir_url( __FILE__ ) . 'style.css',
				null,
				$this->version
			);
		}

		/**
		 * Add our styles.
		 */
		public function add_styles() {
			$current_page = isset( $_GET['page'] ) ? $_GET['page'] : null;
			$current_tab  = isset( $_GET['tab'] ) ? $_GET['tab'] : null;

			$page_ID         = $this->translate_name_to_id( $this->page );
			$tab_ID          = $this->translate_name_to_id( $this->tab );
			$settings_tab_ID = $this->translate_name_to_id( $this->settings_tab );

			// Only add style if on extension tab or on extension settings tab
			if ( ( $current_page == $page_ID && $current_tab == $tab_ID )
			     || ( $current_page == 'cd_settings' && $current_tab == $settings_tab_ID )
			) {
				wp_enqueue_style( $this->pre );
			}
		}

		/**
		 * Our section output.
		 */
		public function section_output() {
			/************************************
			** This is where your content goes **
			 ***********************************/





			echo '{plugin} is working! {name} deserves a high five.';





			/*****************************************
			 ** This is the end of your new content **
			 ****************************************/
		}
	}

	// Instantiate the class
	new {class}();

	/**
	 * Class {class}_Settings
	 *
	 * This is an optional class for adding a settings page to the Client
	 * Dash interface. If your extension does not need settings, delete
	 * this class.
	 */
	class {class}_Settings extends {class} {

		// Set up our settings section name
		private $section_name = '{plugin} Settings';

		/*
		* Now let's setup our options
		* You can change the strings to be more unique
		* If you change the variable names, you'll need to update the
		* references in the register_settings() and settings_display() functions
		*/
		// A checkbox option
		private $cb_option = '_checkbox';

		// A text field option
		private $text_option = '_text';

		// and a URL/text field option
		private $url_option = '_url';

		/**
		 * The main construct function.
		 */
		function __construct() {

			// Register the extension settings
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// Add our content section
			$this->add_content_section(
				array(
					'name'     => $this->section_name,
					'page'     => 'Settings',
					'tab'      => $this->settings_tab,
					'callback' => array( $this, 'settings_output' )
				)
			);
		}

		/**
		 * Register the extension's settings.
		 */
		public function register_settings() {

			register_setting(
				'cd_options_' . $this->translate_name_to_id( $this->settings_tab ),
				$this->pre . $this->cb_option
			);

			register_setting(
				'cd_options_' . $this->translate_name_to_id( $this->settings_tab ),
				$this->pre . $this->text_option,
				'esc_html' );

			register_setting(
				'cd_options_' . $this->translate_name_to_id( $this->settings_tab ),
				$this->pre . $this->url_option,
				'esc_url_raw' );
		}

		/**
		 * Our settings content.
		 */
		public function settings_output() {

			$checkbox_option_name = $this->pre . $this->cb_option;
			$checkbox_option      = get_option( $checkbox_option_name );
			$text_option          = $this->pre . $this->text_option;
			$url_option           = $this->pre . $this->url_option;
			?>
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row"><h3><?php echo $this->plugin; ?> settings</th>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $checkbox_option_name; ?>">Checkbox setting</label>
					</th>
					<td><input type="hidden" name="<?php echo $checkbox_option_name; ?>" value="0"/>
						<input type="checkbox" name="<?php echo $checkbox_option_name; ?>"
						       id="<?php echo $checkbox_option_name; ?>"
						       value="1" <?php checked( '1', $checkbox_option ); ?> />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $text_option; ?>">Text setting</label>
					</th>
					<td><input type="text"
					           id="<?php echo $text_option; ?>"
					           name="<?php echo $text_option; ?>"
					           value="<?php echo get_option( $text_option ); ?>"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $url_option; ?>">URL setting</label>
					</th>
					<td><input type="text"
					           id="<?php echo $url_option; ?>"
					           name="<?php echo $url_option; ?>"
					           value="<?php echo get_option( $url_option ); ?>"/>
					</td>
				</tr>
				</tbody>
			</table>
		<?php
		}
	}
	// Uncomment the next line if these settings are necessary
	// new {class}_Settings();
}

add_action( 'plugins_loaded', 'cd_{plugin_u}' );

/**
 * Notices for if CD is not active (no need to change)
 */
function {dotorgun}_notice() { ?>
	<div class="error">
		<p>You have activated {plugin} which requires <a href="http://w.org/plugins/client-dash">Client Dash</a>
			version 1.5.5 or greater.
			Please install and activate <b>Client Dash</b> to continue using.</p>
	</div>
<?php
}
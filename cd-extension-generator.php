<?php
/*
Plugin Name: Client Dash Extension Generator
Description: This plugin will generate a custom Client Dash Extension.
Version: 0.1.1
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
License: GPL2
*/

// Make sure the class doesn't exist
if ( ! class_exists( 'ClientDash_Extension_Generator' ) ) {

	/**
	 * Class ClientDash_Extension_Generator
	 *
	 * The main class for the plugin. Uses a gravity form to dynamically produce a Client Dash extension.
	 *
	 * @since CD Extension Generator 0.1.0
	 */
	class ClientDash_Extension_Generator {

		/**
		 * This will be populated with all variables to parse the zipped files.
		 *
		 * @since CD Extension Generator 0.1.0
		 */
		private $variables = array();

		/**
		 * The default values for all of the variable fields.
		 *
		 * @since CD Extension Generator 0.1.0
		 */
		private static $variable_defaults = array(
			'plugin_name'        => '',
			'plugin_description' => '',
			'plugin_tags'        => '',
			'author_name'        => '',
			'author_uri'         => '',
			'author_wp'          => '',
			'page'               => '',
			'tab'                => '',
			'settings_tab'       => '',
			'section_name'       => '',
			'do_settings'        => '',
			'do_menus'           => '',
			'do_widgets'         => '',
		);

		/**
		 * The path to the plugin directory.
		 *
		 * @since CD Extension Generator 0.1.0
		 */
		private $_path;

		/**
		 * The version of the plugin.
		 *
		 * @since CD Extension Generator 0.1.0
		 */
		private static $version = '0.1.1';

		/**
		 * The main construct function.
		 *
		 * @since CD Extension Generator 0.1.0
		 */
		public function __construct() {

			// Set the path
			$this->_path = plugin_dir_path( __FILE__ );

			// Hook into the form submission
			$form_ID = self::get_generator_form();
			if ( ! empty( $form_ID ) ) {
				add_action( "gform_after_submission_$form_ID", array( $this, 'form_submit' ), 10, 5 );
			}

			// Generate and download the zip
			if ( isset( $_GET['download'] ) ) {
				$this->generate_zip();
			}
		}

		/**
		 * Gets the form ID of the "Extension Generator" gravity form.
		 *
		 * @since CD Extension Generator 0.1.1
		 *
		 * @return mixed The form ID, or empty if it doesn't exist.
		 */
		private static function get_generator_form() {

			global $wpdb;
			$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rg_form where title='Extension Generator'" );
			$result = reset( $result );
			return $result->id;
		}

		/**
		 * Fires when you submit the generator gravity form.
		 *
		 * @since CD Extension Generator 0.1.0
		 *
		 * @param string $entry The entry object.
		 * @param array $form The gravity form object.
		 */
		public function form_submit( $entry, $form ) {

			// Associate form values with their inputs and store them in this object's variables property
			$this->associate_form_values( $form );

			// Set defaults
			$this->variables = wp_parse_args( $this->variables, self::$variable_defaults );

			// Store the data in a transient to carray it to the page refresh
			set_transient( 'cd_extension_generator_variables', $this->variables, MINUTE_IN_SECONDS );

			// Refresh the page to download the document
			header( 'Refresh: 1; url=' . add_query_arg( 'download', 1 ) );
		}

		/**
		 * Associates form input fields with their proper values.
		 *
		 * Gravity form dynamically creates fields, so they all get arbitrary names like "input_1", "input_2", etc. This
		 * can get really confusing and cumbersome. So I'm using the "Admin Label" field in Gravity Forms and
		 * associating that with each input, then storing each input's value into an element with that name.
		 *
		 * @since CD Extension Generator 0.1.0
		 *
		 * @param array $form_object The freshly submitted Gravity Form object.
		 */
		private function associate_form_values( $form_object ) {

			// Cycle through all of the form's fields
			foreach ( $form_object['fields'] as $field_object ) {

				// If this key exists, then use the first one (checkboxes)
				if ( ! empty( $field_object['inputs'] ) ) {
					$field_object['id'] = str_replace( '.', '_', $field_object['inputs'][0]['id'] );
				}

				// Skip if it's not in POST (it should be) or if it's empty
				if ( ! isset( $_POST["input_$field_object[id]"] ) || empty( $_POST["input_$field_object[id]"] ) ) {
					continue;
				}

				// Store the POST value and the label
				$value = $_POST["input_$field_object[id]"];
				$label = $field_object['adminLabel'];

				// If the current field is set in our variables array, add it
				if ( isset( self::$variable_defaults[ $label ] ) ) {
					$this->variables[ $label ] = $value;
				}
			}

			// Remove default author uri
			if ( $this->variables['author_uri'] == 'http://' ) {
				$this->variables['author_uri'] = '';
			}

			// Setup some new vars
			$this->variables['plugin_ID']    = strtolower(
				str_replace(
					array(
						' ',
						'-'
					),
					'_',
					$this->variables['plugin_name']
				)
			);
			$this->variables['plugin_class'] = str_replace(
				array(
					' ',
					'-'
				),
				'',
				ucwords( $this->variables['plugin_name']
				)
			);
		}

		/**
		 * Generates the extension zip dynamically.
		 *
		 * @since CD Extension Generator 0.1.0
		 */
		public function generate_zip() {

			// Get our data from the just recently saved transient
			$this->variables = get_transient( 'cd_extension_generator_variables' );
			delete_transient( 'cd_extension_generator_variables' );
			if ( ! $this->variables ) {
				return;
			}

			$zip = new ZipArchive();

			if ( $msg = $zip->open( $this->variables['plugin_ID'] . '.zip', ZipArchive::CREATE && ZipArchive::OVERWRITE ) !== true ) {
				die( $msg );
			}

			$files = array(
				'client-dash-extension.php' => $this->variables['plugin_ID'] . '.php',
				'readme.txt',
				'style.css',
			);

			if ( $this->variables['do_settings'] == '1' ) {
				$files[] = 'inc/settings.php';
			}

			if ( $this->variables['do_menus'] == '1' ) {
				$files[] = 'inc/menus.php';
			}

			if ( $this->variables['do_widgets'] == '1' ) {
				$files[] = 'inc/widgets.php';
			}

			foreach ( $files as $file => $new_file ) {

				// If file does not have new name
				if ( is_integer( $file ) ) {
					$file = $new_file;
				}

				$file_contents = file_get_contents( "$this->_path/source/$file" );

				// Remove or clean up conditional blocks
				$file_contents = $this->replace_condition_blocks( $file_contents );

				// Replace variables
				$file_contents = $this->replace_variables( $file_contents );

				// Add Files in
				$zip->addFromString( "/$new_file", $file_contents );
			}

			$zip->close();

			header( 'Content-type: application/zip' );
			header( 'Content-Disposition: attachment; filename="' . $this->variables['plugin_ID'] . '.zip"' );
			readfile( $this->variables['plugin_ID'] . '.zip' );
			unlink( $this->variables['plugin_ID'] . '.zip' );
			exit();
		}

		/**
		 * Parses the file contents and replaces conditional blocks conditionally.
		 *
		 * @since CD Extension Generator 0.1.0
		 *
		 * @param string $file_contents The old file contents.
		 *
		 * @return string The new file contents.
		 */
		private function replace_condition_blocks( $file_contents ) {

			// TODO Get all extra white space to be deleted in new file when replacing conditionals

			preg_match_all( '/{if:.*?{endif}/s', $file_contents, $conditional_blocks );
			$conditional_blocks = reset( $conditional_blocks );

			foreach ( $conditional_blocks as $block ) {

				// Get the conditional parameter
				preg_match( '/{if:(.*)}/', $block, $param );
				$param = $param[1];

				if ( isset( $this->variables[ $param ] ) && ! empty( $this->variables[ $param ] ) ) {

					preg_match_all( '/{if:' . $param . '}(.*){endif}/s', $block, $content );
					$content       = ltrim( $content[1][0], '\n\r' );
					$file_contents = preg_replace( '/{if:' . $param . '}.*?{endif}[\\n]*/s', $content, $file_contents, 1 );
				} else {
					$file_contents = preg_replace( '/{if:' . $param . '}.*?{endif}[\\n]*/s', '', $file_contents, 1 );
				}
			}

			return $file_contents;
		}

		/**
		 * Parses the file contents and replaces variables.
		 *
		 * @since CD Extension Generator 0.1.0
		 *
		 * @param string $file_contents The old file contents.
		 *
		 * @return string The new file contents.
		 */
		private function replace_variables( $file_contents ) {

			preg_match_all( '/{(.*?)}/', $file_contents, $content_variables );

			foreach ( $content_variables[1] as $variable ) {

				if ( ! array_key_exists( $variable, $this->variables ) && ! array_key_exists( $variable, self::$variable_defaults ) ) {
					continue;
				}

				if ( isset( $this->variables[ $variable ] ) && ! empty( $this->variables[ $variable ] ) ) {
					$file_contents = preg_replace( '/{' . $variable . '}/', $this->variables[ $variable ], $file_contents );
				} else {
					$file_contents = preg_replace( '/{' . $variable . '}/', self::$variable_defaults[ $variable ], $file_contents );
				}
			}

			return $file_contents;
		}
	}

	// Instantiate the class
	new ClientDash_Extension_Generator();
}


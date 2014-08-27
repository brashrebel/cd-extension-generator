<?php
/*
Plugin Name: Client Dash Extension Generator
Description: This plugin will generate a custom Client Dash Extension.
Version: 0.1
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
License: GPL2
*/

/**
 * Zip File Generator Class for WordPress
 */
if ( !class_exists( 'WP_Zip_Generator' ) ) {

	class WP_Zip_Generator {

		var $options = array();
		var $slug = '';

		function __construct( $args = null ) {

			$defaults = array(
				'name'                 => '',
				'source_directory'     => '',
				'process_extensions'   => array( 'php', 'css', 'js', 'txt', 'md' ),
				'zip_root_directory'   => '',
				'zip_temp_directory'   => plugin_dir_path( __FILE__ ),
				'download_filename'    => '',
				'exclude_directories'  => array( '.git', '.svn', '.', '..' ),
				'exclude_files'        => array( '.git', '.svn', '.DS_Store', '.gitignore', '.', '..' ),
				'filename_filter'      => null,
				'file_contents_filter' => null,
				'post_process_action'  => null,
				'variables'            => array()
			);

			$this->options = wp_parse_args( $args, $defaults );

			//check required args
			if ( empty( $this->options['name'] ) ) {
				throw new Exception( "Zip_Generator class requires a name in order to function!" );
			}

			$this->slug = sanitize_title_with_dashes( $this->options['name'] );

			$this->options['download_filename'] = empty( $this->options['download_filename'] ) ? "{$this->slug}.zip" : $this->options['download_filename'];

			$this->options['zip_temp_filename'] = trailingslashit( $this->options['zip_temp_directory'] ) . sprintf( '%s-%s.zip', $this->slug, md5( print_r( $this->options['variables'], true ) ) );

			if ( ! empty( $this->options['filename_filter'] ) ) {
				add_filter( 'zip_generator_process_filename-' . $this->slug, $this->options['filename_filter'], 10, 2 );
			}

			if ( ! empty( $this->options['file_contents_filter'] ) ) {
				add_filter( 'zip_generator_process_file_contents-' . $this->slug, $this->options['file_contents_filter'], 10, 2 );
			}

			if ( ! empty( $this->options['post_process_action'] ) ) {
				add_action( 'zip_generator_post_process-' . $this->slug, $this->options['post_process_action'], 10, 2 );
			}
		}

		/**
		 * Creates the new zip file based on the source_directory
		 */
		function generate() {
			$zip = new ZipArchive;

			$res = $zip->open( $this->options['zip_temp_filename'], ZipArchive::CREATE && ZipArchive::OVERWRITE );

			$iterator = new RecursiveDirectoryIterator( $this->options['source_directory'] );
			foreach ( new RecursiveIteratorIterator( $iterator ) as $filename ) {

				if ( in_array( basename( $filename ), $this->options['exclude_files'] ) ) {
					continue;
				}

				foreach ( $this->options['exclude_directories'] as $directory ) {
					if ( strstr( $filename, "/{$directory}/" ) ) {
						continue 2;
					}
				} // continue the parent foreach loop

				$zip_filename = str_replace( trailingslashit( $this->options['source_directory'] ), '', basename( $filename ) );

				$zip_filename = apply_filters( 'zip_generator_process_filename-' . $this->slug, $zip_filename );

				$contents = $this->process_file_contents( file_get_contents( $filename ), basename( $filename ) );

				$zip->addFromString( trailingslashit( $this->options['zip_root_directory'] ) . $zip_filename, $contents );
			}

			do_action( 'zip_generator_post_process-' . $this->slug, $zip, $this->options );

			$zip->close();
		}

		/**
		 * Process the contents of an individual file
		 *
		 * @param $contents
		 * @param $filename
		 *
		 * @return string
		 */
		function process_file_contents( $contents, $filename ) {
			// Replace only files are care about
			$valid_extensions_regex = implode( '|', $this->options['process_extensions'] );
			if ( ! preg_match( "/\.({$valid_extensions_regex})$/", $filename ) ) {
				return $contents;
			}

			foreach ( $this->options['variables'] as $key => $value ) {
				$contents = preg_replace( '/(' . $key . ')/', $value, $contents );
			}

			$contents = apply_filters( 'zip_generator_process_file_contents-' . $this->slug, $contents, $filename );

			return $contents;
		}

		/**
		 * Send the download headers to the browser
		 *
		 * @param bool $delete
		 */
		function send_download_headers( $delete = true ) {
			header( 'Content-type: application/zip' );
			header( sprintf( 'Content-Disposition: attachment; filename="%s"', $this->options['download_filename'] ) );
			readfile( $this->options['zip_temp_filename'] );
			if ( $delete ) {
				unlink( $this->options['zip_temp_filename'] );
			}
		}
	}
}

	// Kyle additions
	$variables = array(
		'{name}'               => 'Double Rainbow',
		'{author}'             => 'Brad Vincent'
	);

//create the generator
	$zip_generator = new WP_Zip_Generator(array(
		'name'                 => 'admin-color-scheme-generator',
		'process_extensions'   => array('php', 'css', 'js', 'txt', 'md'),
		'source_directory'     => dirname( __FILE__ ) . '/source/',
		//'zip_root_directory'   => "beer",
		'download_filename'    => "beer.zip",
		'variables'            => $variables
	));

if ( $_GET['beer'] == 'true' ) {
//generate the zip file
	$zip_generator->generate();

//download it to the client
	$zip_generator->send_download_headers();

	die();
}
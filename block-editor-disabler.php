<?php
/*
 * Plugin Name: Block Editor Disabler
 * Description: This plugin for disable gutenberg editor for specific role.
 * Version: 1.0.2
 * Author: khorshedalamwp
 * Author URI: https://profiles.wordpress.org/khorshedalamwp
 * Text Domain: block-editor-disabler
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


/**
 * Can not direct access this page.
 */
defined( 'ABSPATH' ) || exit;

defined( 'BEDP_PLUGIN_URI' ) || define( 'BEDP_PLUGIN_URI', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
defined( "BEDP_PLUGIN_DIR" ) || define( "BEDP_PLUGIN_DIR", plugin_dir_path( __FILE__ ) );
defined( "BEDP_PLUGIN_FILE" ) || define( "BEDP_PLUGIN_FILE", plugin_basename( __FILE__ ) );
defined( "BEDP_PLUGIN_VERSION" ) || define( "BEDP_PLUGIN_VERSION", "1.0.0" );


if ( ! class_exists( 'BEDP_Main' ) ) {
	class BEDP_Main {

		protected static $_instance = null;

		public function __construct() {
			$this->include_files();
		}


		/**
		 * Include Files
		 * @return void
		 */
		public function include_files() {
			require BEDP_PLUGIN_DIR . 'includes/class-hooks.php';
			require BEDP_PLUGIN_DIR . 'includes/class-settings.php';
		}


		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

BEDP_Main::instance();
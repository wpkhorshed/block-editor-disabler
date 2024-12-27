<?php
/**
 * DGEP Class Hooks
 */


/**
 * Can not direct access this page.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BEDP_Class_Hooks' ) ) {
	class BEDP_Class_Hooks {

		protected static $_instance = null;

		public function __construct() {
			add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg_for_all' ), 10, 2 );
			add_filter( 'use_block_editor_for_post', array( $this, 'disable_gutenberg_for_specific_pages' ), 10, 2 );
			add_filter( 'use_block_editor_for_post', array( $this, 'disable_gutenberg_for_all_pages' ), 10, 2 );
			add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg_for_posts' ), 10, 2 );
		}


		/**
		 * @param $use_block_editor
		 * @param $post_type
		 *
		 * @return false|mixed
		 */
		public function disable_gutenberg_for_posts( $use_block_editor, $post_type ) {
			$all_posts = get_option( 'bedp_disable_all_posts' );

			if ( $all_posts === 'yes' ) {
				if ( $post_type === 'post' ) {
					return false;
				}
			}

			return $use_block_editor;
		}


		/**
		 * @param $use_block_editor
		 * @param $post_type
		 *
		 * @return false|mixed
		 */
		public function disable_gutenberg_for_all_pages( $use_block_editor, $post_type ) {

			$all_pages = get_option( 'bedp_disable_all_pages' );

			if ( $all_pages === 'yes' ) {
				if ( $post_type->post_type === 'page' ) {
					return false;
				}
			}

			return $use_block_editor;
		}


		/**
		 * @param $use_block_editor
		 * @param $post
		 *
		 * @return false|mixed
		 */
		public function disable_gutenberg_for_specific_pages( $use_block_editor, $post ) {
			$page_slugs = get_option( 'bedp_disable_specific_pages', [] );
			if ( ! empty( $page_slugs ) ) {
				if ( is_object( $post ) && isset( $post->post_name ) ) {
					if ( in_array( $post->post_name, $page_slugs ) ) {
						return false;
					}
				}
			}

			return $use_block_editor;
		}


		/**
		 * @param $use_block_editor
		 * @param $post
		 *
		 * @return false|mixed
		 */
		public function disable_gutenberg_for_all( $use_block_editor, $post ) {
			$bedp_disable_all = get_option( 'bedp_disable_all' );
			if ( $bedp_disable_all === 'yes' ) {
				return false;
			}

			return $use_block_editor;
		}


		/**
		 * @return self|null
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

BEDP_Class_Hooks::instance();

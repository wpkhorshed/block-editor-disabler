<?php
/**
 * DGEP Class Settings
 */


/**
 * Can not direct access this page.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BEDP_Class_Settings' ) ) {
	class BEDP_Class_Settings {

		protected static $_instance = null;

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_admin_setting_page' ) );
			add_action( 'admin_init', array( $this, 'disable_gutenberg_settings_init' ) );
			add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 10, 4 );
		}


		/**
		 * Add Plugin Action Link
		 *
		 * @param $links
		 * @param $file
		 * @param $plugin_data
		 * @param $context
		 *
		 * @return array|mixed
		 */
		public function add_plugin_action_links( $links, $file, $plugin_data, $context ) {
			if ( 'dropins' === $context ) {
				return $links;
			}

			$what      = ( 'mustuse' === $context ) ? 'muplugin' : 'plugin';
			$new_links = array();

			foreach ( $links as $link_id => $link ) {

				if ( 'deactivate' == $link_id && BEDP_PLUGIN_FILE == $file ) {
					$new_links['block-editor-disabler-link'] = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=dgep-settings-page' ), esc_html__( 'Settings', 'block-editor-disabler' ) );
				}

				$new_links[ $link_id ] = $link;
			}

			return $new_links;
		}


		/**
		 * Create Settings Option Fields
		 * @return void
		 */
		public function disable_gutenberg_settings_init() {

			$pages     = [];
			$all_pages = get_posts( array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => - 1
			) );

			if ( ! empty( $all_pages ) ) {
				foreach ( $all_pages as $page ) {
					$replace        = str_replace( ' ', '-', $page->post_title );
					$slug           = strtolower( $replace );
					$pages[ $slug ] = $page->post_title;
				}
			}

			add_settings_section( 'disable_gutenberg_section', 'Disable Gutenberg Editor', null, 'bedp_settings' );

			if ( get_option( 'bedp_disable_all' ) === 'yes' ) {
				$fields = array(
					'bedp_disable_all' => array(
						'title'    => esc_html__( 'Disable All', 'block-editor-disabler' ),
						'type'     => 'checkbox',
						'subtitle' => esc_html__( 'Deactivate the Block Editor on Your Website', 'block-editor-disabler' ),
					),
				);
			} elseif ( get_option( 'bedp_disable_all_pages' ) === 'yes' ) {
				$fields = array(
					'bedp_disable_all'       => array(
						'title'    => esc_html__( 'Disable All', 'block-editor-disabler' ),
						'type'     => 'checkbox',
						'subtitle' => esc_html__( 'Deactivate the Block Editor on Your Website', 'block-editor-disabler' ),
					),
					'bedp_disable_all_posts' => array(
						'title'    => esc_html__( 'All Posts', 'block-editor-disabler' ),
						'type'     => 'checkbox',
						'subtitle' => esc_html__( 'Disable all posts only', 'block-editor-disabler' ),
					),

					'bedp_disable_all_pages' => array(
						'title'    => esc_html__( 'All Pages', 'block-editor-disabler' ),
						'type'     => 'checkbox',
						'subtitle' => esc_html__( 'Disable all pages only', 'block-editor-disabler' ),
					),
				);
			} else {
				$fields = array(
					'bedp_disable_all'       => array(
						'title'    => esc_html__( 'Disable All', 'block-editor-disabler' ),
						'type'     => 'checkbox',
						'subtitle' => esc_html__( 'Deactivate the Block Editor on Your Website', 'block-editor-disabler' ),
					),
					'bedp_disable_all_posts' => array(
						'title'    => esc_html__( 'All Posts', 'block-editor-disabler' ),
						'type'     => 'checkbox',
						'subtitle' => esc_html__( 'Disable all posts only', 'block-editor-disabler' ),
					),

					'bedp_disable_all_pages' => array(
						'title'    => esc_html__( 'All Pages', 'block-editor-disabler' ),
						'type'     => 'checkbox',
						'subtitle' => esc_html__( 'Disable all pages only', 'block-editor-disabler' ),
					),

					'bedp_disable_specific_pages' => array(
						'title'    => esc_html__( 'Specific pages', 'block-editor-disabler' ),
						'type'     => 'checkboxes',
						'subtitle' => esc_html__( 'Disable for specific pages(Post Type Page).', 'block-editor-disabler' ),
						'pages'    => $pages
					),
				);
			}

			foreach ( $fields as $field_id => $field_data ) {

				add_settings_field(
					$field_id,
					$field_data['title'],
					array( $this, 'render_setting_fields' ),
					'bedp_settings',
					'disable_gutenberg_section',
					array(
						'field_id'    => $field_id,
						'field_type'  => $field_data['type'],
						'placeholder' => $field_data['placeholder'] ?? '',
						'subtitle'    => $field_data['subtitle'] ?? '',
						'pages'       => $field_data['pages'] ?? '',
					)
				);
				register_setting( 'bedp_settings', $field_id, array( $this, 'sanitize_checkbox' ) );
			}

		}


		/**
		 * Sanitize the checkbox values
		 *
		 * @param $input
		 *
		 * @return array|string
		 */
		public function sanitize_checkbox( $input ) {
			if ( is_array( $input ) ) {
				return array_map( 'sanitize_text_field', $input );
			}

			return sanitize_text_field( $input );
		}


		/**
		 * Render setting fields in admin menu page.
		 *
		 * @param $args
		 *
		 * @return void
		 */
		public function render_setting_fields( $args ) {

			$field_id    = $args['field_id'];
			$field_type  = $args['field_type'];
			$field_value = get_option( $field_id, '' ); // Get the saved option, default to empty string for single checkbox
			$placeholder = $args['placeholder'];
			$pages       = $args['pages'];
			$subtitle    = isset( $args['subtitle'] ) ? sanitize_text_field( $args['subtitle'] ) : '';

			if ( $field_type == 'checkbox' ) {
				// Ensure the checkbox value is handled as a string
				$field_value = is_array( $field_value ) ? '' : $field_value;
				echo '<input type="checkbox" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_id ) . '" value="yes" ' . checked( 'yes', $field_value, false ) . ' /><p>' . esc_html( $subtitle ) . '</p>';
			}elseif ( $field_type == 'checkboxes' ) {
				foreach ( $pages as $slug => $name ) {
					$checked = in_array( $slug, (array) $field_value ) ? 'checked="checked"' : ''; ?>
                    <input type="checkbox" id="<?php echo esc_attr( $slug ); ?>" name="bedp_disable_specific_pages[]" value="<?php echo esc_attr( $slug ); ?>" <?php echo esc_html( $checked ); ?>>
                    <label for="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></label><br>
				<?php }
			} else {
				echo '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $field_id ) . '" placeholder="' . esc_attr( $placeholder ) . '" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_value ) . '" /><p>' . esc_html( $subtitle ) . '</p>';
			}
		}


		/**
		 * Register Admin Settings Page
		 * @return void
		 */
		public function register_admin_setting_page() {
			add_menu_page( 'BEDP Settings', 'Block Editor Settings', 'manage_options', 'dgep-settings-page', array( $this, 'render_settings_page' ), 'dashicons-plus-alt2', '21' );
		}


		/**
		 * Settings Page Callback.
		 * @return void
		 */
		public function render_settings_page() { ?>
            <div class='wrap-bedp-settings-page'>
                <form id="bedp-settings-form" method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" enctype="multipart/form-data">
					<?php settings_fields( 'bedp_settings' ); ?>
					<?php do_settings_sections( 'bedp_settings' ); ?>
					<?php submit_button(); ?>
                </form>
            </div>
			<?php
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

BEDP_Class_Settings::instance();
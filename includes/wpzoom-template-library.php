<?php
namespace Elementor\TemplateLibrary;

//Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) 
	exit;

/**
 * WPZOOM Templates Library
 *
 * Uses built-in Elementor functions to create a new source for template library.
 *
 * @since 1.0.0
 */
if ( did_action( 'elementor/loaded' ) ) {
	class WPZOOM_Library_Source extends Source_Base {

		public function __construct() {
			
			parent::__construct();
			add_action( 'wp_ajax_get_content_from_elementor_export_file', array( $this, 'get_finalized_data' ) );
		
		}

		public function get_id() {}
		public function get_title() {}
		public function register_data(){ }
		public function get_items( $args = [] ){}
		public function get_item( $template_id ){}
		public function get_data( array $args ){}
		public function delete_template( $template_id ){}
		public function save_item( $template_data ){}
		public function update_item( $new_data ){}
		public function export_template( $template_id ){}

		public function get_finalized_data()
		{

		// Check if user has permission to import templates
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Insufficient permissions to import templates.', 'wpzoom-elementor-addons' )
			) );
		}

		// Additional security check - ensure we're in Elementor context
		if ( ! did_action( 'elementor/loaded' ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Elementor is required to import templates.', 'wpzoom-elementor-addons' )
			) );
		}

		// Validate and sanitize filename
		if ( ! isset( $_POST['filename'] ) || empty( $_POST['filename'] ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Template filename is required.', 'wpzoom-elementor-addons' )
			) );
		}

		$filename = sanitize_text_field($_POST['filename']);

		// Get import type (template or section) - defaults to template for backward compatibility
		$import_type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'template';

		// Additional security: ensure filename has .json extension and no path traversal
		if (!preg_match('/^[a-zA-Z0-9\-_]+\.json$/', $filename)) {
			wp_send_json_error(array(
				'message' => esc_html__('Invalid template filename format.', 'wpzoom-elementor-addons')
			));
		}

		if ('section' !== $import_type && 'wireframe' !== $import_type && $this->is_pro_template($filename) && !$this->can_import_pro_template()) {
			$has_pro_plugin = class_exists( 'WPZOOM_Elementor_Addons_Pro' );
			
			if ( ! $has_pro_plugin && ! class_exists( 'WPZOOM' ) ) {
				$error_message = esc_html__( 'This template requires WPZOOM Elementor Addons Pro plugin. Please install and activate the Pro plugin to import premium templates.', 'wpzoom-elementor-addons' );
			} else {
				$error_message = '';
			}
			
			if ( empty( $error_message ) ) {
				$error_message = esc_html__( 'This template requires WPZOOM Elementor Addons Pro license. Please activate your license key to import PRO templates.', 'wpzoom-elementor-addons' );
			}
			
			wp_send_json_error( array(
				'message' => $error_message,
				'is_license_error' => true
			) );
		}

			//$url = sprintf( 'https://api.wpzoom.com/elementor/templates/%s', $filename );
			//$response = wp_remote_get( $url, array( 'timeout' => 60 ) );
			//if( !is_wp_error( $response ) ) {
			//	$data = json_decode( wp_remote_retrieve_body( $response ), true );
			//}
			//else {
			// Check the appropriate directory based on import type
			$data = null;

			if ($import_type === 'section') {
				// For sections, only check sections directory
				$local_file = sprintf(WPZOOM_EL_ADDONS_PATH . '/includes/data/sections/json/%s', $filename);
				if (self::get_filesystem()->exists($local_file)) {
					$data = self::get_filesystem()->get_contents($local_file);
					$data = json_decode($data, true);
				}
			} elseif ($import_type === 'wireframe') {
				// For wireframes, only check wireframes directory
				$local_file = sprintf(WPZOOM_EL_ADDONS_PATH . '/includes/data/wireframes/json/%s', $filename);
				if (self::get_filesystem()->exists($local_file)) {
					$data = self::get_filesystem()->get_contents($local_file);
					$data = json_decode($data, true);
				}
			} else {
				// For templates (default), check templates directory first
				$local_file = sprintf(WPZOOM_EL_ADDONS_PATH . '/includes/data/templates/json/%s', $filename);
			if( self::get_filesystem()->exists( $local_file ) ) {
				$data = self::get_filesystem()->get_contents( $local_file );
				$data = json_decode( $data, true );
			}
				// If not found in templates, try sections as fallback for backward compatibility
			if (empty($data)) {
				$local_section_file = sprintf(WPZOOM_EL_ADDONS_PATH . '/includes/data/sections/json/%s', $filename);
				if (self::get_filesystem()->exists($local_section_file)) {
					$data = self::get_filesystem()->get_contents($local_section_file);
					$data = json_decode($data, true);
				}
			}
			}
			//}
		
		if ( empty( $data ) || ! isset( $data['content'] ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Template data could not be loaded. Please try again or contact support.', 'wpzoom-elementor-addons' )
			) );
		}
		
		$content = $data['content'];
		$content = $this->process_export_import_content( $content, 'on_import' );
		$content = $this->replace_elements_ids( $content );
		
		echo json_encode( $content );
		wp_die();

	}

	/**
	 * Check if template is a PRO template
	 *
	 * @param string $filename Template filename
	 * @return bool
	 */
	private function is_pro_template( $filename ) {
		// Get template list to check if this template belongs to a PRO theme
			$local_file = WPZOOM_EL_ADDONS_PATH . '/includes/data/templates/json/info.json';
		if ( ! self::get_filesystem()->exists( $local_file ) ) {
			return false;
		}
		
		$data = self::get_filesystem()->get_contents( $local_file );
		$template_list = json_decode( $data, true );
		
		if ( empty( $template_list ) ) {
			return false;
		}
		
		// Define which themes are free for everyone
		$free_themes = array( 'Foodica', 'Inspiro Lite' );
		
		// Extract template ID from filename (remove .json extension)
		$template_id = str_replace( '.json', '', $filename );
		$template_id = str_replace( '-', ' ', $template_id );
		
		// Find the template in the list
		foreach ( $template_list as $template ) {
			if ( ! isset( $template['id'] ) || ! isset( $template['theme'] ) ) {
				continue;
			}
			
			$slug = strtolower( str_replace( ' ', '-', $template['id'] ) );
			if ( $slug === str_replace( '.json', '', $filename ) ) {
				// Check if this template's theme is not in the free themes list
				return ! in_array( $template['theme'], $free_themes );
			}
		}
		
		// If template not found in list, assume it's PRO for safety
		return true;
	}

	/**
	 * Check if user can import PRO templates
	 *
	 * @return bool
	 */
	private function can_import_pro_template() {
		// For template imports, require Pro plugin to be active
		// OR if premium WPZOOM theme is active
		return class_exists( 'WPZOOM_Elementor_Addons_Pro' ) || class_exists( 'WPZOOM' );
	}

		/**
		 * Get an instance of WP_Filesystem_Direct.
		 *
		 * @since 1.0.0
		 * @return object A WP_Filesystem_Direct instance.
		 */
		public static function get_filesystem() {
		
			global $wp_filesystem;
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			return $wp_filesystem;
		}
	}
	new WPZOOM_Library_Source();
}
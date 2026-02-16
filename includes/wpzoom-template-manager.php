<?php

//Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) 
	exit;

if ( !class_exists( 'WPZOOM_Elementor_Library_Manager' ) ) {

	class WPZOOM_Elementor_Library_Manager {


		/**
		 * Creates a single Instance of self
		 *
		 * @var Static data - Define menu main menu name
		 * @since 1.0.0
		 */
		private static $_instance = null;


		/**
		 * Settings plugin details
		 *
		 * @var Static data - Define all important magic strings
		 * @since 1.0.0
		 */
		static $library_source = null;

		/**
		 * Define All Actions
		 *
		 * @var Static data - Define all actions
		 * @since 1.0.0
		 */
		static $element_pro_actions = null;


		/**
		 * Creates and returns the main object for this plugin
		 *
		 *
		 * @since  1.0.0
		 * @return WPZOOM_Elementor_Library_Manager
		 */
		static public function init() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;

		}

		/**
		 * Main Constructor that sets up all static data associated with this plugin.
		 *
		 *
		 * @since  1.0.0
		 *
		 */
		private function __construct() {

			//Setup static library_source
			self::$library_source = 'https://api.wpzoom.com/elementor/templates/';

			add_action('wp_ajax_get_wpzoom_templates_library_view', array($this, 'get_wpzoom_templates_library_view'));
			// Alias: pages view (same handler)
			add_action('wp_ajax_get_wpzoom_pages_library_view', array($this, 'get_wpzoom_templates_library_view'));
			add_action( 'wp_ajax_get_wpzoom_preview', array( $this, 'ajax_get_wpzoom_preview' ) );
			add_action( 'wp_ajax_get_filter_options', array( $this, 'get_template_filter_options_values' ) );

			// Sections (patterns) AJAX endpoints
			add_action('wp_ajax_get_wpzoom_sections_library_view', array($this, 'get_wpzoom_sections_library_view'));
			add_action('wp_ajax_get_wpzoom_section_preview', array($this, 'ajax_get_wpzoom_section_preview'));
			add_action('wp_ajax_get_sections_filter_options', array($this, 'get_sections_filter_options_values'));

			/* Set initial version to the and call update on first use */
			if( get_option( 'wpz_current_version' ) == false ) {
				update_option( 'wpz_current_version', '0.0.0' );
			}

		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpzoom-elementor-addons' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpzoom-elementor-addons' ), '1.0.0' );
		}

		/**
		 * Get templates from the json library
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function get_wpzoom_templates_library_view() {
			
			$template_list = array();
			$thumb_url = '';
			echo '<script> var WPZ_Index = []; </script>';
			
			// Check Pro plugin and premium theme status
			$has_premium_access = class_exists( 'WPZOOM_Elementor_Addons_Pro' ) || class_exists( 'WPZOOM' );
			$has_pro_plugin = class_exists( 'WPZOOM_Elementor_Addons_Pro' );

			// Define which themes are free for everyone
			$free_themes = array( 'Foodica', 'Inspiro Lite' );

			//Get libray json from source
			$response = wp_remote_get(self::$library_source, array('timeout' => 60));

			//if( !is_wp_error( $response ) ) {
			//	$info_data = wp_remote_retrieve_body( $response );
			//	$template_list = json_decode( $info_data, true );
			//}
			//else {
			$local_file = WPZOOM_EL_ADDONS_PATH . '/includes/data/templates/json/info.json';
				if( self::init()->get_filesystem()->exists( $local_file ) ) {
					$data = self::init()->get_filesystem()->get_contents( $local_file );
					$template_list = json_decode( $data, true );
				}
				$thumb_url = 'https://wpzoom.s3.us-east-1.amazonaws.com/elementor/templates/assets/thumbs/';
			//}

			echo '<div class="wpzoom-main-tiled-view">';
			if (count($template_list) != 0) {

				for ($i = 0; $i < count($template_list); $i++) {
					$slug = strtolower(str_replace(' ', '-', $template_list[$i]['id']));
					$theme = $template_list[$i]['theme'];
					$is_theme_free = in_array($theme, $free_themes);
					$is_restricted = !$has_premium_access && !$is_theme_free;

					// Get appropriate button data for Pro templates
					$button_data = array(
						'text' => esc_html__('Get Pro Plugin', 'wpzoom-elementor-addons'),
						'url' => 'https://www.wpzoom.com/plugins/wpzoom-elementor-addons/'
					);

					if( isset( $template_list[$i]['separator'] ) ) {
						$separator_title = $template_list[$i]['separator'];
						if ( !$is_theme_free ) {
							$separator_title .= ' <span class="wpzoom-pro-badge" style="color: #fff; font-weight: 600; font-size: 12px;">PRO</span>';
						}
						echo '<h2 class="wpzoom-templates-library-template-category" data-theme="'. esc_attr( strtolower( str_replace( ' ', '-', $template_list[$i]['theme'] ) ) ) .'">' . wp_kses_post( $separator_title ) . '</h2>';
					}
					?>
					<div 
						class="wpzoom-templates-library-template wpzoom-item <?php echo $is_restricted ? 'wpzoom-template-pro-only' : ''; ?>"
						data-theme="<?php echo esc_attr( strtolower( str_replace( ' ', '-', $template_list[$i]['theme'] ) ) ) ?>" 
						data-category="<?php echo esc_attr( strtolower( str_replace( ' ', '-', $template_list[$i]['category'] ) ) ) ?>"
						>
						<div class="wpzoom-template-title">
							<?php echo esc_html( $template_list[$i]['name'] ); ?>

						</div>
					<div 
						class="wpzoom-template-thumb wpzoom-index-<?php echo esc_attr($i); ?> <?php echo $is_restricted ? 'wpzoom-template-thumb-locked' : ''; ?>"
						data-index="<?php echo esc_attr($i); ?>"
						data-template="<?php echo esc_attr(wp_json_encode($template_list[$i])); ?>">
						<img src="<?php echo esc_url($thumb_url . $template_list[$i]['thumbnail']); ?>-thumb.png" 
							alt="<?php echo esc_attr($template_list[$i]['name']); ?>" 
							class="wpzoom-thumb-image">
							<?php if ( $is_restricted ) : ?>
								<div class="wpzoom-template-overlay">
									<div class="wpzoom-template-lock-icon">🔒</div>
									<div class="wpzoom-template-pro-text"><?php esc_html_e( 'PRO Only', 'wpzoom-elementor-addons' ); ?></div>
								</div>
							<?php endif; ?>
					</div>
						<div class="wpzoom-action-bar">
							<div class="wpzoom-grow"> </div>
							<?php if ( $is_restricted ) : ?>
								<a href="<?php echo esc_url( $button_data['url'] ); ?>" target="_blank" class="wpzoom-btn-template-upgrade wpzoom-btn-pro-required" title="<?php echo esc_attr( $button_data['text'] ); ?>">
									<?php echo esc_html( $button_data['text'] ); ?>
								</a>
                            <?php else: ?>
								<div class="wpzoom-btn-template-insert" data-version="WPZ__version-<?php echo esc_attr($i); ?>"
									data-template-name="<?php echo esc_attr($slug); ?>">
									<?php esc_html_e('Insert Page', 'wpzoom-elementor-addons'); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php
				}  /* Thumbnail Loop */
			} else {
				echo '<div class="wpzoom-no-results"> <i class="fa fa-frown-o"></i> ' . esc_html__('No Pages Found!', 'wpzoom-elementor-addons') . ' </div>';
			}
			
			echo '</div>';	
			
			wp_die();
		
		}

		/**
		 * Get templates themes
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function get_template_filter_options_values( $data ) {

			$themesList = $templates = array();

			//Get libray json from source
			$response = wp_remote_get( self::$library_source, array( 'timeout' => 60 ) );

			if( !is_wp_error( $response ) ) {
				$data = wp_remote_retrieve_body( $response );
				$templates = json_decode( $data, true );
			}
			else {
				$localJson = WPZOOM_EL_ADDONS_PATH . '/includes/data/templates/json/info.json';
				if( self::init()->get_filesystem()->exists( $localJson ) ) {
					$data = self::init()->get_filesystem()->get_contents( $localJson );
					$templates = json_decode( $data, true );
				}
			}
			if( count( $templates ) != 0 ) {
				foreach( $templates as $key => $template ) {
					$themesList[] = strtolower( str_replace(' ', '-', $template['theme'] ) );
				}
			}
			$themesList = array_unique( $themesList );

			echo json_encode( $themesList );
			
			wp_die();

		}

		/**
		 * Get sections categories for filter dropdown
		 *
		 * @return void
		 */
		public function get_sections_filter_options_values()
		{

			$categoriesList = $sections = array();

			$localJson = WPZOOM_EL_ADDONS_PATH . '/includes/data/sections/json/info.json';
			if (self::init()->get_filesystem()->exists($localJson)) {
				$data = self::init()->get_filesystem()->get_contents($localJson);
				$sections = json_decode($data, true);
			}

			if (count($sections) != 0) {
				foreach ($sections as $key => $section) {
					if (isset($section['category'])) {
						$categoriesList[] = strtolower(str_replace(' ', '-', $section['category']));
					}
				}
			}

			$categoriesList = array_unique($categoriesList);

			// Custom order for section categories in the dropdown.
			// Default slugs currently in use: general, hero, features, contact.
			$desired_order = array('general', 'hero', 'features', 'contact');

			usort(
				$categoriesList,
				function ($a, $b) use ($desired_order) {
					$pos_a = array_search($a, $desired_order, true);
					$pos_b = array_search($b, $desired_order, true);

					if (false === $pos_a) {
						$pos_a = PHP_INT_MAX;
					}
					if (false === $pos_b) {
						$pos_b = PHP_INT_MAX;
					}

					if ($pos_a === $pos_b) {
						return strcmp($a, $b);
					}

					return $pos_a <=> $pos_b;
				}
			);

			echo json_encode($categoriesList);

			wp_die();

		}

		/**
		 * Get  ajax preview template
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function ajax_get_wpzoom_preview()
		{
			$this->get_preview_template($_POST['data'], 'templates');
			wp_die();
		}

		/**
		 * Print the preview window and make callable through ajax
		 *
		 * @param array $data Template/Section data
		 * @param string $type Type of content: 'templates' or 'sections'
		 * @return void
		 */
		private function get_preview_template($data, $type = 'templates')
		{

			if (wp_http_validate_url($data['thumbnail'])) {
				$thumb_url = $data['thumbnail'];
			} else {
				// Use local plugin assets for sections, AWS S3 for templates (pages)
				if ($type === 'sections') {
                    $thumb_url = 'https://wpzoom.s3.us-east-1.amazonaws.com/elementor/templates/assets/thumbs/inspiro/sections/' . $data['thumbnail'];
				} else {
					$thumb_url = 'https://wpzoom.s3.us-east-1.amazonaws.com/elementor/' . $type . '/assets/thumbs/' . $data['thumbnail'];
				}
			}

			// Check if this template is restricted.
			$has_premium_access = class_exists( 'WPZOOM_Elementor_Addons_Pro' ) || class_exists( 'WPZOOM' );
			$is_restricted = false;

			if ('sections' !== $type) {
				$free_themes = array('Foodica', 'Inspiro Lite');
				$theme = isset($data['theme']) ? $data['theme'] : '';
				$is_theme_free = in_array($theme, $free_themes, true);
				$is_restricted = !$has_premium_access && !$is_theme_free;
			}
			
			// Get appropriate button data and messages for Pro plugin
			$button_data = $this->get_pro_button_data();
			$preview_message = $this->get_pro_preview_message();
			?>
			<div id="wpzoom-elementor-template-library-preview">
				<?php if ( $is_restricted ) : ?>
					<div class="wpzoom-preview-pro-notice" style="background: #222; color: white; padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center;">
						<div style="font-size: 18px; font-weight: 500; margin-bottom: 5px;"><?php esc_html_e( 'Premium Template Preview', 'wpzoom-elementor-addons' ); ?></div>
						<p style="margin: 0; opacity: 0.9; font-size: 14px;">
							<?php echo esc_html( $preview_message ); ?>
						</p>
						<a href="<?php echo esc_url( $button_data['url'] ); ?>" target="_blank" style="display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; text-decoration: none; border-radius: 20px; font-size: 13px; margin-top: 10px; transition: background 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'" title="<?php echo esc_attr( $button_data['text'] ); ?>">
							<?php echo esc_html( $button_data['text'] ); ?>
						</a>
					</div>
				<?php endif; ?>
				    <img src="<?php echo esc_url($thumb_url . '-full.png'); ?>" alt="<?php echo esc_attr($data['name']); ?>" />
			</div>
			<?php
		}

		/**
		 * Sections (patterns): List view
		 *
		 * Loads section entries from local catalog and renders tiles similarly to templates
		 */
		public function get_wpzoom_sections_library_view()
		{
			$section_list = array();
			$thumb_url = '';
			echo '<script> var WPZ_Sections_Index = []; </script>';

			// Check Pro plugin and premium theme status
			$has_premium_access = class_exists('WPZOOM_Elementor_Addons_Pro') || class_exists('WPZOOM');

			// Define which themes are free for everyone
			$free_themes = array('Foodica', 'Inspiro Lite');

			$local_file = WPZOOM_EL_ADDONS_PATH . '/includes/data/sections/json/info.json';
			if (self::init()->get_filesystem()->exists($local_file)) {
				$data = self::init()->get_filesystem()->get_contents($local_file);
				$section_list = json_decode($data, true);
			}
			// Use local plugin assets for section thumbnails
			// $thumb_url = WPZOOM_EL_ADDONS_URL . 'assets/images/sections/';
            $thumb_url = 'https://wpzoom.s3.us-east-1.amazonaws.com/elementor/templates/assets/thumbs/inspiro/sections/';

			echo '<div class="wpzoom-main-tiled-view">';
			if (count($section_list) != 0) {
				// Group sections by category
				$category_to_items = array();
				$category_to_themes = array();
				foreach ($section_list as $item) {
					$category = isset($item['category']) ? strtolower(str_replace(' ', '-', $item['category'])) : 'general';
					$category_to_items[$category][] = $item;
					$theme_slug = strtolower(str_replace(' ', '-', isset($item['theme']) ? $item['theme'] : ''));
					if (!isset($category_to_themes[$category])) {
						$category_to_themes[$category] = array();
					}
					if (!empty($theme_slug)) {
						$category_to_themes[$category][$theme_slug] = true;
					}
				}

				// Custom order for section categories in the grid: General, Hero, Features, Contact.
				$desired_order = array('general', 'hero', 'features', 'contact');

				$category_slugs = array_keys($category_to_items);
				usort(
					$category_slugs,
					function ($a, $b) use ($desired_order) {
						$pos_a = array_search($a, $desired_order, true);
						$pos_b = array_search($b, $desired_order, true);

						if (false === $pos_a) {
							$pos_a = PHP_INT_MAX;
						}
						if (false === $pos_b) {
							$pos_b = PHP_INT_MAX;
						}

						if ($pos_a === $pos_b) {
							return strcmp($a, $b);
						}

						return $pos_a <=> $pos_b;
					}
				);

				foreach ($category_slugs as $category_slug) {
					$items = $category_to_items[$category_slug];
					$category_title = ucwords(str_replace('-', ' ', $category_slug));
					$themes_for_cat = implode(',', array_keys($category_to_themes[$category_slug]));
					// Category heading similar to Pages view
					echo '<h2 class="wpzoom-templates-library-template-category" data-theme="' . esc_attr($themes_for_cat) . '" data-category="' . esc_attr($category_slug) . '">' . esc_html($category_title) . '</h2>';
					foreach ($items as $index => $entry) {
						$slug = strtolower(str_replace(' ', '-', $entry['id']));
						$theme = isset($entry['theme']) ? $entry['theme'] : '';
						$is_restricted = false;
						?>
						<div class="wpzoom-templates-library-template wpzoom-item <?php echo $is_restricted ? 'wpzoom-template-pro-only' : ''; ?>"
							data-theme="<?php echo esc_attr(strtolower(str_replace(' ', '-', $theme))); ?>"
							data-category="<?php echo esc_attr($category_slug); ?>">
							<div class="wpzoom-template-title">
								<?php echo esc_html($entry['name']); ?>
							</div>
							<div class="wpzoom-template-thumb wpzoom-sections-index-<?php echo esc_attr($index); ?> <?php echo $is_restricted ? 'wpzoom-template-thumb-locked' : ''; ?>"
								data-index="<?php echo esc_attr($index); ?>" data-template="<?php echo esc_attr(wp_json_encode($entry)); ?>">
								<img src="<?php echo esc_url($thumb_url . $entry['thumbnail'] . '-thumb.png'); ?>" 
									alt="<?php echo esc_attr($entry['name']); ?>" 
									class="wpzoom-thumb-image">
								<?php if ($is_restricted): ?>
									<div class="wpzoom-template-overlay">
										<div class="wpzoom-template-lock-icon">🔒</div>
										<div class="wpzoom-template-pro-text"><?php esc_html_e('PRO Only', 'wpzoom-elementor-addons'); ?></div>
									</div>
								<?php endif; ?>
							</div>
							<div class="wpzoom-action-bar">
								<div class="wpzoom-grow"> </div>
								<?php if ($is_restricted): ?>
									<a href="https://www.wpzoom.com/plugins/wpzoom-elementor-addons/" target="_blank"
										class="wpzoom-btn-template-upgrade wpzoom-btn-pro-required"
										title="<?php echo esc_attr__('Get Pro Plugin', 'wpzoom-elementor-addons'); ?>">
										<?php echo esc_html__('Get Pro Plugin', 'wpzoom-elementor-addons'); ?>
									</a>
								<?php else: ?>
									<div class="wpzoom-btn-template-insert"
										data-version="WPZ__section-version-<?php echo esc_attr($index); ?>"
										data-template-name="<?php echo esc_attr($slug); ?>">
										<?php esc_html_e('Insert Section', 'wpzoom-elementor-addons'); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php
					}
				}
			} else {
				echo '<div class="wpzoom-no-results"> <i class="fa fa-frown-o"></i> ' . esc_html__('No Sections Found!', 'wpzoom-elementor-addons') . ' </div>';
			}

			echo '</div>';
			wp_die();
		}

		/**
		 * Sections (patterns): Preview handler
		 */
		public function ajax_get_wpzoom_section_preview()
		{
			$this->get_preview_template($_POST['data'], 'sections');
			wp_die();
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

			/**
	 * Get appropriate button text and URL for Pro plugin
	 *
	 * @return array Button text and URL
	 */
	private function get_pro_button_data() {
		return array(
			'text' => esc_html__( 'Get Pro Plugin', 'wpzoom-elementor-addons' ),
			'url' => 'https://www.wpzoom.com/plugins/wpzoom-elementor-addons/'
		);
	}

	/**
	 * Get preview message for Pro plugin requirement
	 *
	 * @return string Preview message
	 */
	private function get_pro_preview_message() {
		return esc_html__( 'This template requires WPZOOM Elementor Addons Pro plugin. Get the Pro plugin to unlock this and all premium templates.', 'wpzoom-elementor-addons' );
	}

		/**
		 * Clear template cache when license status changes
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function clear_template_cache() {
			// This will force the template library to reload with updated restrictions
			// The cache is cleared by removing the JavaScript WPZCached variable on next load
			// We could also delete transients here if we implement server-side caching
		}
	}

	// Initialize the Elementor library
	WPZOOM_Elementor_Library_Manager::init();

} // Make sure class doesn't already exist
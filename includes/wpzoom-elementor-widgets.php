<?php
namespace WPZOOMElementorWidgets;

use Elementor\Plugin;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Instance the plugin
WPZOOM_Elementor_Widgets::instance();

/**
 * Main WPZOOM Elementor Widgets Class
 *
 * @since 1.0.0
 */
class WPZOOM_Elementor_Widgets {

	/**
	 * Instance
	 *
	 * @var WPZOOM_Elementor_Widgets The single instance of the class.
	 * @since 1.0.0
	 * @access private
	 * @static
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/init', array( $this, 'init' ), 9 );
	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {

		// Add Plugin actions
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_widget_categories' ] );
		add_action( 'elementor/widgets/register', [ $this, 'init_widgets' ] );

		// Add editor assets for pro upsell
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init_widgets() {

		// Include Widget files
		foreach ( glob( __DIR__ . '/widgets/*', GLOB_ONLYDIR | GLOB_NOSORT ) as $path ) {
			$slug = str_replace( __DIR__ . '/widgets/', '', $path );
			$slug_ = str_replace( '-', '_', $slug );
			$file = trailingslashit( $path ) . $slug . '.php';

			if ( file_exists( $file ) ) {

				require_once( $file );

				$class_name = '\WPZOOMElementorWidgets\\' . ucwords( $slug_, '_' );

				if ( class_exists( $class_name ) ) {
					// Register widget
					Plugin::instance()->widgets_manager->register( new $class_name() );
				}
			}
		}
	}

	/**
	 * Add Widget Categories
	 *
	 * Add custom widget categories to Elementor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	function add_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'wpzoom-elementor-addons',
			[
				'title' => __( 'WPZOOM', 'wpzoom-elementor-addons' ),
				'icon' => 'fa fa-plug'
			]
		);
        // Check Pro plugin status and premium theme for dynamic category title
        $has_pro_plugin = class_exists( 'WPZOOM_Elementor_Addons_Pro' );
        $has_premium_theme = self::has_premium_wpzoom_theme();
        
        if ( $has_pro_plugin || $has_premium_theme ) {
            // Show "Active" status with link to license page (for Pro plugin) or just "Active" (for premium themes)
            if ( $has_pro_plugin ) {
                $pro_title = __( 'WPZOOM PRO', 'wpzoom-elementor-addons' ) . ' <span class="wpzoom-pro-active" style="color: #46b450; font-weight: 600;">✓ ' . esc_html__( 'Active', 'wpzoom-elementor-addons' ) . '</span> <a href="' . esc_url( admin_url( 'options-general.php?page=wpzoom-addons-pro' ) ) . '" class="wpzoom-license-link" title="' . esc_attr__( 'Manage License', 'wpzoom-elementor-addons' ) . '">' . esc_html__( 'License', 'wpzoom-elementor-addons' ) . '</a>';
            } else {
                // Premium theme active, just show "Active" status
                $pro_title = __( 'WPZOOM PRO', 'wpzoom-elementor-addons' ) . ' <span class="wpzoom-pro-active" style="color: #46b450; font-weight: 600;">✓ ' . esc_html__( 'Active', 'wpzoom-elementor-addons' ) . '</span>';
            }
        } else {
            // Show "Get Pro" link to plugin page
            $pro_title = __( 'WPZOOM PRO', 'wpzoom-elementor-addons' ) . ' <a href="https://www.wpzoom.com/plugins/wpzoom-elementor-addons/?utm_source=wpadmin&utm_medium=wpzoom-elementor-addons&utm_campaign=widget-panel" target="_blank" class="wpzoom-upgrade-link" title="' . esc_attr__( 'Get Pro Plugin', 'wpzoom-elementor-addons' ) . '">' . esc_html__( 'Upgrade', 'wpzoom-elementor-addons' ) . '</a>';
        }
        
        $elements_manager->add_category(
            'wpzoom-elementor-addons-pro',
            [
                'title' => $pro_title,
                'icon' => 'fa fa-plug'
            ]
        );
 		if( self::is_supported_theme( 'inspiro' ) ) {
			$elements_manager->add_category(
				'wpzoom-elementor-addons-inspiro',
				[
					'title' => __( 'Inspiro Widgets', 'wpzoom-elementor-addons' ),
					'icon' => 'fa fa-plug'
				]
			);
		}
		if( self::is_supported_theme( 'foodica' ) ) {
			$elements_manager->add_category(
				'wpzoom-elementor-addons-foodica',
				[
					'title' => __( 'WPZOOM Foodica', 'wpzoom-elementor-addons' ),
					'icon' => 'fa fa-plug'
				]
			);
		}
		if( self::is_supported_theme( 'cookbook' ) ) {
			$elements_manager->add_category(
				'wpzoom-elementor-addons-cookbook',
				[
					'title' => __( 'WPZOOM CookBook', 'wpzoom-elementor-addons' ),
					'icon' => 'fa fa-plug'
				]
			);
		}
        if( self::is_supported_theme( 'reel' ) ) {
            $elements_manager->add_category(
                'wpzoom-elementor-addons-reel',
                [
                    'title' => __( 'WPZOOM Reel', 'wpzoom-elementor-addons' ),
                    'icon' => 'fa fa-plug'
                ]
            );
        }
	}

	/**
	 * Get list of the supported themes
	 *
	 * @since 1.0.0
	 * @access private
	 * @return bool
	 */

	public static function is_supported_theme( $theme = 'inspiro' ) {

		$current_theme = get_template();

		switch( $theme ) {
			
			case 'inspiro':
				if( 'wpzoom-inspiro-pro' === $current_theme || 'inspiro' === $current_theme && class_exists( 'WPZOOM' ) ) {
					return true;
				}
			break;
			case 'foodica':
				if( 'foodica-pro' === $current_theme || 'foodica' === $current_theme && class_exists( 'WPZOOM' ) ) {
					return true;
				}
			break;
			case 'cookbook':
				if( 'wpzoom-cookbook' === $current_theme || 'wpzoom/wpzoom-cookbook' === $current_theme && class_exists( 'WPZOOM' ) ) {
					return true;
				}
			break;	
            case 'reel':
                if( 'wpzoom-reel' === $current_theme || 'wpzoom/wpzoom-reel' === $current_theme && class_exists( 'WPZOOM' ) ) {
                    return true;
                }
            break;
		
		}

		return false;

	}

	/**
	 * Check if a premium WPZOOM theme is active
	 *
	 * @since 1.0.0
	 * @access public
	 * @return bool
	 */
	public static function has_premium_wpzoom_theme() {
		return class_exists( 'WPZOOM' );
	}

	/**
	 * Get a list of all the allowed HTML tags.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Array of allowed HTML tags.
	 */
	public static function get_allowed_html_tags() {
		$allowed_html = [
			'b' => [],
			'i' => [],
			'u' => [],
			's' => [],
			'br' => [],
			'em' => [],
			'del' => [],
			'ins' => [],
			'sub' => [],
			'sup' => [],
			'code' => [],
			'mark' => [],
			'small' => [],
			'strike' => [],
			'abbr' => [
				'title' => [],
			],
			'span' => [
				'class' => [],
			],
			'strong' => [],
			'a' => [
				'href' => [],
				'title' => [],
				'class' => [],
				'id' => [],
			],
			'q' => [
				'cite' => [],
			],
			'img' => [
				'src' => [],
				'alt' => [],
				'height' => [],
				'width' => [],
			],
			'dfn' => [
				'title' => [],
			],
			'time' => [
				'datetime' => [],
			],
			'cite' => [
				'title' => [],
			],
			'acronym' => [
				'title' => [],
			],
			'hr' => [],
		];

		return $allowed_html;
	}

	/**
	 * Strip all the tags except allowed html tags
	 *
	 * @param string $string
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public static function custom_kses( $string = '' ) {
		return wp_kses( $string, self::get_allowed_html_tags() );
	}

	/**
	 * Enqueue editor assets for upgrade links
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_editor_assets() {
		// Enqueue editor CSS for upgrade links
		wp_enqueue_style(
			'wpzoom-elementor-addons-editor',
			WPZOOM_EL_ADDONS_URL . 'assets/css/editor.css',
			[],
			WPZOOM_EL_ADDONS_VER
		);

	}
}

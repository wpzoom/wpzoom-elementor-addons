<?php
namespace WPZOOMElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Embed;
use Elementor\Icons_Manager;

use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

use Elementor\Modules\DynamicTags\Module as TagsModule;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - Slider Widget.
 *
 * Elementor widget that inserts a customizable slider.
 *
 * @since 1.0.0
 */
class Slider extends Widget_Base {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		if ( ! wp_style_is( 'slick-slider', 'registered' ) ) {
			wp_register_style( 'slick-slider', WPZOOM_EL_ADDONS_URL . 'assets/vendors/slick/slick.css', null, WPZOOM_EL_ADDONS_VER );
		}

		if ( ! wp_style_is( 'slick-slider-theme', 'registered' ) ) {
			wp_register_style( 'slick-slider-theme', WPZOOM_EL_ADDONS_URL . 'assets/vendors/slick/slick-theme.css', null, WPZOOM_EL_ADDONS_VER );
		}

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-slider', plugins_url( 'frontend.css', __FILE__ ), [ 'slick-slider', 'slick-slider-theme' ], WPZOOM_EL_ADDONS_VER );

		if ( ! wp_script_is( 'jquery-slick-slider', 'registered' ) ) {
			wp_register_script( 'jquery-slick-slider', WPZOOM_EL_ADDONS_URL . 'assets/vendors/slick/slick.min.js', [ 'jquery' ], WPZOOM_EL_ADDONS_VER, true );
		}

		wp_register_script( 'wpzoom-elementor-addons-js-frontend-slider', plugins_url( 'frontend.js', __FILE__ ), [ 'jquery', 'jquery-slick-slider' ], WPZOOM_EL_ADDONS_VER, true );
	}

	/**
	 * Get widget name.
	 *
	 * Retrieve widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wpzoom-elementor-addons-slider';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Image Slider', 'wpzoom-elementor-addons' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-slides';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'wpzoom-elementor-addons' ];
	}

	/**
	 * Style Dependencies.
	 *
	 * Returns all the styles the widget depends on.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Style slugs.
	 */
	public function get_style_depends() {
		return [
			'slick-slider',
			'slick-slider-theme',
			'font-awesome-5-all',
			'font-awesome-4-shim',
			'wpzoom-elementor-addons-css-frontend-slider'
		];
	}

	/**
	 * Script Dependencies.
	 *
	 * Returns all the scripts the widget depends on.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Script slugs.
	 */
	public function get_script_depends() {
		return [
			'jquery',
			'jquery-slick-slider',
			'font-awesome-4-shim',
			'wpzoom-elementor-addons-js-frontend-slider'
		];
	}

	/**
	 * Get All Post Types.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array An array of all currently-registered post types.
	 */
	public function get_post_types() {
		$post_types = get_post_types( [ 'public' => true, 'show_in_nav_menus' => true ], 'objects' );
		$post_types = wp_list_pluck( $post_types, 'label', 'name' );

		return array_diff_key( $post_types, [ 'elementor_library', 'attachment' ] );
	}

	/**
	 * Get All Posts of Type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param  string $post_type The post type to get all posts for.
	 * @param  int    $limit     Limit to the number of posts returned.
	 * @return array             An array of all posts in the given post type.
	 */
	public function get_post_list( $post_type = 'any', $limit = -1 ) {
		global $wpdb;

		$where = '';
		$data = [];

		if ( -1 == $limit ) {
			$limit = '';
		} elseif ( 0 == $limit ) {
			$limit = "limit 0,1";
		} else {
			$limit = $wpdb->prepare( " limit 0,%d", esc_sql( $limit ) );
		}

		if ( 'any' === $post_type ) {
			$in_search_post_types = get_post_types( [ 'exclude_from_search' => false ] );

			if ( empty( $in_search_post_types ) ) {
				$where .= ' AND 1=0 ';
			} else {
				$where .= " AND {$wpdb->posts}.post_type IN ('" . join( "', '", array_map( 'esc_sql', $in_search_post_types ) ) . "')";
			}
		} elseif ( ! empty( $post_type ) ) {
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_type = %s", esc_sql( $post_type ) );
		}

		if ( ! empty( $search ) ) {
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_title LIKE %s", '%' . esc_sql( $search ) . '%' );
		}

		$query = "select post_title,ID  from $wpdb->posts where post_status = 'publish' $where $limit";
		$results = $wpdb->get_results( $query );

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$data[ $row->ID ] = $row->post_title;
			}
		}

		return $data;
	}

	/**
	 * Get All Authors.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array An array of all registered authors.
	 */
	public function get_authors_list() {
		$users = get_users( [
			'capability'          => 'edit_posts',
			'has_published_posts' => true,
			'fields' => [
				'ID',
				'display_name'
			]
		] );

		if ( ! empty( $users ) ) {
			return wp_list_pluck( $users, 'display_name', 'ID' );
		}

		return [];
	}

	/**
	 * Post Order-By Options.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array An array of all Order-By options for querying posts.
	 */
	public function get_post_orderby_options() {
		return [
			'ID'            => esc_html__( 'Post ID', 'wpzoom-elementor-addons' ),
			'author'        => esc_html__( 'Post Author', 'wpzoom-elementor-addons' ),
			'title'         => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
			'date'          => esc_html__( 'Date', 'wpzoom-elementor-addons' ),
			'modified'      => esc_html__( 'Last Modified Date', 'wpzoom-elementor-addons' ),
			'parent'        => esc_html__( 'Parent ID', 'wpzoom-elementor-addons' ),
			'rand'          => esc_html__( 'Random', 'wpzoom-elementor-addons' ),
			'comment_count' => esc_html__( 'Comment Count', 'wpzoom-elementor-addons' ),
			'menu_order'    => esc_html__( 'Menu Order', 'wpzoom-elementor-addons' )
		];
	}

	/**
	 * Get Post Terms.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array An array of all terms in the given taxonomy.
	 */
	public function get_terms_list( $taxonomy = 'category', $key = 'term_id' ) {
		$options = [];
		$terms = get_terms( [
			'taxonomy' => $taxonomy,
			'hide_empty' => true
		] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->{$key} ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Get Featured Posts settings registered by the active theme.
	 *
	 * WPZOOM themes (e.g. Indigo) register a Featured Posts module via
	 * add_theme_support( 'wpz-featured-posts-settings', [ ... ] ). This reads
	 * that registration and returns the list of usable settings (those that
	 * define both a post type and the meta key used to flag featured posts),
	 * so the slider can offer the theme's featured posts as a slides source.
	 *
	 * @since 1.4.8
	 * @access public
	 * @return array List of featured posts settings; empty when unsupported.
	 */
	public function get_featured_posts_settings() {
		if ( ! current_theme_supports( 'wpz-featured-posts-settings' ) ) {
			return [];
		}

		$wrapped = get_theme_support( 'wpz-featured-posts-settings' );

		if ( ! is_array( $wrapped ) || empty( $wrapped ) ) {
			return [];
		}

		// add_theme_support() wraps the passed array in an outer array.
		$settings = array_pop( $wrapped );

		if ( ! is_array( $settings ) ) {
			return [];
		}

		$available = [];

		foreach ( $settings as $setting ) {
			// A usable setting must define the post type and the meta key
			// that flags a post as featured.
			if ( empty( $setting['post_type'] ) || empty( $setting['name'] ) ) {
				continue;
			}

			$available[] = $setting;
		}

		return $available;
	}

	/**
	 * Register Controls.
	 *
	 * Registers all the controls for this widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Register Content Controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'_section_slides',
			[
				'label' => esc_html__( 'Slides', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$featured_settings = $this->get_featured_posts_settings();

		$slides_source_options = [
			'custom' => esc_html__( 'Custom', 'wpzoom-elementor-addons' ),
			'posts' => esc_html__( 'WordPress Posts', 'wpzoom-elementor-addons' )
		];

		// Only expose the theme's Featured Posts when the active theme registers it.
		if ( ! empty( $featured_settings ) ) {
			$slides_source_options['featured'] = esc_html__( 'Featured Posts (Theme)', 'wpzoom-elementor-addons' );
		}

		$this->add_control(
			'slides_source',
			[
				'label' => esc_html__( 'Source', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => $slides_source_options,
				'separator' => 'after'
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'type' => Controls_Manager::MEDIA,
				'label' => esc_html__( 'Image', 'wpzoom-elementor-addons' ),
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);
        /*
		$repeater->add_control(
			'video',
			[
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'label' => esc_html__( 'Video', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'None', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Custom', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'frontend_available' => true
			]
		);

		$repeater->start_popover();

		$repeater->add_control(
			'video_type',
			[
				'label' => esc_html__( 'Source', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'condition' => [
					'video' => 'yes'
				],
				'default' => 'youtube',
				'options' => [
					'youtube' => esc_html__( 'YouTube', 'wpzoom-elementor-addons' ),
					'vimeo' => esc_html__( 'Vimeo', 'wpzoom-elementor-addons' ),
					'dailymotion' => esc_html__( 'Dailymotion', 'wpzoom-elementor-addons' ),
					'hosted' => esc_html__( 'Self Hosted', 'wpzoom-elementor-addons' )
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'youtube_url',
			[
				'label' => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'placeholder' => esc_html__( 'Enter your URL', 'wpzoom-elementor-addons' ) . ' (YouTube)',
				'default' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				'label_block' => true,
				'condition' => [
					'video' => 'yes',
					'video_type' => 'youtube'
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'vimeo_url',
			[
				'label' => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'placeholder' => esc_html__( 'Enter your URL', 'wpzoom-elementor-addons' ) . ' (Vimeo)',
				'default' => 'https://vimeo.com/235215203',
				'label_block' => true,
				'condition' => [
					'video' => 'yes',
					'video_type' => 'vimeo'
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'dailymotion_url',
			[
				'label' => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'placeholder' => esc_html__( 'Enter your URL', 'wpzoom-elementor-addons' ) . ' (Dailymotion)',
				'default' => 'https://www.dailymotion.com/video/x6tqhqb',
				'label_block' => true,
				'condition' => [
					'video' => 'yes',
					'video_type' => 'dailymotion'
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'insert_url',
			[
				'label' => esc_html__( 'External URL', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video' => 'yes',
					'video_type' => 'hosted'
				]
			]
		);

		$repeater->add_control(
			'hosted_url',
			[
				'label' => esc_html__( 'Choose File', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::MEDIA_CATEGORY
					]
				],
				'media_type' => 'video',
				'condition' => [
					'video' => 'yes',
					'video_type' => 'hosted',
					'insert_url' => ''
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'external_url',
			[
				'label' => esc_html__( 'URL', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'options' => false,
				'label_block' => true,
				'show_label' => false,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'media_type' => 'video',
				'placeholder' => esc_html__( 'Enter your URL', 'wpzoom-elementor-addons' ),
				'condition' => [
					'video' => 'yes',
					'video_type' => 'hosted',
					'insert_url' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$repeater->end_popover(); */

		$repeater->add_control(
			'title',
			[
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'label' => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
				'placeholder' => esc_html__( 'Type title here', 'wpzoom-elementor-addons' ),
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'subtitle',
			[
				'label' => esc_html__( 'Subtitle', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'placeholder' => esc_html__( 'Type subtitle here', 'wpzoom-elementor-addons' ),
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => 'https://example.com',
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$placeholder = [
			'image' => [
				'url' => Utils::get_placeholder_image_src(),
			],
		];

		$this->add_control(
			'slides',
			[
				'show_label' => false,
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '<# print(title || "Slider Item"); #>',
				'default' => array_fill( 0, 7, $placeholder ),
				'condition' => [
					'slides_source' => 'custom'
				]
			]
		);

		$post_types = $this->get_post_types();
		$post_types[ 'by_id' ] = esc_html__( 'Manual Selection', 'wpzoom-elementor-addons' );
		$post_list = $this->get_post_list();
		$author_list = $this->get_authors_list();
		$taxonomies = get_taxonomies( [], 'objects' );
		$orderby_options = $this->get_post_orderby_options();

		$this->add_control(
			'posts_type',
			[
				'label' => esc_html__( 'Posts Source', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => $post_types,
				'default' => key( $post_types ),
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_control(
			'posts_ids',
			[
				'label' => esc_html__( 'Search & Select', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT2,
				'options' => $post_list,
				'label_block' => true,
				'multiple' => true,
				'condition' => [
					'slides_source' => 'posts',
					'posts_type' => 'by_id'
				]
			]
		);

		$this->add_control(
			'posts_authors', [
				'label' => esc_html__( 'Author', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'default' => [],
				'options' => $author_list,
				'condition' => [
					'slides_source' => 'posts',
					'posts_type!' => [ 'by_id' ]
				]
			]
		);

		foreach ( $taxonomies as $taxonomy => $object ) {
			if ( ! isset( $object->object_type[0] ) || ! in_array( $object->object_type[0], array_keys( $post_types ) ) ) {
				continue;
			}

			$this->add_control(
				'posts_' . $taxonomy . '_ids',
				[
					'label' => $object->label,
					'type' => Controls_Manager::SELECT2,
					'label_block' => true,
					'multiple' => true,
					'object_type' => $taxonomy,
					'options' => wp_list_pluck( get_terms( $taxonomy ), 'name', 'term_id' ),
					'condition' => [
						'slides_source' => 'posts',
						'posts_type' => $object->object_type
					]
				]
			);
		}

		$this->add_control(
			'posts__not_in',
			[
				'label' => esc_html__( 'Exclude', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT2,
				'options' => $post_list,
				'label_block' => true,
				'post_type' => '',
				'multiple' => true,
				'condition' => [
					'slides_source' => 'posts',
					'posts_type!' => [ 'by_id' ]
				]
			]
		);

		$this->add_control(
			'posts_offset',
			[
				'label' => esc_html__( 'Offset', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '0',
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_control(
			'posts_orderby',
			[
				'label' => esc_html__( 'Order By', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => $orderby_options,
				'default' => 'date',
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_control(
			'posts_order',
			[
				'label' => esc_html__( 'Order', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'asc' => esc_html__( 'Ascending', 'wpzoom-elementor-addons' ),
					'desc' => esc_html__( 'Descending', 'wpzoom-elementor-addons' ),
				],
				'default' => 'desc',
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		$this->add_control(
			'posts_amount',
			[
				'label' => esc_html__( 'Amount', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'default' => 5,
				'condition' => [
					'slides_source' => 'posts'
				]
			]
		);

		// Featured Posts (theme) source controls.
		if ( ! empty( $featured_settings ) ) {
			$featured_types = [];
			$featured_default_type = '';
			$featured_default_amount = 5;

			foreach ( $featured_settings as $setting ) {
				$featured_types[ $setting['post_type'] ] = ! empty( $setting['menu_title'] )
					? $setting['menu_title']
					: $setting['post_type'];

				// Prefer the post type the theme currently exposes as featured.
				if ( '' === $featured_default_type && ! empty( $setting['show'] ) ) {
					$featured_default_type = $setting['post_type'];
				}

				// Use the theme's configured limit as a sensible default amount.
				if ( 5 === $featured_default_amount && ! empty( $setting['posts_limit'] ) ) {
					$featured_default_amount = intval( $setting['posts_limit'] );
				}
			}

			if ( '' === $featured_default_type ) {
				$featured_default_type = (string) key( $featured_types );
			}

			if ( count( $featured_types ) > 1 ) {
				$this->add_control(
					'featured_post_type',
					[
						'label' => esc_html__( 'Featured Type', 'wpzoom-elementor-addons' ),
						'type' => Controls_Manager::SELECT,
						'options' => $featured_types,
						'default' => $featured_default_type,
						'condition' => [
							'slides_source' => 'featured'
						]
					]
				);
			} else {
				// Single featured type: store it without showing a redundant dropdown.
				$this->add_control(
					'featured_post_type',
					[
						'type' => Controls_Manager::HIDDEN,
						'default' => $featured_default_type,
						'condition' => [
							'slides_source' => 'featured'
						]
					]
				);
			}

			$this->add_control(
				'featured_amount',
				[
					'label' => esc_html__( 'Amount', 'wpzoom-elementor-addons' ),
					'type' => Controls_Manager::NUMBER,
					'min' => 1,
					'step' => 1,
					'max' => 100,
					'default' => $featured_default_amount,
					'condition' => [
						'slides_source' => 'featured'
					]
				]
			);
		}

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'thumbnail',
				'default' => 'medium_large',
				'separator' => 'before',
				'exclude' => [
					'custom'
				]
			]
		);

		// Elements shown when pulling slides from posts / featured posts.
		$query_sources = [ 'posts', 'featured' ];

		$this->add_control(
			'_heading_post_elements',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Display Elements', 'wpzoom-elementor-addons' ),
				'separator' => 'before',
				'condition' => [
					'slides_source' => $query_sources
				]
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => esc_html__( 'Show Title', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'slides_source' => $query_sources
				]
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label' => esc_html__( 'Show Excerpt', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'slides_source' => $query_sources
				]
			]
		);

		$this->add_control(
			'show_read_more',
			[
				'label' => esc_html__( 'Show Read More Button', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => '',
				'condition' => [
					'slides_source' => $query_sources
				]
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label' => esc_html__( 'Button Text', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Read More', 'wpzoom-elementor-addons' ),
				'placeholder' => esc_html__( 'Read More', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'slides_source' => $query_sources,
					'show_read_more' => 'yes'
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_settings',
			[
				'label' => esc_html__( 'Settings', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'settings_slides',
			[
				'label' => esc_html__( 'Slides', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING
			]
		);

		$this->add_control(
			'animation_speed',
			[
				'label' => esc_html__( 'Animation Speed', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 100,
				'step' => 10,
				'max' => 10000,
				'default' => 300,
				'description' => esc_html__( 'Slide speed in milliseconds', 'wpzoom-elementor-addons' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__( 'Autoplay?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => esc_html__( 'Autoplay Speed', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 100,
				'step' => 100,
				'max' => 10000,
				'default' => 3000,
				'description' => esc_html__( 'Autoplay speed in milliseconds', 'wpzoom-elementor-addons' ),
				'condition' => [
					'autoplay' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'loop',
			[
				'label' => esc_html__( 'Infinite Loop?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'center',
			[
				'label' => esc_html__( 'Center Mode?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'description' => esc_html__( 'Best works with odd number of slides (Slides To Show) and loop (Infinite Loop)', 'wpzoom-elementor-addons' ),
				'frontend_available' => true,
				'style_transfer' => true
			]
		);

		$this->add_control(
			'vertical',
			[
				'label' => esc_html__( 'Vertical Mode?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'frontend_available' => true,
				'style_transfer' => true
			]
		);

		$this->add_responsive_control(
			'slides_to_show',
			[
				'label' => esc_html__( 'Slides To Show', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					1 => esc_html__( '1 Slide', 'wpzoom-elementor-addons' ),
					2 => esc_html__( '2 Slides', 'wpzoom-elementor-addons' ),
					3 => esc_html__( '3 Slides', 'wpzoom-elementor-addons' ),
					4 => esc_html__( '4 Slides', 'wpzoom-elementor-addons' ),
					5 => esc_html__( '5 Slides', 'wpzoom-elementor-addons' ),
					6 => esc_html__( '6 Slides', 'wpzoom-elementor-addons' )
				],
				'desktop_default' => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
				'frontend_available' => true,
				'style_transfer' => true
			]
		);
        /*
		$this->add_control(
			'settings_video',
			[
				'label' => esc_html__( 'Video', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before'
			]
		);

		$this->add_control(
			'video_autoplay',
			[
				'label' => esc_html__( 'Autoplay', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'play_on_mobile',
			[
				'label' => esc_html__( 'Play On Mobile', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_autoplay' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_mute',
			[
				'label' => esc_html__( 'Mute', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_loop',
			[
				'label' => esc_html__( 'Loop', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_controls',
			[
				'label' => esc_html__( 'Player Controls', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_showinfo',
			[
				'label' => esc_html__( 'Video Info', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_modestbranding',
			[
				'label' => esc_html__( 'Modest Branding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_controls' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_logo',
			[
				'label' => esc_html__( 'Logo', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'yt_privacy',
			[
				'label' => esc_html__( 'Privacy Mode', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'When you turn on privacy mode, YouTube won\'t store information about visitors on your website unless they play the video.', 'wpzoom-elementor-addons' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_rel',
			[
				'label' => esc_html__( 'Suggested Videos', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Current Video Channel', 'wpzoom-elementor-addons' ),
					'yes' => esc_html__( 'Any Video', 'wpzoom-elementor-addons' )
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'vimeo_title',
			[
				'label' => esc_html__( 'Intro Title', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'vimeo_portrait',
			[
				'label' => esc_html__( 'Intro Portrait', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'default' => 'yes',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'vimeo_byline',
			[
				'label' => esc_html__( 'Intro Byline', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_download_button',
			[
				'label' => esc_html__( 'Download Button', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'frontend_available' => true
			]
		);

		$this->add_control(
			'video_poster',
			[
				'label' => esc_html__( 'Poster', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true
				],
				'frontend_available' => true
			]
		);

		$this->add_control(
			'show_play_icon',
			[
				'label' => esc_html__( 'Play Icon', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);
        */
		$this->add_control(
			'settings_navigation',
			[
				'label' => esc_html__( 'Navigation', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before'
			]
		);

		$this->add_control(
			'navigation',
			[
				'label' => esc_html__( 'Navigation', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'none' => esc_html__( 'None', 'wpzoom-elementor-addons' ),
					'arrow' => esc_html__( 'Arrow', 'wpzoom-elementor-addons' ),
					'dots' => esc_html__( 'Dots', 'wpzoom-elementor-addons' ),
					'both' => esc_html__( 'Arrow & Dots', 'wpzoom-elementor-addons' )
				],
				'default' => 'arrow',
				'frontend_available' => true,
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'arrow_prev_icon',
			[
				'label' => esc_html__( 'Previous Icon', 'wpzoom-elementor-addons' ),
				'label_block' => false,
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'default' => [
					'value' => 'fas fa-chevron-left',
					'library' => 'fa-solid'
				],
				'condition' => [
					'navigation' => [ 'arrow', 'both' ]
				],
			]
		);

		$this->add_control(
			'arrow_next_icon',
			[
				'label' => esc_html__( 'Next Icon', 'wpzoom-elementor-addons' ),
				'label_block' => false,
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'default' => [
					'value' => 'fas fa-chevron-right',
					'library' => 'fa-solid'
				],
				'condition' => [
					'navigation' => [ 'arrow', 'both' ]
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register Style Controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_style_controls() {
		$this->start_controls_section(
			'_section_style_slider',
			[
				'label' => esc_html__( 'Slider', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'slider_height',
			[
				'label' => esc_html__( 'Height', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'description' => esc_html__( 'Sets the height of the slider. Images fill this area and are cropped to fit (cover), so the slider stays fluid across screen sizes.', 'wpzoom-elementor-addons' ),
				'size_units' => [ 'px', 'vh', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1500,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					]
				],
				'default' => [
					'unit' => 'px',
					'size' => 550
				],
				'tablet_default' => [
					'unit' => 'px',
					'size' => 350
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 250
				],
				'selectors' => [
					'{{WRAPPER}} .slick-slider' => 'height: {{SIZE}}{{UNIT}};'
				],
			]
		);

		$this->end_controls_section();
		
		$this->start_controls_section(
			'_section_style_item',
			[
				'label' => esc_html__( 'Slider Item', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label' => esc_html__( 'Slide Spacing (px)', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} .slick-slider:not(.slick-vertical) .slick-slide' => 'padding-right: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .slick-slider.slick-vertical .slick-slide' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_overlay',
			[
				'label' => esc_html__( 'Background Overlay', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'background_overlay_enable',
			[
				'label' => esc_html__( 'Enable Overlay', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-item::before' => 'display: block;',
				],
			]
		);

		$this->add_control(
			'background_overlay_color',
			[
				'label' => esc_html__( 'Overlay Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-item::before' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'background_overlay_enable' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'background_overlay_opacity',
			[
				'label' => esc_html__( 'Overlay Opacity', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.01,
					],
				],
				'default' => [
					'size' => 0.5,
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-item::before' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'background_overlay_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'background_overlay_blend_mode',
			[
				'label' => esc_html__( 'Blend Mode', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'normal' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
					'multiply' => esc_html__( 'Multiply', 'wpzoom-elementor-addons' ),
					'screen' => esc_html__( 'Screen', 'wpzoom-elementor-addons' ),
					'overlay' => esc_html__( 'Overlay', 'wpzoom-elementor-addons' ),
					'darken' => esc_html__( 'Darken', 'wpzoom-elementor-addons' ),
					'lighten' => esc_html__( 'Lighten', 'wpzoom-elementor-addons' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'wpzoom-elementor-addons' ),
					'color-burn' => esc_html__( 'Color Burn', 'wpzoom-elementor-addons' ),
					'hard-light' => esc_html__( 'Hard Light', 'wpzoom-elementor-addons' ),
					'soft-light' => esc_html__( 'Soft Light', 'wpzoom-elementor-addons' ),
					'difference' => esc_html__( 'Difference', 'wpzoom-elementor-addons' ),
					'exclusion' => esc_html__( 'Exclusion', 'wpzoom-elementor-addons' ),
					'hue' => esc_html__( 'Hue', 'wpzoom-elementor-addons' ),
					'saturation' => esc_html__( 'Saturation', 'wpzoom-elementor-addons' ),
					'color' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
					'luminosity' => esc_html__( 'Luminosity', 'wpzoom-elementor-addons' ),
				],
				'default' => 'normal',
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-item::before' => 'mix-blend-mode: {{VALUE}};',
				],
				'condition' => [
					'background_overlay_enable' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_content',
			[
				'label' => esc_html__( 'Slide Content', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label' => esc_html__( 'Content Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'selector' => '{{WRAPPER}} .wpz-slick-content',
				'exclude' => [
					 'image'
				]
			]
		);

		$this->add_control(
			'_heading_title',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-slick-title',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
			]
		);

		$this->add_control(
			'_heading_subtitle',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Subtitle', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'subtitle_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-subtitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-slick-subtitle',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);
        /*
		$this->add_control(
			'_heading_video',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Video', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_control(
			'aspect_ratio',
			[
				'label' => esc_html__( 'Aspect Ratio', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'169' => '16:9',
					'219' => '21:9',
					'43' => '4:3',
					'32' => '3:2',
					'11' => '1:1',
					'916' => '9:16'
				],
				'default' => '169',
				'prefix_class' => 'elementor-aspect-ratio-',
				'frontend_available' => true
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'css_filters',
				'selector' => '{{WRAPPER}} .elementor-wrapper'
			]
		);

		$this->add_control(
			'video_controls_color',
			[
				'label' => esc_html__( 'Controls Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => ''
			]
		);

		$this->add_control(
			'play_icon_color',
			[
				'label' => esc_html__( 'Play Icon Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-custom-embed-play i' => 'color: {{VALUE}}'
				],
				'condition' => [
					'show_play_icon' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'play_icon_size',
			[
				'label' => esc_html__( 'Play Icon Size', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 300
					]
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-custom-embed-play i' => 'font-size: {{SIZE}}{{UNIT}}'
				],
				'condition' => [
					'show_play_icon' => 'yes'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'play_icon_text_shadow',
				'selector' => '{{WRAPPER}} .elementor-custom-embed-play i',
				'fields_options' => [
					'text_shadow_type' => [
						'label' => _x( 'Play Icon Shadow', 'Text Shadow Control', 'wpzoom-elementor-addons' )
					]
				],
				'condition' => [
					'show_play_icon' => 'yes'
				]
			]
		);
*/
		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_button',
			[
				'label' => esc_html__( 'Read More Button', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'slides_source' => [ 'posts', 'featured' ],
					'show_read_more' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'read_more_spacing',
			[
				'label' => esc_html__( 'Top Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ]
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-button' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'read_more_typography',
				'selector' => '{{WRAPPER}} .wpz-slick-button',
			]
		);

		$this->add_responsive_control(
			'read_more_padding',
			[
				'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'read_more_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'read_more_tabs' );

		$this->start_controls_tab(
			'read_more_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' )
			]
		);

		$this->add_control(
			'read_more_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'read_more_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpz-slick-button',
				'exclude' => [ 'image' ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'read_more_border',
				'selector' => '{{WRAPPER}} .wpz-slick-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'read_more_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' )
			]
		);

		$this->add_control(
			'read_more_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'read_more_background_hover',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpz-slick-button:hover',
				'exclude' => [ 'image' ],
			]
		);

		$this->add_control(
			'read_more_border_color_hover',
			[
				'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-slick-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_arrow',
			[
				'label' => esc_html__( 'Navigation :: Arrow', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'arrow_position_toggle',
			[
				'label' => esc_html__( 'Position', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'label_off' => esc_html__( 'None', 'wpzoom-elementor-addons' ),
				'label_on' => esc_html__( 'Custom', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
			]
		);

		$this->start_popover();

		$this->add_responsive_control(
			'arrow_position_y',
			[
				'label' => esc_html__( 'Vertical', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'condition' => [
					'arrow_position_toggle' => 'yes'
				],
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 500,
					],
					'%' => [
						'min' => -110,
						'max' => 110,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_position_x',
			[
				'label' => esc_html__( 'Horizontal', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'unit' => 'px',
					'size' => 25,
				],
				'condition' => [
					'arrow_position_toggle' => 'yes'
				],
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 500,
					],
					'%' => [
						'min' => -110,
						'max' => 110,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-prev' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .slick-next' => 'right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_popover();

		$this->add_responsive_control(
			'arrow_size',
			[
				'label' => esc_html__( 'Size', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default' => [
					'unit' => 'px',
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'arrow_border',
				'selector' => '{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next',
			]
		);

		$this->add_responsive_control(
			'arrow_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->start_controls_tabs( '_tabs_arrow' );

		$this->start_controls_tab(
			'_tab_arrow_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'arrow_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#00000000',
				'selectors' => [
					'{{WRAPPER}} .slick-prev, {{WRAPPER}} .slick-next' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_arrow_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'arrow_hover_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-prev:hover, {{WRAPPER}} .slick-next:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_hover_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-prev:hover, {{WRAPPER}} .slick-next:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'arrow_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .slick-prev:hover, {{WRAPPER}} .slick-next:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_dots',
			[
				'label' => esc_html__( 'Navigation :: Dots', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'dots_nav_position_y',
			[
				'label' => esc_html__( 'Vertical Position', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-dots' => 'bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'dots_nav_spacing',
			[
				'label' => esc_html__( 'Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .slick-dots li' => 'margin-right: calc({{SIZE}}{{UNIT}} / 2); margin-left: calc({{SIZE}}{{UNIT}} / 2);',
				],
			]
		);

		$this->add_responsive_control(
			'dots_nav_align',
			[
				'label' => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .slick-dots' => 'text-align: {{VALUE}}'
				]
			]
		);

		$this->start_controls_tabs( '_tabs_dots' );
		$this->start_controls_tab(
			'_tab_dots_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'dots_nav_size',
			[
				'label' => esc_html__( 'Size', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .slick-dots li button:before' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dots_nav_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-dots li button:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_dots_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'dots_nav_hover_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-dots li button:hover:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_dots_active',
			[
				'label' => esc_html__( 'Active', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'dots_nav_active_size',
			[
				'label' => esc_html__( 'Size', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .slick-dots li.slick-active button:before' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dots_nav_active_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-dots .slick-active button:before' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Get All Query Arguments from Settings.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param  array  $settings  The settings array that contains the query arguments.
	 * @param  string $post_type The post type that the query will be run for.
	 * @return array             Array of all the query arguments in the given settings array.
	 */
	public function get_query_args( $settings = [], $post_type = 'post' ) {
		$settings = wp_parse_args(
			$settings,
			[
				'posts_type' => $post_type,
				'posts_ids' => [],
				'posts_orderby' => 'date',
				'posts_order' => 'desc',
				'posts_amount' => 5,
				'posts_offset' => 0,
				'posts__not_in' => []
			]
		);

		$args = [
			'orderby' => $settings[ 'posts_orderby' ],
			'order' => $settings[ 'posts_order' ],
			'ignore_sticky_posts' => 1,
			'post_status' => 'publish',
			'posts_per_page' => intval( $settings[ 'posts_amount' ] ),
			'offset' => intval( $settings[ 'posts_offset' ] )
		];

		if ( 'by_id' === $settings[ 'posts_type' ] ) {
			$args[ 'post_type' ] = 'any';
			$args[ 'post__in' ] = empty( $settings[ 'posts_ids' ] ) ? [0] : $settings[ 'posts_ids' ];
		} else {
			$args[ 'post_type' ] = $settings[ 'posts_type' ];
			$args[ 'tax_query' ] = [];

			$taxonomies = get_object_taxonomies( $settings[ 'posts_type' ], 'objects' );

			foreach ( $taxonomies as $object ) {
				$setting_key = 'posts_' . $object->name . '_ids';

				if ( ! empty( $settings[ $setting_key ] ) ) {
					$args[ 'tax_query' ][] = [
						'taxonomy' => $object->name,
						'field' => 'term_id',
						'terms' => $settings[ $setting_key ]
					];
				}
			}

			if ( ! empty( $args[ 'tax_query' ] ) ) {
				$args[ 'tax_query' ][ 'relation' ] = 'AND';
			}
		}

		if ( ! empty( $settings[ 'posts_authors' ] ) ) {
			$args[ 'author__in' ] = $settings[ 'posts_authors' ];
		}

		if ( ! empty( $settings[ 'posts__not_in' ] ) ) {
			$args[ 'post__not_in' ] = $settings[ 'posts__not_in' ];
		}

		return $args;
	}

	/**
	 * Build the WP_Query args for the theme's Featured Posts.
	 *
	 * Mirrors the query WPZOOM themes use to populate their homepage featured
	 * slideshow: posts flagged with the theme's featured meta key, ordered by
	 * the manual order set on the theme's "Featured Posts" admin screen.
	 *
	 * @since 1.4.8
	 * @access public
	 * @param array $settings Widget settings.
	 * @return array Query args, or empty array when featured posts aren't available.
	 */
	public function get_featured_query_args( $settings = [] ) {
		$featured_settings = $this->get_featured_posts_settings();

		if ( empty( $featured_settings ) ) {
			return [];
		}

		$post_type = ! empty( $settings[ 'featured_post_type' ] ) ? $settings[ 'featured_post_type' ] : '';
		$meta_key  = '';

		// Resolve the meta key for the chosen post type.
		foreach ( $featured_settings as $setting ) {
			if ( $post_type === $setting[ 'post_type' ] ) {
				$meta_key = $setting[ 'name' ];
				break;
			}
		}

		// Fall back to the first registered featured setting.
		if ( '' === $meta_key ) {
			$first     = reset( $featured_settings );
			$post_type = $first[ 'post_type' ];
			$meta_key  = $first[ 'name' ];
		}

		$amount = isset( $settings[ 'featured_amount' ] ) ? intval( $settings[ 'featured_amount' ] ) : 5;

		if ( $amount < 1 ) {
			$amount = 5;
		}

		return [
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'orderby'             => 'menu_order date',
			'order'               => 'DESC',
			'meta_key'            => $meta_key,
			'meta_value'          => 1,
			'posts_per_page'      => $amount,
			'ignore_sticky_posts' => 1,
		];
	}

	/**
	 * Get embed params.
	 *
	 * Retrieve video widget embed parameters.
	 *
	 * @param array $slide The slide to get the data from.
	 * @since 1.0.0
	 * @access public
	 * @return array Video embed parameters.
	 */
	public function get_embed_params( $slide ) {
		$settings = $this->get_settings_for_display();

		$params = [];

		if ( $settings[ 'video_autoplay' ] ) {
			$params[ 'video_autoplay' ] = '1';

			if ( $settings['play_on_mobile'] ) {
				$params[ 'playsinline' ] = '1';
			}
		}

		$params_dictionary = [];

		if ( 'youtube' === $slide[ 'video_type' ] ) {
			$params_dictionary = [
				'video_loop' => 'loop',
				'video_controls' => 'controls',
				'video_mute' => 'mute',
				'video_rel' => 'rel',
				'video_modestbranding' => 'modestbranding'
			];

			if ( $settings[ 'video_loop' ] ) {
				$video_properties = Embed::get_video_properties( $slide[ 'youtube_url' ] );

				$params[ 'playlist' ] = $video_properties[ 'video_id' ];
			}

			$params[ 'wmode' ] = 'opaque';
		} elseif ( 'vimeo' === $slide[ 'video_type' ] ) {
			$params_dictionary = [
				'video_loop' => 'loop',
				'video_mute' => 'muted',
				'vimeo_title' => 'title',
				'vimeo_portrait' => 'portrait',
				'vimeo_byline' => 'byline',
			];

			$params[ 'color' ] = str_replace( '#', '', $settings[ 'video_controls_color' ] );
			$params[ 'autopause' ] = '0';
		} elseif ( 'dailymotion' === $slide[ 'video_type' ] ) {
			$params_dictionary = [
				'video_controls' => 'controls',
				'video_mute' => 'mute',
				'video_showinfo' => 'ui-start-screen-info',
				'video_logo' => 'ui-logo',
			];

			$params[ 'ui-highlight' ] = str_replace( '#', '', $settings[ 'video_controls_color' ] );

			$params[ 'endscreen-enable' ] = '0';
		}

		foreach ( $params_dictionary as $key => $param_name ) {
			$setting_name = $param_name;

			if ( is_string( $key ) ) {
				$setting_name = $key;
			}

			$setting_value = $settings[ $setting_name ] ? '1' : '0';

			$params[ $param_name ] = $setting_value;
		}

		return $params;
	}

	/**
	 * Get video embed options.
	 * 
	 * @param array $slide The slide to get the data from.
	 * @since 1.0.0
	 * @access private
	 * @return array
	 */
	private function get_embed_options( $slide ) {
		$embed_options = [];
		$settings = $this->get_settings_for_display();

		if ( 'youtube' === $slide[ 'video_type' ] ) {
			$embed_options[ 'privacy' ] = $settings[ 'yt_privacy' ];
		}

		return $embed_options;
	}

	/**
	 * Get hosted video parameters.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return array
	 */
	private function get_hosted_params() {
		$video_params = [];
		$settings = $this->get_settings_for_display();

		foreach ( [ 'video_autoplay', 'video_loop', 'video_controls' ] as $option_name ) {
			if ( $settings[ $option_name ] ) {
				$video_params[ $option_name ] = '';
			}
		}

		if ( $settings[ 'video_mute' ] ) {
			$video_params[ 'muted' ] = 'muted';
		}

		if ( $settings[ 'play_on_mobile' ] ) {
			$video_params[ 'playsinline' ] = '';
		}

		if ( ! $settings[ 'video_download_button' ] ) {
			$video_params[ 'controlsList' ] = 'nodownload';
		}

		if ( $settings[ 'video_poster' ][ 'url' ] ) {
			$video_params[ 'poster' ] = $settings[ 'video_poster' ][ 'url' ];
		}

		return $video_params;
	}

	/**
	 * Get the URL of a hosted video.
	 *
	 * @param array $slide The slide to get the data from.
	 * @since 1.0.0
	 * @access private
	 * @return string
	 */
	private function get_hosted_video_url( $slide ) {
		if ( ! empty( $slide[ 'insert_url' ] ) ) {
			$video_url = $slide[ 'external_url' ][ 'url' ];
		} else {
			$video_url = $slide[ 'hosted_url' ][ 'url' ];
		}

		if ( empty( $video_url ) ) {
			return '';
		}

		return $video_url;
	}

	/**
	 * Render a hosted video.
	 *
	 * @param array $slide The slide to get the data from.
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function render_hosted_video( $slide ) {
		$video_url = $this->get_hosted_video_url( $slide );

		if ( empty( $video_url ) ) {
			return;
		}

		$video_params = $this->get_hosted_params();

		?>
		<video class="elementor-video" src="<?php echo esc_url( $video_url ); ?>" <?php echo Utils::render_html_attributes( $video_params ); ?>></video>
		<?php
	}

	/**
	 * Render the Widget.
	 *
	 * Renders the widget on the frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides = [];
		$args = [];

		// The display toggles only apply to query-driven sources (posts / featured).
		// Custom slides keep their original behavior: show whatever was entered.
		$is_query_source = in_array( $settings[ 'slides_source' ], [ 'posts', 'featured' ], true );
		$show_title      = ! $is_query_source || 'yes' === $settings[ 'show_title' ];
		$show_excerpt    = ! $is_query_source || 'yes' === $settings[ 'show_excerpt' ];
		$show_read_more  = $is_query_source && 'yes' === $settings[ 'show_read_more' ];
		$read_more_text  = isset( $settings[ 'read_more_text' ] ) && '' !== $settings[ 'read_more_text' ]
			? $settings[ 'read_more_text' ]
			: esc_html__( 'Read More', 'wpzoom-elementor-addons' );

		if ( 'posts' == $settings[ 'slides_source' ] ) {
			$args = $this->get_query_args( $settings );
		} elseif ( 'featured' == $settings[ 'slides_source' ] ) {
			$args = $this->get_featured_query_args( $settings );
		}

		if ( 'custom' == $settings[ 'slides_source' ] ) {
			$slides = $settings[ 'slides' ];
		} elseif ( ! empty( $args ) ) {
			$query = new \WP_Query( $args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();

					$slides[] = [
						'_id' => get_the_ID(),
						'title' => get_the_title(),
						'subtitle' => get_the_excerpt(),
						'image' => [ 'id' => get_post_thumbnail_id(), 'url' => false ],
						'link' => [ 'url' => get_permalink() ]
					];
				}

				wp_reset_postdata();
			}
		}

		if ( empty( $slides ) ) {
			return;
		}

		?><div class="wpzjs-slick wpz-slick wpz-slick--slider">

			<?php foreach ( $slides as $slide ) :

                /*
				if ( isset( $slide[ 'video_type' ] ) && ! empty( $slide[ 'video_type' ] ) ) {
					$video_url = $slide[ $slide[ 'video_type' ] . '_url' ];
					$video_html = '';

					if ( 'hosted' === $slide[ 'video_type' ] ) {
						$video_url = $this->get_hosted_video_url( $slide );
					} else {
						$embed_params = $this->get_embed_params( $slide );
						$embed_options = $this->get_embed_options( $slide );
					}

					if ( ! empty( $video_url ) ) {
						if ( 'youtube' === $slide[ 'video_type' ] ) {
							$video_html = '<div class="elementor-video"></div>';
						}

						if ( 'hosted' === $slide[ 'video_type' ] ) {
							ob_start();

							$this->render_hosted_video( $slide );

							$video_html = ob_get_clean();
						} else {
							$is_static_render_mode = Plugin::$instance->frontend->is_static_render_mode();
							$post_id = get_queried_object_id();

							if ( $is_static_render_mode ) {
								$video_html = Embed::get_embed_thumbnail_html( $video_url, $post_id );
							// YouTube API requires a different markup which was set above.
							} else if ( 'youtube' !== $slide[ 'video_type' ] ) {
								$video_html = Embed::get_embed_html( $video_url, $embed_params, $embed_options );
							}
						}

						if ( empty( $video_html ) ) {
							$video_html = esc_url( $video_url );
						}

						$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-wrapper' );
						$this->add_render_attribute( 'video-wrapper', 'class', 'wpz-video-wrapper' );
						$this->add_render_attribute( 'video-wrapper', 'class', 'e-' . $slide[ 'video_type' ] . '-video' );
						$this->add_render_attribute( 'video-wrapper', 'data-video-type', $slide[ 'video_type' ] );
						$this->add_render_attribute( 'video-wrapper', 'data-video-url', $video_url );
					}
				}

                */

				$image = wp_get_attachment_image_url( $slide[ 'image' ][ 'id' ], $settings[ 'thumbnail_size' ] );

				if ( ! $image ) {
					$image = $slide[ 'image' ][ 'url' ];
				}

				$item_tag = 'div';
				$id = 'wpz-slick-item-' . $slide ['_id' ];

				$this->add_render_attribute( $id, 'class', 'wpz-slick-item' );

				if ( isset( $slide[ 'link' ] ) && ! empty( $slide[ 'link' ][ 'url' ] ) ) {
					$item_tag = 'a';
					$this->add_link_attributes( $id, $slide[ 'link' ] );
				}
				?>

				<div class="wpz-slick-slide">

					<<?php echo $item_tag; // WPCS: XSS OK. ?> <?php $this->print_render_attribute_string( $id ); ?>>

						<?php if ( $image ) : ?>
							<div class="wpz-slick-img" role="img"<?php echo ! empty( $slide[ 'title' ] ) ? ' aria-label="' . esc_attr( $slide[ 'title' ] ) . '"' : ''; ?> style="background-image: url('<?php echo esc_url( $image ); ?>');"></div>
						<?php endif; ?>

						<?php /* if ( isset( $slide[ 'video_type' ] ) && ! empty( $slide[ 'video_type' ] ) && ! empty( $video_html ) ) : ?>
							<div <?php echo $this->get_render_attribute_string( 'video-wrapper' ); ?>>
								<?php echo $video_html; // WPCS: XSS OK. ?>

								<?php if ( 'yes' === $settings[ 'show_play_icon' ] ) : ?>
									<div class="elementor-custom-embed-play" role="button">
										<i class="eicon-play" aria-hidden="true"></i>
										<span class="elementor-screen-only"><?php _e( 'Play Video', 'wpzoom-elementor-addons' ); ?></span>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; */ ?>

						<?php
						$has_title    = $show_title && ! empty( $slide[ 'title' ] );
						$has_subtitle = $show_excerpt && ! empty( $slide[ 'subtitle' ] );
						$has_button   = $show_read_more && '' !== $read_more_text;
						?>
						<?php if ( $has_title || $has_subtitle || $has_button ) : ?>
							<div class="wpz-slick-content">
								<?php if ( $has_title ) : ?>
									<h2 class="wpz-slick-title"><?php echo WPZOOM_Elementor_Widgets::custom_kses( $slide[ 'title' ] ); ?></h2>
								<?php endif; ?>
								<?php if ( $has_subtitle ) : ?>
									<p class="wpz-slick-subtitle"><?php echo WPZOOM_Elementor_Widgets::custom_kses( $slide[ 'subtitle' ] ); ?></p>
								<?php endif; ?>
								<?php if ( $has_button ) : ?>
									<span class="wpz-slick-button"><?php echo esc_html( $read_more_text ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

					</<?php echo $item_tag; // WPCS: XSS OK. ?>>

				</div>

			<?php endforeach; ?>

		</div>

		<?php if ( ! empty( $settings[ 'arrow_prev_icon' ][ 'value' ] ) ) : ?>
			<button type="button" class="slick-prev"><?php Icons_Manager::render_icon( $settings[ 'arrow_prev_icon' ], [ 'aria-hidden' => 'true' ] ); ?></button>
		<?php endif; ?>

		<?php if ( ! empty( $settings[ 'arrow_next_icon' ][ 'value' ] ) ) : ?>
			<button type="button" class="slick-next"><?php Icons_Manager::render_icon( $settings[ 'arrow_next_icon' ], [ 'aria-hidden' => 'true' ] ); ?></button>
		<?php endif; ?>

		<?php
	}
}

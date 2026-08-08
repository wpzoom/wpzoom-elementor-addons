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
class Video_Slider extends Widget_Base {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-video-slider', plugins_url( 'frontend.css', __FILE__ ), [], WPZOOM_EL_ADDONS_VER );

		wp_register_script( 'wpzoom-elementor-addons-js-frontend-video-slider', plugins_url( 'frontend.js', __FILE__ ), [ 'jquery', 'swiper' ], WPZOOM_EL_ADDONS_VER, true );
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
		return 'wpzoom-elementor-addons-video-slider';
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
		$title = esc_html__( 'Video Slideshow', 'wpzoom-elementor-addons' );
		
		if ( ! $this->has_premium_access() ) {
			$title .= ' ' . esc_html__( '(Pro)', 'wpzoom-elementor-addons' );
		}
		
		return $title;
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
		if ( ! $this->has_premium_access() ) {
			return 'eicon-lock';
		}
		
		return 'eicon-media-carousel';
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
		return [ 'wpzoom-elementor-addons-pro' ];
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
			'swiper',
			'font-awesome-5-all',
			'font-awesome-4-shim',
			'wpzoom-elementor-addons-css-frontend-video-slider'
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
			'swiper',
			'font-awesome-4-shim',
			'wpzoom-elementor-addons-js-frontend-video-slider'
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
	 * Register Controls.
	 *
	 * Registers all the controls for this widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_controls() {
		if ( ! $this->has_premium_access() ) {
			$this->register_premium_upgrade_controls();
			return;
		}
		
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Register Premium Upgrade Controls.
	 *
	 * Shows upgrade notice for non-premium users.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function register_premium_upgrade_controls() {
		$this->start_controls_section(
			'_section_premium_upgrade',
			[
				'label' => esc_html__( 'Video Slideshow Widget', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'premium_upgrade_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					'<div style="text-align: center; padding: 25px 20px; background: #0f5099; border-radius: 12px; color: white; position: relative; overflow: hidden;">
						<div style="position: absolute; top: -20px; right: -20px; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%%;"></div>
						<div style="position: absolute; bottom: -30px; left: -30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%%;"></div>
						<div style="position: relative; z-index: 2;">
							<div style="font-size: 48px; margin-bottom: 15px;">🎬</div>
							<h3 style="margin: 0 0 12px 0; color: white; font-size: 22px; font-weight: 600;">%s</h3>
							<p style="margin: 0 0 25px 0; color: rgba(255,255,255,0.9); line-height: 1.6; font-size: 15px; max-width: 400px; margin-left: auto; margin-right: auto;">
								%s
							</p>
							<div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
								<a href="%s" target="_blank" style="display: inline-block; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 600; font-size: 14px; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.3);">
									%s
								</a>
								<a href="%s" target="_blank" style="display: inline-block; background: white; color: #0f5099; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 600; font-size: 14px; transition: all 0.3s ease;">
									%s
								</a>
							</div>
							<div style="margin-top: 20px; font-size: 12px; color: rgba(255,255,255,0.7);">
								✨ %s
							</div>
						</div>
					</div>',
					esc_html__( 'Video Slideshow Widget', 'wpzoom-elementor-addons' ),
					esc_html__( 'Create stunning video slideshows with background videos, custom overlays, and advanced animations. This premium widget requires the WPZOOM Elementor Addons Pro plugin to be installed and activated, or you can use it with any premium WPZOOM theme.', 'wpzoom-elementor-addons' ),
					esc_url( 'https://www.wpzoom.com/themes/' ),
					esc_html__( 'Browse WPZOOM Themes', 'wpzoom-elementor-addons' ),
					esc_url( 'https://www.wpzoom.com/plugins/wpzoom-elementor-addons/' ),
					esc_html__( 'Get Pro Plugin', 'wpzoom-elementor-addons' ),
					esc_html__( 'Unlock all Elementor templates', 'wpzoom-elementor-addons' )
				),
				'content_classes' => 'wpzoom-premium-upgrade-notice',
			]
		);

		$this->end_controls_section();
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

		$this->add_control(
			'slides_source',
			[
				'label' => esc_html__( 'Source', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'custom' => esc_html__( 'Custom', 'wpzoom-elementor-addons' ),
					'posts' => esc_html__( 'WordPress Posts', 'wpzoom-elementor-addons' )
				],
				'separator' => 'after'
			]
		);

		$repeater = new Repeater();

		// Start controls tabs
		$repeater->start_controls_tabs( 'slides_repeater' );

		// Background Tab
		$repeater->start_controls_tab(
			'background',
			[
				'label' => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
			]
		);

		// Background Type Control
		$repeater->add_control(
			'background_type',
			[
				'label' => esc_html__( 'Background Type', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
                'description' => esc_html__( 'The overlay can be customized in the Style tab', 'wpzoom-elementor-addons' ),
				'options' => [
					'image' => [
						'title' => esc_html__( 'Image', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-image',
					],
					'video' => [
						'title' => esc_html__( 'Video', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-video-camera',
					],
				],
				'default' => 'image',
				'toggle' => false,
				'frontend_available' => true,
			]
		);

		// Image Background Controls
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
				],
				'condition' => [
					'background_type' => 'image',
				],
			]
		);

		// Video Background Controls
		$repeater->add_control(
			'video_source',
			[
				'label' => esc_html__( 'Source', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'external' => esc_html__( 'External URL', 'wpzoom-elementor-addons' ),
					'hosted' => esc_html__( 'Self Hosted', 'wpzoom-elementor-addons' ),
				],
				'default' => 'external',
				'condition' => [
					'background_type' => 'video',
				],
				'frontend_available' => true,
			]
		);



		$repeater->add_control(
			'video_link',
			[
				'label' => esc_html__( 'Video URL', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY
					]
				],
				'placeholder' => esc_html__( 'Enter YouTube or Vimeo URL', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'condition' => [
					'background_type' => 'video',
					'video_source' => 'external',
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'video_file',
			[
				'label' => esc_html__( 'Choose Video File', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::MEDIA,
				'media_types' => [ 'video' ],
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'background_type' => 'video',
					'video_source' => 'hosted',
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'video_start_time',
			[
				'label' => esc_html__( 'Start Time', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::NUMBER,
				'description' => esc_html__( 'Specify a start time (in seconds)', 'wpzoom-elementor-addons' ),
				'condition' => [
					'background_type' => 'video',
					'video_source' => 'external',
					'video_link!' => '',
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'video_end_time',
			[
				'label' => esc_html__( 'End Time', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::NUMBER,
				'description' => esc_html__( 'Specify an end time (in seconds)', 'wpzoom-elementor-addons' ),
				'condition' => [
					'background_type' => 'video',
					'video_source' => 'external',
					'video_link!' => '',
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'video_play_once',
			[
				'label' => esc_html__( 'Play Once', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'background_type' => 'video',
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'video_play_on_mobile',
			[
				'label' => esc_html__( 'Play On Mobile', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'background_type' => 'video',
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'video_privacy_mode',
			[
				'label' => esc_html__( 'Privacy Mode', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Enable privacy mode for YouTube videos. YouTube won\'t store information about visitors unless they play the video.', 'wpzoom-elementor-addons' ),
				'condition' => [
					'background_type' => 'video',
					'video_source' => 'external',
					'video_link!' => '',
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'background_fallback',
			[
				'label' => esc_html__( 'Background Fallback', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::MEDIA,
				'description' => esc_html__( 'This cover image will replace the background video in case that the video could not be loaded.', 'wpzoom-elementor-addons' ),
				'condition' => [
					'background_type' => 'video',
				],
				'dynamic' => [
					'active' => true,
				],
				'frontend_available' => true,
			]
		);

		$repeater->end_controls_tab();

		// Content Tab
		$repeater->start_controls_tab(
			'content',
			[
				'label' => esc_html__( 'Content', 'wpzoom-elementor-addons' ),
			]
		);

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
            'link',
            [
                'label' => esc_html__( 'Title Link', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::URL,
                'label_block' => true,
                'placeholder' => 'https://example.com',
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

		$repeater->add_control(
			'subtitle',
			[
				'label' => esc_html__( 'Subtitle', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'placeholder' => esc_html__( 'Type subtitle here', 'wpzoom-elementor-addons' ),
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'show_button',
			[
				'label' => esc_html__( 'Show Button', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);

		$repeater->add_control(
			'button_text',
			[
				'label' => esc_html__( 'Button Text', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Learn More', 'wpzoom-elementor-addons' ),
                'label_block' => true,
				'condition' => [
					'show_button' => 'yes',
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'button_link',
			[
				'label' => esc_html__( 'Button Link', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'condition' => [
					'show_button' => 'yes',
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->end_controls_tab();

		// Lightbox Tab
		$repeater->start_controls_tab(
			'lightbox',
			[
				'label' => esc_html__( 'Lightbox', 'wpzoom-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'show_video_lightbox',
			[
				'label' => esc_html__( 'Show Video Lightbox', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);

		$repeater->add_control(
			'lightbox_video_url',
			[
				'label' => esc_html__( 'Lightbox Video URL', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'YouTube or Vimeo URL', 'wpzoom-elementor-addons' ),
                'label_block' => true,
				'condition' => [
					'show_video_lightbox' => 'yes',
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'lightbox_text',
			[
				'label' => esc_html__( 'Lightbox Text', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Watch Video', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'condition' => [
					'show_video_lightbox' => 'yes',
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$placeholder = [
			'image' => [
				'url' => Utils::get_placeholder_image_src(),
			],
		];

		// Sample slides with different configurations
		$sample_slides = [
			// Slide 1: Self-hosted MP4 video background with button
			[
				'_id' => 'slide_1',
				'background_type' => 'video',
				'video_source' => 'external',
				'video_link' => 'https://wpzoom.s3.amazonaws.com/inspiro-blocks-pro/video/video.mp4',
				'video_play_on_mobile' => 'yes',
				'title' => 'External Video Background',
				'subtitle' => '<p>Experience <strong>smooth video playbook</strong> with our external video support.</p><p>Perfect for showcasing your content with <em>reliable performance</em> and fast loading times.</p>',
				'show_button' => 'yes',
				'button_text' => 'Learn More',
				'button_link' => [
					'url' => '#',
					'is_external' => false,
					'nofollow' => false,
				],
			],
			// Slide 2: Vimeo video background with video lightbox
			[
				'_id' => 'slide_2',
				'background_type' => 'video',
				'video_source' => 'external',
				'video_link' => 'https://vimeo.com/729485552',
				'video_play_on_mobile' => '',
				'title' => 'Vimeo Video Integration',
				'subtitle' => '<p>Seamlessly integrate Vimeo videos as background elements. Enjoy professional video hosting with advanced customization options</p>',
				'show_video_lightbox' => 'yes',
				'lightbox_video_url' => 'https://www.youtube.com/watch?v=a3ICNMQW7Ok',
				'lightbox_text' => 'Play',
			],
			// Slide 3: Image background with clickable title link
			[
				'_id' => 'slide_3',
				'background_type' => 'image',
				'image' => [
					'url' => 'https://demo.wpzoom.com/inspiro/files/2021/09/alexander-popov-vCbKwN2IXT4-unsplash.jpg',
				],
				'title' => 'Upload Your Own Videos',
				'subtitle' => '<p>Easily upload and use your own video files as stunning backgrounds.</p><p>Perfect for <a href="#custom-content">custom content</a> and branding.</p>',
				'link' => [
					'url' => 'https://wpzoom.com/plugins/',
					'is_external' => true,
					'nofollow' => false,
				],
			],
		];

		$this->add_control(
			'slides',
			[
				'show_label' => false,
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '<# print(title || "' . esc_js( __( 'Slider Item', 'wpzoom-elementor-addons' ) ) . '"); #>',
				'default' => $sample_slides,
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

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'thumbnail',
				'default' => 'full',
				'separator' => 'before',
				'exclude' => [
					'custom'
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
				'default' => '',
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


		$this->add_control(
			'content_order',
			[
				'label' => esc_html__( 'Content Order', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before'
			]
		);

		$this->add_control(
			'content_order_description',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'Choose the order of content elements in all slides. This affects title, subtitle, and actions (button/lightbox) across all slides.', 'wpzoom-elementor-addons' ),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->add_control(
			'content_order_preset',
			[
				'label' => esc_html__( 'Content Order', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'title-subtitle-actions' => esc_html__( '1. Title → Subtitle → Actions', 'wpzoom-elementor-addons' ),
					'subtitle-title-actions' => esc_html__( '2. Subtitle → Title → Actions', 'wpzoom-elementor-addons' ),
					'actions-title-subtitle' => esc_html__( '3. Actions → Title → Subtitle', 'wpzoom-elementor-addons' ),
					'title-actions-subtitle' => esc_html__( '4. Title → Actions → Subtitle', 'wpzoom-elementor-addons' ),
					'subtitle-actions-title' => esc_html__( '5. Subtitle → Actions → Title', 'wpzoom-elementor-addons' ),
					'actions-subtitle-title' => esc_html__( '6. Actions → Subtitle → Title', 'wpzoom-elementor-addons' ),
				],
				'default' => 'title-subtitle-actions',
				'description' => esc_html__( 'Select the order for your slide content elements.', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'content_order_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__( '💡 Tip: "Actions" includes both buttons and video lightbox triggers when they are enabled in individual slides.', 'wpzoom-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

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
			'settings_video_controls',
			[
				'label' => esc_html__( 'Video Controls', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before'
			]
		);

		$this->add_control(
			'show_video_controls',
			[
				'label' => esc_html__( 'Show Video Background Controls', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => esc_html__( 'Display play/pause and mute/unmute controls for video backgrounds in the bottom right corner.', 'wpzoom-elementor-addons' ),
				'frontend_available' => true,
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
				'label' => esc_html__( 'Slider Height', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'auto_height',
			[
				'label' => esc_html__( 'Custom Height', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no'
			]
		);

		$this->add_responsive_control(
			'auto_height_max',
			[
				'label' => esc_html__( 'Automatic Height Maximum', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
                    'vh' => [
                        'min' => 1,
                        'max' => 100,
                    ]
				],
				'default' => [
					'unit' => 'vh',
					'size' => 100
				],
				'desktop_default' => [
					'unit' => 'vh',
					'size' => 100
				],
				'tablet_default' => [
					'unit' => 'vh',
					'size' => 100
				],
				'mobile_default' => [
					'unit' => 'vh',
					'size' => 100
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-video-slider-wrapper' => 'height: {{SIZE}}{{UNIT}};'
				],
				'condition' => [
					'auto_height' => 'yes'
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_item',
			[
				'label' => esc_html__( 'Slider Items', 'wpzoom-elementor-addons' ),
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
					'{{WRAPPER}} .swiper-slide' => 'padding-right: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .wpz-slide-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
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
                'default' => [
                    'unit' => '%',
                    'size' => 70,
                ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_width',
			[
				'label' => esc_html__( 'Content Width', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range' => [
					'%' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
					'px' => [
						'min' => 100,
						'max' => 1200,
						'step' => 10,
					],
					'vw' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 70,
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_horizontal_position',
			[
				'label' => esc_html__( 'Horizontal Position', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-item' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_vertical_position',
			[
				'label' => esc_html__( 'Vertical Position', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Top', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Middle', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Bottom', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-item' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_text_align',
			[
				'label' => esc_html__( 'Text Align', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
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
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_elements_spacing',
			[
				'label' => esc_html__( 'Elements Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-content > *' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpz-slide-content > *:last-child' => 'margin-bottom: 0;',
				],
				'description' => esc_html__( 'Space between content elements (title, subtitle, actions).', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'selector' => '{{WRAPPER}} .wpz-slide-content',
				'exclude' => [
					 'image'
				]
			]
		);

        $this->end_controls_section();

        $this->start_controls_section(
            '_section_title_item',
            [
                'label' => esc_html__( 'Slide Title', 'wpzoom-elementor-addons' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
                'default' => '#fff',
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_link_hover_color',
			[
				'label' => esc_html__( 'Link Hover Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-title-link:hover .wpz-slide-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-slide-title',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
				'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_family' => ['default' => 'Onest'],
                    'font_weight' => ['default' => 500],
					'font_size' => [
						'default' => [
							'unit' => 'px',
							'size' => 50,
						],
						'tablet_default' => [
							'unit' => 'px',
							'size' => 36,
						],
						'mobile_default' => [
							'unit' => 'px',
							'size' => 26,
						],
					],
				],
			]
		);

        $this->end_controls_section();

        $this->start_controls_section(
            '_section_text_item',
            [
                'label' => esc_html__( 'Slide Text', 'wpzoom-elementor-addons' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

		$this->add_responsive_control(
			'subtitle_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-subtitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-slide-subtitle',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 18,
                        ],
                        'tablet_default' => [
                            'unit' => 'px',
                            'size' => 16,
                        ],
                        'mobile_default' => [
                            'unit' => 'px',
                            'size' => 16,
                        ],
                    ],
                ],
			]
		);

		$this->end_controls_section();

        $this->start_controls_section(
            '_section_style_button',
            [
                'label' => esc_html__( 'Button', 'wpzoom-elementor-addons' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'button_align',
            [
                'label' => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button-wrapper' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
                'selector' => '{{WRAPPER}} .wpz-slide-button',
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_margin',
            [
                'label' => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .wpz-slide-button',
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( '_tabs_button' );

        $this->start_controls_tab(
            '_tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            '_tab_button_hover',
            [
                'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->add_control(
            'button_hover_color',
            [
                'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_bg_color',
            [
                'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'button_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            '_section_style_play_icon',
            [
                'label' => esc_html__( 'Play Icon', 'wpzoom-elementor-addons' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'play_icon_align',
            [
                'label' => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-wrapper' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'play_icon_size',
            [
                'label' => esc_html__( 'Icon Size', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.5,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 24,
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'play_icon_box_size',
            [
                'label' => esc_html__( 'Icon Circle Size', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 30,
                        'max' => 150,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 70,
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-lightbox-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'play_icon_margin',
            [
                'label' => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'play_icon_border',
                'selector' => '{{WRAPPER}} .wpz-lightbox-icon',
            ]
        );

        $this->add_responsive_control(
            'play_icon_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-lightbox-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( '_tabs_play_icon' );

        $this->start_controls_tab(
            '_tab_play_icon_normal',
            [
                'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->add_control(
            'play_icon_color',
            [
                'label' => esc_html__( 'Icon Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'play_icon_bg_color',
            [
                'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpz-lightbox-icon' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            '_tab_play_icon_hover',
            [
                'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->add_control(
            'play_icon_hover_color',
            [
                'label' => esc_html__( 'Icon Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'play_icon_hover_bg_color',
            [
                'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger:hover .wpz-lightbox-icon' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'play_icon_hover_border_color',
            [
                'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'play_icon_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger:hover .wpz-lightbox-icon' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'play_icon_hover_transform',
            [
                'label' => esc_html__( 'Hover Transform', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'none' => esc_html__( 'None', 'wpzoom-elementor-addons' ),
                    'scale(1.1)' => esc_html__( 'Scale Up', 'wpzoom-elementor-addons' ),
                    'scale(0.9)' => esc_html__( 'Scale Down', 'wpzoom-elementor-addons' ),
                    'translateY(-5px)' => esc_html__( 'Move Up', 'wpzoom-elementor-addons' ),
                    'rotate(15deg)' => esc_html__( 'Rotate', 'wpzoom-elementor-addons' ),
                ],
                'default' => 'scale(1.1)',
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger:hover' => 'transform: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'lightbox_text_heading',
            [
                'label' => esc_html__( 'Lightbox Text', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'lightbox_text_typography',
                'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
                'selector' => '{{WRAPPER}} .wpz-lightbox-text',
            ]
        );

        $this->add_responsive_control(
            'lightbox_text_spacing',
            [
                'label' => esc_html__( 'Text Spacing', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpz-slide-lightbox-trigger' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'description' => esc_html__( 'Space between icon and text.', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
			'_section_style_arrow',
			[
				'label' => esc_html__( 'Navigation Arrows', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_responsive_control(
			'arrow_size',
			[
				'label' => esc_html__( 'Size', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default' => [
					'unit' => 'px',
					'size' => 70,
				],
				'selectors' => [
					'{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: calc({{SIZE}}{{UNIT}} * 0.4);',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'arrow_border',
				'selector' => '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next',
			]
		);

		$this->add_responsive_control(
			'arrow_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
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
					'{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .swiper-button-prev:hover, {{WRAPPER}} .swiper-button-next:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_hover_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-button-prev:hover, {{WRAPPER}} .swiper-button-next:hover' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .swiper-button-prev:hover, {{WRAPPER}} .swiper-button-next:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_dots',
			[
				'label' => esc_html__( 'Navigation Dots', 'wpzoom-elementor-addons' ),
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
					'{{WRAPPER}} .swiper-pagination' => 'bottom: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .swiper-pagination .swiper-pagination-bullet' => 'margin-right: calc({{SIZE}}{{UNIT}} / 2); margin-left: calc({{SIZE}}{{UNIT}} / 2);',
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
					'{{WRAPPER}} .swiper-pagination' => 'text-align: {{VALUE}}'
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
					'{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dots_nav_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .swiper-pagination-bullet:hover' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .swiper-pagination-bullet-active' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dots_nav_active_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

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
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .wpz-slide-item::before' => 'display: block;',
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
					'{{WRAPPER}} .wpz-slide-item::before' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'background_overlay_enable' => 'yes',
				],
			]
		);

		$this->add_control(
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
					'{{WRAPPER}} .wpz-slide-item::before' => 'opacity: {{SIZE}};',
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
					'{{WRAPPER}} .wpz-slide-item::before' => 'mix-blend-mode: {{VALUE}};',
				],
				'condition' => [
					'background_overlay_enable' => 'yes',
				],
			]
		);

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
	 * Detect video type from URL.
	 *
	 * @param string $url The video URL to analyze.
	 * @since 1.0.0
	 * @access private
	 * @return string|false The video type (youtube, vimeo, hosted) or false if not detected.
	 */
	private function detect_video_type( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		// YouTube detection
		if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url ) ) {
			return 'youtube';
		}

		// Vimeo detection
		if ( preg_match( '/(?:vimeo\.com\/)([0-9]+)/', $url ) ) {
			return 'vimeo';
		}

		// Check if it's a direct video file
		$video_extensions = [ 'mp4', 'webm', 'ogg', 'mov', 'avi' ];
		$url_parts = parse_url( $url );
		if ( isset( $url_parts['path'] ) ) {
			$path_info = pathinfo( $url_parts['path'] );
			if ( isset( $path_info['extension'] ) && in_array( strtolower( $path_info['extension'] ), $video_extensions ) ) {
				return 'hosted';
			}
		}

		return false;
	}

	/**
	 * Get video ID from URL.
	 *
	 * @param string $url The video URL.
	 * @param string $type The video type.
	 * @since 1.0.0
	 * @access private
	 * @return string|false The video ID or false if not found.
	 */
	private function get_video_id( $url, $type ) {
		switch ( $type ) {
			case 'youtube':
				preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches );
				return isset( $matches[1] ) ? $matches[1] : false;

			case 'vimeo':
				preg_match( '/(?:vimeo\.com\/)([0-9]+)/', $url, $matches );
				return isset( $matches[1] ) ? $matches[1] : false;

			default:
				return false;
		}
	}

	/**
	 * Build video embed URL with parameters.
	 *
	 * @param array $slide The slide data.
	 * @param string $video_type The video type.
	 * @param string $video_id The video ID.
	 * @since 1.0.0
	 * @access private
	 * @return string The embed URL.
	 */
	private function build_video_embed_url( $slide, $video_type, $video_id ) {
		$params = [];

		switch ( $video_type ) {
			case 'youtube':
				$base_url = $slide['video_privacy_mode'] ? 'https://www.youtube-nocookie.com/embed/' : 'https://www.youtube.com/embed/';
				$embed_url = $base_url . $video_id;

				$params['autoplay'] = '1';
				$params['mute'] = '1';
				$params['controls'] = '0';
				$params['rel'] = '0';
				$params['modestbranding'] = '1';
				$params['playsinline'] = '1';
				$params['enablejsapi'] = '1'; // Enable JavaScript API for controls

				if ( ! empty( $slide['video_start_time'] ) ) {
					$params['start'] = $slide['video_start_time'];
				}

				if ( ! empty( $slide['video_end_time'] ) ) {
					$params['end'] = $slide['video_end_time'];
				}

				if ( empty( $slide['video_play_once'] ) ) {
					$params['loop'] = '1';
					$params['playlist'] = $video_id;
				}

				break;

			case 'vimeo':
				$embed_url = 'https://player.vimeo.com/video/' . $video_id;

				$params['autoplay'] = '1';
				$params['muted'] = '1';
				$params['controls'] = '0';
				$params['title'] = '0';
				$params['byline'] = '0';
				$params['portrait'] = '0';
				$params['playsinline'] = '1';
				$params['background'] = '1';
				$params['api'] = '1'; // Enable JavaScript API for controls
				$params['player_id'] = 'vimeo_' . $video_id; // Unique player ID for API

				if ( ! empty( $slide['video_start_time'] ) ) {
					$params['t'] = $slide['video_start_time'] . 's';
				}

				if ( empty( $slide['video_play_once'] ) ) {
					$params['loop'] = '1';
				}

				break;

			default:
				return '';
		}

		if ( ! empty( $params ) ) {
			$embed_url .= '?' . http_build_query( $params );
		}

		return $embed_url;
	}

	/**
	 * Render video background.
	 *
	 * @param array $slide The slide data.
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function render_video_background( $slide ) {
		// Determine video source and URL
		$video_source = isset( $slide['video_source'] ) ? $slide['video_source'] : 'external';
		$video_url = '';

		if ( 'hosted' === $video_source && ! empty( $slide['video_file']['url'] ) ) {
			// Self-hosted video
			$video_url = $slide['video_file']['url'];
			$video_type = 'hosted';
		} elseif ( 'external' === $video_source && ! empty( $slide['video_link'] ) ) {
			// External video (YouTube/Vimeo)
			$video_url = $slide['video_link'];
			$video_type = $this->detect_video_type( $video_url );
		} else {
			// Fallback to old structure for backward compatibility
			if ( ! empty( $slide['video_link'] ) ) {
				$video_url = $slide['video_link'];
				$video_type = $this->detect_video_type( $video_url );
			} else {
				return;
			}
		}

		if ( empty( $video_url ) || ! $video_type ) {
			return;
		}

		$video_attrs = [
			'class' => 'wpz-video-bg',
			'data-video-type' => $video_type,
			'data-video-url' => esc_url( $video_url ),
		];

		if ( ! empty( $slide['video_play_on_mobile'] ) ) {
			$video_attrs['data-play-on-mobile'] = 'true';
		}

		if ( ! empty( $slide['video_start_time'] ) ) {
			$video_attrs['data-start-time'] = intval( $slide['video_start_time'] );
		}

		if ( ! empty( $slide['video_end_time'] ) ) {
			$video_attrs['data-end-time'] = intval( $slide['video_end_time'] );
		}

		if ( ! empty( $slide['video_play_once'] ) ) {
			$video_attrs['data-play-once'] = 'true';
		}

		if ( ! empty( $slide['video_privacy_mode'] ) ) {
			$video_attrs['data-privacy-mode'] = 'true';
		}

		// Add video control data attributes for external videos
		if ( 'youtube' === $video_type || 'vimeo' === $video_type ) {
			$video_id = $this->get_video_id( $video_url, $video_type );
			if ( $video_id ) {
				$video_attrs['data-video-id'] = $video_id;
			}
		}

		?>
		<div <?php echo Utils::render_html_attributes( $video_attrs ); ?>>
			<?php if ( 'hosted' === $video_type ) : ?>
				<video
					autoplay
					muted
					playsinline
					<?php echo empty( $slide['video_play_once'] ) ? 'loop' : ''; ?>
					<?php echo empty( $slide['video_play_on_mobile'] ) ? 'data-mobile-disabled="true"' : ''; ?>
				>
					<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
				</video>
			<?php else :
				$video_id = $this->get_video_id( $video_url, $video_type );
				if ( $video_id ) :
					$embed_url = $this->build_video_embed_url( $slide, $video_type, $video_id );
			?>
				<iframe
					src="<?php echo esc_url( $embed_url ); ?>"
					frameborder="0"
					allow="autoplay; fullscreen; picture-in-picture"
					allowfullscreen
					class="fitvidsignore">
				</iframe>
			<?php
				endif;
			endif; ?>
		</div>

		<?php
		// Add video background controls if enabled
		$settings = $this->get_settings_for_display();
		if ( ! empty( $settings['show_video_controls'] ) && 'yes' === $settings['show_video_controls'] ) :
		?>
		<div class="wpz-background-video-buttons-wrapper">
			<a class="wpz-button-video-background-play display-none" href="#" aria-label="<?php esc_attr_e( 'Play', 'wpzoom-elementor-addons' ); ?>">
                <svg height="32" version="1.1" viewBox="0 0 512 512" width="32" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M405.2,232.9L126.8,67.2c-3.4-2-6.9-3.2-10.9-3.2c-10.9,0-19.8,9-19.8,20H96v344h0.1c0,11,8.9,20,19.8,20  c4.1,0,7.5-1.4,11.2-3.4l278.1-165.5c6.6-5.5,10.8-13.8,10.8-23.1C416,246.7,411.8,238.5,405.2,232.9z" fill="#fff"></path></svg>
				<span class="screen-reader-text"><?php esc_html_e( 'Play', 'wpzoom-elementor-addons' ); ?></span>
			</a>
			<a class="wpz-button-video-background-pause display-none" href="#" aria-label="<?php esc_attr_e( 'Pause', 'wpzoom-elementor-addons' ); ?>">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 4H10V20H6V4ZM14 4H18V20H14V4Z" fill="currentColor"/>
                </svg>
				<span class="screen-reader-text"><?php esc_html_e( 'Pause', 'wpzoom-elementor-addons' ); ?></span>
			</a>
			<a class="wpz-button-sound-background-unmute display-none" href="#" aria-label="<?php esc_attr_e( 'Unmute', 'wpzoom-elementor-addons' ); ?>">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32"  viewBox="0 0 32 32"><path d="M13.333 10.108v13.119l-4.5-3.6c-0.227-0.183-0.517-0.293-0.833-0.293h-4v-5.333h4c0.291 0.001 0.585-0.095 0.833-0.292zM13.833 6.292l-6.301 5.041h-4.865c-0.736 0-1.333 0.597-1.333 1.333v8c0 0.736 0.597 1.333 1.333 1.333h4.865l6.301 5.041c0.575 0.46 1.415 0.367 1.875-0.208 0.197-0.247 0.293-0.543 0.292-0.833v-18.667c0-0.736-0.597-1.333-1.333-1.333-0.316 0-0.607 0.111-0.833 0.292zM21.724 13.609l3.057 3.057-3.057 3.057c-0.521 0.521-0.521 1.365 0 1.885s1.365 0.521 1.885 0l3.057-3.057 3.057 3.057c0.521 0.521 1.365 0.521 1.885 0s0.521-1.365 0-1.885l-3.057-3.057 3.057-3.057c0.521-0.521 0.521-1.365 0-1.885s-1.365-0.521-1.885 0l-3.057 3.057-3.057-3.057c-0.521-0.521-1.365-0.521-1.885 0s-0.521 1.365 0 1.885z" fill="currentColor"></path></svg>
				<span class="screen-reader-text"><?php esc_html_e( 'Unmute', 'wpzoom-elementor-addons' ); ?></span>
			</a>
			<a class="wpz-button-sound-background-mute display-none" href="#" aria-label="<?php esc_attr_e( 'Mute', 'wpzoom-elementor-addons' ); ?>">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path d="M13.333 10.108v13.119l-4.5-3.6c-0.227-0.183-0.517-0.293-0.833-0.293h-4v-5.333h4c0.291 0.001 0.585-0.095 0.833-0.292zM13.833 6.292l-6.301 5.041h-4.865c-0.736 0-1.333 0.597-1.333 1.333v8c0 0.736 0.597 1.333 1.333 1.333h4.865l6.301 5.041c0.575 0.46 1.415 0.367 1.875-0.208 0.197-0.247 0.293-0.543 0.292-0.833v-18.667c0-0.736-0.597-1.333-1.333-1.333-0.316 0-0.607 0.111-0.833 0.292zM24.484 8.183c2.343 2.344 3.513 5.412 3.513 8.485 0 3.072-1.171 6.14-3.513 8.483-0.52 0.521-0.52 1.365 0 1.885s1.365 0.52 1.885 0c2.863-2.863 4.293-6.617 4.295-10.368 0-3.751-1.432-7.507-4.295-10.371-0.52-0.521-1.365-0.521-1.885 0s-0.521 1.365 0 1.885zM19.777 12.889c1.041 1.041 1.561 2.404 1.561 3.771s-0.52 2.729-1.561 3.771c-0.52 0.521-0.52 1.365 0 1.885s1.365 0.52 1.885 0c1.561-1.561 2.343-3.611 2.343-5.656s-0.781-4.095-2.343-5.656c-0.52-0.521-1.365-0.521-1.885 0s-0.521 1.365 0 1.885z" fill="currentColor"></path></svg>
				<span class="screen-reader-text"><?php esc_html_e( 'Mute', 'wpzoom-elementor-addons' ); ?></span>
			</a>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render slide content element based on type.
	 *
	 * @param string $element_type The type of element to render.
	 * @param array $slide The slide data.
	 * @param bool $has_slide_link Whether the slide has a link.
	 * @param bool $has_button Whether the slide has a button.
	 * @param bool $has_lightbox Whether the slide has a lightbox.
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function render_slide_content_element( $element_type, $slide, $has_slide_link, $has_button, $has_lightbox ) {
		switch ( $element_type ) {
			case 'title':
				if ( $slide[ 'title' ] ) {
					if ( $has_slide_link ) {
						?>
						<h2 class="wpz-slide-title">
							<a href="<?php echo esc_url( $slide['link']['url'] ); ?>"
							   class="wpz-slide-title-link"
							   <?php echo ( $slide['link']['is_external'] ?? false ) ? 'target="_blank"' : ''; ?>
							   <?php echo ( $slide['link']['nofollow'] ?? false ) ? 'rel="nofollow"' : ''; ?>>
								<?php echo WPZOOM_Elementor_Widgets::custom_kses( $slide[ 'title' ] ); ?>
							</a>
						</h2>
						<?php
					} else {
						?>
						<h2 class="wpz-slide-title"><?php echo WPZOOM_Elementor_Widgets::custom_kses( $slide[ 'title' ] ); ?></h2>
						<?php
					}
				}
				break;

			case 'subtitle':
				if ( $slide[ 'subtitle' ] ) {
					?>
					<div class="wpz-slide-subtitle"><?php echo wp_kses_post( $slide[ 'subtitle' ] ); ?></div>
					<?php
				}
				break;

			case 'actions':
				if ( $has_button || $has_lightbox ) {
					?>
					<div class="wpz-slide-actions">
						<?php if ( $has_button && !empty( $slide['button_text'] ) ) : ?>
							<div class="wpz-slide-button-wrapper">
								<a href="<?php echo esc_url( $slide['button_link']['url'] ?? '#' ); ?>"
								   class="wpz-slide-button elementor-button"
								   <?php echo ( $slide['button_link']['is_external'] ?? false ) ? 'target="_blank"' : ''; ?>
								   <?php echo ( $slide['button_link']['nofollow'] ?? false ) ? 'rel="nofollow"' : ''; ?>>
									<?php echo esc_html( $slide['button_text'] ); ?>
								</a>
							</div>
						<?php endif; ?>

						<?php if ( $has_lightbox && !empty( $slide['lightbox_video_url'] ) ) : ?>
							<div class="wpz-slide-lightbox-wrapper">
								<a href="<?php echo esc_url( $slide['lightbox_video_url'] ); ?>"
								   class="wpz-slide-lightbox-trigger"
								   title="<?php esc_attr_e( 'Play Video', 'wpzoom-elementor-addons' ); ?>">
									<span class="wpz-lightbox-icon">
										<svg height="32px" version="1.1" viewBox="0 0 512 512" width="512px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M405.2,232.9L126.8,67.2c-3.4-2-6.9-3.2-10.9-3.2c-10.9,0-19.8,9-19.8,20H96v344h0.1c0,11,8.9,20,19.8,20  c4.1,0,7.5-1.4,11.2-3.4l278.1-165.5c6.6-5.5,10.8-13.8,10.8-23.1C416,246.7,411.8,238.5,405.2,232.9z" fill="#fff"/></svg>
									</span>
									<?php if ( !empty( $slide['lightbox_text'] ) ) : ?>
										<span class="wpz-lightbox-text"><?php echo esc_html( $slide['lightbox_text'] ); ?></span>
									<?php endif; ?>
									<span class="elementor-screen-only"><?php esc_html_e( 'Play Video', 'wpzoom-elementor-addons' ); ?></span>
								</a>
							</div>
						<?php endif; ?>
					</div>
					<?php
				}
				break;
		}
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
		if ( ! $this->has_premium_access() ) {
			$this->render_premium_upgrade_notice();
			return;
		}
		
		$settings = $this->get_settings_for_display();
		$slides = [];

		if ( 'posts' == $settings[ 'slides_source' ] ) {
			$args = $this->get_query_args( $settings );
			$query = new \WP_Query( $args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();

					$slides[] = [
						'_id' => get_the_ID(),
						'title' => get_the_title(),
						'subtitle' => get_the_excerpt(),
						'background_type' => 'image',
						'image' => [ 'id' => get_post_thumbnail_id(), 'url' => false ],
						'link' => [ 'url' => get_permalink() ]
					];
				}

				wp_reset_postdata();
			}
		} else {
			$slides = $settings[ 'slides' ];
		}

		if ( empty( $slides ) ) {
			return;
		}

		$direction = is_rtl() ? 'rtl' : 'ltr';

		$this->add_render_attribute( [
			'wrapper' => [
				'class' => [ 'wpz-video-slider-wrapper', 'swiper' ],
				'dir' => $direction,
			],
		] );

		?><div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="swiper-wrapper">

				<?php foreach ( $slides as $slide ) :

					// Set default background type if not set
					$background_type = isset( $slide['background_type'] ) ? $slide['background_type'] : 'image';

					$item_tag = 'div';
					$id = 'wpz-slide-item-' . $slide ['_id' ];

					$this->add_render_attribute( $id, 'class', 'wpz-slide-item swiper-slide elementor-repeater-item-' . esc_attr( $slide['_id'] ) );
					$this->add_render_attribute( $id, 'class', 'wpz-slide-item--' . $background_type );

					// Only make the slide a link if no button or lightbox is shown and a link is provided
					$has_button = !empty($slide['show_button']) && $slide['show_button'] === 'yes';
					$has_lightbox = !empty($slide['show_video_lightbox']) && $slide['show_video_lightbox'] === 'yes';
					$has_slide_link = isset( $slide[ 'link' ] ) && ! empty( $slide[ 'link' ][ 'url' ] );

					// Make entire slide clickable only if there's a link but no title, button, or lightbox
					if ( $has_slide_link && !$has_button && !$has_lightbox && empty( $slide[ 'title' ] ) ) {
						$item_tag = 'a';
						$this->add_link_attributes( $id, $slide[ 'link' ] );
					}
					?>

					<<?php echo $item_tag; // WPCS: XSS OK. ?> <?php $this->print_render_attribute_string( $id ); ?>>

						<?php if ( 'video' === $background_type ) : ?>
							<!-- Video Background -->
							<?php $this->render_video_background( $slide ); ?>

							<!-- Fallback Image for Video (hidden by default, shown only when video fails) -->
							<?php if ( ! empty( $slide['background_fallback']['url'] ) ) : ?>
								<img class="wpz-video-fallback" src="<?php echo esc_url( $slide['background_fallback']['url'] ); ?>" alt="<?php echo esc_attr( $slide[ 'title' ] ); ?>">
							<?php endif; ?>

						<?php else : ?>
							<!-- Image Background -->
							<?php
							$image = wp_get_attachment_image_url( $slide[ 'image' ][ 'id' ], $settings[ 'thumbnail_size' ] );
							if ( ! $image ) {
								$image = $slide[ 'image' ][ 'url' ];
							}
							?>
							<?php if ( $image ) : ?>
								<div class="wpz-slide-bg" style="background-image: url(<?php echo esc_url( $image ); ?>);"></div>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( $slide[ 'title' ] || $slide[ 'subtitle' ] || $has_button || $has_lightbox ) : ?>
							<div class="wpz-slide-inner">
								<div class="wpz-slide-content">
									<?php
									// Use simple preset ordering
									$content_order_preset = $settings['content_order_preset'] ?? 'title-subtitle-actions';
									
									// Map preset to element order
									$preset_map = [
										'title-subtitle-actions' => ['title', 'subtitle', 'actions'],
										'subtitle-title-actions' => ['subtitle', 'title', 'actions'],
										'actions-title-subtitle' => ['actions', 'title', 'subtitle'],
										'title-actions-subtitle' => ['title', 'actions', 'subtitle'],
										'subtitle-actions-title' => ['subtitle', 'actions', 'title'],
										'actions-subtitle-title' => ['actions', 'subtitle', 'title'],
									];
									
									$content_order = $preset_map[$content_order_preset] ?? ['title', 'subtitle', 'actions'];

									// Render content elements in the specified order
									foreach ( $content_order as $element_type ) {
										$this->render_slide_content_element(
											$element_type,
											$slide,
											$has_slide_link,
											$has_button,
											$has_lightbox
										);
									}
									?>
								</div>
							</div>
						<?php endif; ?>

					</<?php echo $item_tag; // WPCS: XSS OK. ?>>

				<?php endforeach; ?>

			</div>

			<?php
			$show_arrows = in_array( $settings['navigation'], [ 'arrow', 'both' ] );
			$show_dots = in_array( $settings['navigation'], [ 'dots', 'both' ] );
			?>

			<?php if ( count( $slides ) > 1 ) : ?>
				<?php if ( $show_arrows ) : ?>
					<div class="swiper-button-prev">
						<svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><g data-name="1" id="_1"><path d="M353,450a15,15,0,0,1-10.61-4.39L157.5,260.71a15,15,0,0,1,0-21.21L342.39,54.6a15,15,0,1,1,21.22,21.21L189.32,250.1,363.61,424.39A15,15,0,0,1,353,450Z"/></g></svg>
					</div>
					<div class="swiper-button-next">
						<svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><g data-name="1" id="_1"><path d="M202.1,450a15,15,0,0,1-10.6-25.61L365.79,250.1,191.5,75.81A15,15,0,0,1,212.71,54.6l184.9,184.9a15,15,0,0,1,0,21.21l-184.9,184.9A15,15,0,0,1,202.1,450Z"/></g></svg>
					</div>
				<?php endif; ?>

				<?php if ( $show_dots ) : ?>
					<div class="swiper-pagination"></div>
				<?php endif; ?>
			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Render Premium Upgrade Notice.
	 *
	 * Shows upgrade notice on frontend for non-premium users.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function render_premium_upgrade_notice() {
		if ( Plugin::$instance->editor->is_edit_mode() ) {
			// Show notice in editor
			?>
			<div style="text-align: center; padding: 40px 20px; background: #0f5099; border-radius: 12px; margin: 20px 0; color: white; position: relative; overflow: hidden;">
				<div style="position: absolute; top: -20px; right: -20px; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
				<div style="position: absolute; bottom: -30px; left: -30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
				<div style="position: relative; z-index: 2;">
					<div style="font-size: 64px; margin-bottom: 20px;">🎬</div>
					<h3 style="margin: 0 0 15px 0; color: white; font-size: 28px; font-weight: 600;"><?php esc_html_e( 'Video Slideshow Widget (Pro)', 'wpzoom-elementor-addons' ); ?></h3>
					<p style="margin: 0 0 30px 0; color: rgba(255,255,255,0.9); line-height: 1.6; font-size: 16px; max-width: 500px; margin-left: auto; margin-right: auto;">
						<?php esc_html_e( 'Create stunning video slideshows with background videos and custom overlays. This premium widget requires the WPZOOM Elementor Addons Pro plugin to be installed and activated, or you can use it with any premium WPZOOM theme.', 'wpzoom-elementor-addons' ); ?>
					</p>
				</div>
			</div>
			<?php
		} else {
			// Show minimal notice on frontend only to administrators
			if ( current_user_can( 'manage_options' ) ) {
				?>
				<div style="text-align: center; padding: 30px 20px; background: #0f5099; border-radius: 8px; color: white; margin: 20px 0;">
					<div style="font-size: 24px; margin-bottom: 10px;">🎬</div>
					<h4 style="margin: 0 0 8px 0; color: white; font-size: 18px;"><?php esc_html_e( 'Video Slideshow Widget', 'wpzoom-elementor-addons' ); ?></h4>
					<p style="margin: 0 0 15px 0; color: rgba(255,255,255,0.9); font-size: 14px;">
						<?php esc_html_e( 'This premium widget requires the WPZOOM Elementor Addons Pro plugin or a premium WPZOOM theme', 'wpzoom-elementor-addons' ); ?>
					</p>
					<a href="<?php echo esc_url( 'https://www.wpzoom.com/plugins/wpzoom-elementor-addons/' ); ?>" target="_blank" style="display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px; font-size: 14px; margin-bottom: 10px;">
						<?php esc_html_e( 'Get Pro Plugin', 'wpzoom-elementor-addons' ); ?>
					</a>
					<p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">
						<?php esc_html_e( '(Only visible to administrators)', 'wpzoom-elementor-addons' ); ?>
					</p>
				</div>
				<?php
			}
			// For non-admin users, show nothing on frontend
		}
	}

	/**
	 * Check if user has access to premium features.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return bool True if user has premium access, false otherwise.
	 */
	private function has_premium_access() {
		// Check if Pro plugin is active
		if ( class_exists( 'WPZOOM_Elementor_Addons_Pro' ) ) {
			return true;
		}
		
		// Fallback: Check if WPZOOM theme is active (for backward compatibility)
		if ( class_exists( 'WPZOOM' ) ) {
			return true;
		}
		
		return false;
	}

	/**
	 * Get widget keywords.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'video', 'slider', 'slideshow', 'carousel', 'media', 'background', 'wpzoom', 'pro' ];
	}

	/**
	 * Show in panel.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return bool Whether to show the widget in panel.
	 */
	public function show_in_panel() {
		return true; // Always show in panel, but with restrictions
	}
}

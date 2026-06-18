<?php
namespace WPZOOMElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Scheme_Color;
use Elementor\Utils;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - Posts Grid Widget.
 *
 * Elementor widget that inserts a customizable grid of posts.
 *
 * @since 1.0.0
 */
class Posts_Grid extends Widget_Base {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-posts-grid', plugins_url( 'frontend.css', __FILE__ ), [], WPZOOM_EL_ADDONS_VER );
		wp_register_script( 'wpzoom-elementor-addons-js-frontend-posts-grid', plugins_url( 'frontend.js', __FILE__ ), array( 'jquery' ), WPZOOM_EL_ADDONS_VER, true );
		wp_localize_script( 
			'wpzoom-elementor-addons-js-frontend-posts-grid', 
			'WPZoomElementorAddons', 
			array(
				'ajaxURL' => admin_url( 'admin-ajax.php' ),
				'loadingString' => esc_html__( 'Loading...', 'wpzoom-elementor-addons' )
			) 
		);

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
		return 'wpzoom-elementor-addons-posts-grid';
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
		return esc_html__( 'Posts Grid', 'wpzoom-elementor-addons' );
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
		return 'eicon-posts-grid';
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
			'wpzoom-elementor-addons-css-frontend-posts-grid'
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
			'wpzoom-elementor-addons-js-frontend-posts-grid'
		];
	}

	/**
	 * Get All Post Categories.
	 *
	 * Returns a list of all post categories.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Array of categories.
	 */
	private function wpz_get_all_post_categories( $post_type ) {
		$options = array();

		$taxonomy = 'category';

		if ( ! empty( $taxonomy ) ) {
			// Get categories for post type.
			$terms = get_terms(
				[
					'taxonomy'   => $taxonomy,
					'hide_empty' => false
				]
			);
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					if ( isset( $term ) ) {
						if ( isset( $term->slug ) && isset( $term->name ) ) {
							$options[ $term->slug ] = $term->name . ' (' . $term->count . ')';
						}
					}
				}
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
		$this->wpz_content_layout_options();
		$this->wpz_content_query_options();

		$this->wpz_style_layout_options();
		$this->wpz_style_box_options();
		$this->wpz_style_image_options();

		$this->wpz_style_title_options();
		$this->wpz_style_meta_options();
		$this->wpz_style_content_options();
		$this->wpz_style_readmore_options();
		$this->wpz_style_loadmore_options();
	}

	/**
	 * Content Layout Options.
	 *
	 * Registers the content layout controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	private function wpz_content_layout_options() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'wpzoom-elementor-addons' )
			]
		);

		$this->add_control(
			'grid_style',
			[
				'label' => esc_html__( 'Grid Style', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'1' => esc_html__( 'Layout 1', 'wpzoom-elementor-addons' ),
					'2' => esc_html__( 'Layout 2', 'wpzoom-elementor-addons' ),
					'3' => esc_html__( 'Layout 3', 'wpzoom-elementor-addons' ),
					'4' => esc_html__( 'Layout 4', 'wpzoom-elementor-addons' ),
					'5' => esc_html__( 'Layout 5', 'wpzoom-elementor-addons' ),
					'6' => esc_html__( 'Layout 6', 'wpzoom-elementor-addons' ),
					'7' => esc_html__( 'Layout 7 (List)', 'wpzoom-elementor-addons' ),
					'8' => esc_html__( 'Layout 8 (Overlay)', 'wpzoom-elementor-addons' )
				]
			]
		);
		
		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__( 'Columns', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4'
				],
				'prefix_class' => 'elementor-grid%s-',
				'frontend_available' => true,
				'selectors' => [
					'.elementor-msie {{WRAPPER}} .elementor-portfolio-item' => 'width: calc( 100% / {{SIZE}} )'
				],
				'condition' => [
					'grid_style!' => '7'
				]
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__( 'Posts Per Page', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 3
			]
		);

		$this->add_control(
			'show_image',
			[
				'label' => esc_html__( 'Image', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'default' => 'yes',
				'separator' => 'before'
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'post_thumbnail',
				'exclude' => [ 'custom' ],
				'default' => 'full',
				'prefix_class' => 'post-thumbnail-size-',
				'condition' => [
					'show_image' => 'yes'
				]
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'default' => 'yes',
				'separator' => 'before'
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => esc_html__( 'Title HTML Tag', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p'
				],
				'default'   => 'h3',
				'condition' => [
					'show_title' => 'yes'
				]
			]
		);

		$this->add_control(
			'meta_data',
			[
				'label' => esc_html__( 'Meta Data', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'type' => Controls_Manager::SELECT2,
				'default' => [ 'date', 'comments' ],
				'multiple' => true,
				'options' => apply_filters( 'wpzoom_elementor_addons_posts_grid_meta_fields', [
					'author'     => esc_html__( 'Author', 'wpzoom-elementor-addons' ),
					'author_pic' => esc_html__( 'Author Picture', 'wpzoom-elementor-addons' ),
					'date'       => esc_html__( 'Date', 'wpzoom-elementor-addons' ),
					'categories' => esc_html__( 'Categories', 'wpzoom-elementor-addons' ),
					'comments'   => esc_html__( 'Comments', 'wpzoom-elementor-addons' )
				] ),
				'separator' => 'before'
			]
		);

		$this->add_control(
			'meta_separator',
			[
				'label' => esc_html__( 'Separator Between', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '/',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post .post-grid-meta span + span:before' => 'content: "{{VALUE}}"'
				],
				'condition' => [
					'meta_data!' => []
				]
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'     => esc_html__( 'Excerpt', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'default'   => 'yes',
				'separator' => 'before'
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label' => esc_html__( 'Excerpt Length', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::NUMBER,
				/** This filter is documented in wp-includes/formatting.php */
				'default' => apply_filters( 'excerpt_length', 25 ),
				'condition' => [
					'show_excerpt' => 'yes'
				]
			]
		);

		$this->add_control(
			'show_read_more',
			[
				'label'     => esc_html__( 'Read More', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'default'   => 'yes',
				'separator' => 'before'
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label'     => esc_html__( 'Read More Text', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read More »', 'wpzoom-elementor-addons' ),
				'condition' => [
					'show_read_more' => 'yes'
				]
			]
		);

		$this->add_control(
			'content_align',
			[
				'label' => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-left'
					],
					'center' => [
						'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-center'
					],
					'right' => [
						'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-right'
					]
				],
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .post-grid-inner' => 'text-align: {{VALUE}};'
				],
				'separator' => 'before',
				'condition' => [
					'grid_style!' => '8'
				]
			]
		);

		// Overlay layout uses its own content wrapper and defaults to centered.
		$this->add_control(
			'content_align_overlay',
			[
				'label' => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-left'
					],
					'center' => [
						'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-center'
					],
					'right' => [
						'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-right'
					]
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .grid-overlay-layout .post-grid-overlay-content' => 'text-align: {{VALUE}};'
				],
				'separator' => 'before',
				'condition' => [
					'grid_style' => '8'
				]
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label' => esc_html__( 'Pagination', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'none'       => esc_html__( 'None', 'wpzoom-elementor-addons' ),
					'pagination' => esc_html__( 'Pagination', 'wpzoom-elementor-addons' ),
					'load_more'  => esc_html__( 'Load More Button', 'wpzoom-elementor-addons' )
				],
				'default'   => 'none',
				'separator' => 'before'
			]
		);

		$this->add_control(
			'load_more_text',
			[
				'label'       => esc_html__( 'Load More Text', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Load More', 'wpzoom-elementor-addons' ),
				'condition'   => [
					'pagination_type' => 'load_more'
				]
			]
		);

		$this->add_control(
			'pagination_align',
			[
				'label' => esc_html__( 'Pagination Alignment', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-left'
					],
					'center' => [
						'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-center'
					],
					'right' => [
						'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-right'
					]
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .wpzoom-posts-grid-pagination .pagination, {{WRAPPER}} .wpz-posts-grid-load-more' => 'text-align: {{VALUE}};'
				],
				'condition'   => [
					'pagination_type!' => 'none'
				]
			]
		);
		$this->add_responsive_control(
			'load_more_width',
			[
				'label' => esc_html__( 'Width', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'   => [
					'pagination_type' => 'load_more'
				]
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content Query Options.
	 *
	 * Registers the content query controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_content_query_options() {
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Query', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT
			]
		);

		// Post categories

		$this->start_controls_tabs( '_tabs_query_cats' );
		$this->start_controls_tab(
			'_tab_cats_include',
			[
				'label' => esc_html__( 'Include', 'wpzoom-elementor-addons' ),
			]
		);	
		$this->add_control(
			'post_categories',
			[
				'label'       => esc_html__( 'Categories', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->wpz_get_all_post_categories( 'post' )
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_cats_exclude',
			[
				'label' => esc_html__( 'Exclude', 'wpzoom-elementor-addons' ),
			]
		);
		$this->add_control(
			'ex_post_categories',
			[
				'label'       => esc_html__( 'Categories', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->wpz_get_all_post_categories( 'post' )
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'advanced',
			[
				'label' => esc_html__( 'Advanced', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING
			]
		);



		$this->add_control(
			'orderby',
			[
				'label'   => esc_html__( 'Order By', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post_date',
				'options' => [
					'post_date'  => esc_html__( 'Date', 'wpzoom-elementor-addons' ),
					'post_title' => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
					'rand'       => esc_html__( 'Random', 'wpzoom-elementor-addons' )
				]
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => esc_html__( 'Order', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc'  => esc_html__( 'ASC', 'wpzoom-elementor-addons' ),
					'desc' => esc_html__( 'DESC', 'wpzoom-elementor-addons' ),
				]
			]
		);

        $this->add_control(
            'offset',
            [
                'label'   => esc_html__( 'Number of posts to Offset', 'wpzoom-elementor-addons' ),
                'type'    => Controls_Manager::TEXT,
                'default' => 0,
            ]
        );

		$this->end_controls_section();
	}

	/**
	 * Style Layout Options.
	 *
	 * Registers the style layout controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_style_layout_options() {
		// Layout.
		$this->start_controls_section(
			'section_layout_style',
			[
				'label' => esc_html__( 'Layout', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE
			]
		);

		// Columns margin.
		$this->add_control(
			'grid_style_columns_margin',
			[
				'label'     => esc_html__( 'Columns margin', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 15
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					]
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container' => 'grid-column-gap: {{SIZE}}{{UNIT}}'
				]
			]
		);

		// Row margin.
		$this->add_control(
			'grid_style_rows_margin',
			[
				'label'     => esc_html__( 'Rows margin', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 30
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100
					]
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container' => 'grid-row-gap: {{SIZE}}{{UNIT}}'
				]
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style Box Options.
	 *
	 * Registers the style box controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_style_box_options() {
		// Box.
		$this->start_controls_section(
			'section_box',
			[
				'label' => esc_html__( 'Box', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		// Image border radius.
		$this->add_control(
			'grid_box_border_width',
			[
				'label'      => esc_html__( 'Border Widget', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post' => 'border-style: solid; border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
				],			
			]
		);

		// Border Radius.
		$this->add_control(
			'grid_style_border_radius',
			[
				'label'     => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 0
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200
					]
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post' => 'border-radius: {{SIZE}}{{UNIT}}'
				]
			]
		);

		// Box internal padding.
		$this->add_responsive_control(
			'grid_items_style_padding',
			[
				'label'      => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
				]
			]
		);

		$this->start_controls_tabs( 'grid_button_style' );

		// Normal tab.
		$this->start_controls_tab(
			'grid_button_style_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' )
			]
		);

		// Normal background color.
		$this->add_control(
			'grid_button_style_normal_bg_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post' => 'background-color: {{VALUE}};'
				]
			]
		);

		// Normal border color.
		$this->add_control(
			'grid_button_style_normal_border_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post' => 'border-color: {{VALUE}};'
				]
			]
		);

		// Normal box shadow.
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'grid_button_style_normal_box_shadow',
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post'
			]
		);

		$this->end_controls_tab();

		// Hover tab.
		$this->start_controls_tab(
			'grid_button_style_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' )
			]
		);

		// Hover background color.
		$this->add_control(
			'grid_button_style_hover_bg_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post:hover' => 'background-color: {{VALUE}};'
				]
			]
		);

		// Hover border color.
		$this->add_control(
			'grid_button_style_hover_border_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post:hover' => 'border-color: {{VALUE}};'
				]
			]
		);

		// Hover box shadow.
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'grid_button_style_hover_box_shadow',
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Image Options.
	 *
	 * Registers the style image controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_style_image_options() {
		// Box.
		$this->start_controls_section(
			'section_image',
			[
				'label' => esc_html__( 'Image', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		// Image border radius.
		$this->add_control(
			'grid_image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .post-grid-inner .post-grid-thumbnail img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->add_responsive_control(
			'grid_style_image_margin',
			[
				'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .post-grid-inner .post-grid-thumbnail' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->add_responsive_control(
			'grid_list_image_width',
			[
				'label' => esc_html__( 'Image Width', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 400,
					],
					'%' => [
						'min' => 20,
						'max' => 60,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 200,
				],
				'selectors' => [
					'{{WRAPPER}} .grid-list-layout .post-grid-thumbnail-wrap' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'grid_style' => '7'
				],
				'separator' => 'before'
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style > Title.
	 *
	 * Registers the style title controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_style_title_options() {
		// Tab.
		$this->start_controls_section(
			'section_grid_title_style',
			[
				'label'     => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE
			]
		);

		// Title typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'grid_title_style_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post .title, {{WRAPPER}} .wpz-grid-container .wpz-post .title > a'
			]
		);

		$this->start_controls_tabs( 'grid_title_color_style' );

		// Normal tab.
		$this->start_controls_tab(
			'grid_title_style_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' )
			]
		);

		// Title color.
		$this->add_control(
			'grid_title_style_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post .title, {{WRAPPER}} .wpz-grid-container .wpz-post .title > a' => 'color: {{VALUE}};'
				]
			]
		);

		$this->end_controls_tab();

		// Hover tab.
		$this->start_controls_tab(
			'grid_title_style_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' )
			]
		);

		// Title hover color.
		$this->add_control(
			'grid_title_style_hover_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post .title, {{WRAPPER}} .wpz-grid-container .wpz-post .title > a:hover' => 'color: {{VALUE}};'
				]
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Title margin.
		$this->add_responsive_control(
			'grid_title_style_margin',
			[
				'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post .title, {{WRAPPER}} .wpz-grid-container .wpz-post .title > a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style > Meta.
	 *
	 * Registers the style meta controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_style_meta_options() {
		// Tab.
		$this->start_controls_section(
			'section_grid_meta_style',
			[
				'label'     => esc_html__( 'Meta', 'wpzoom-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE
			]
		);

		// Meta typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'grid_meta_style_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post .post-grid-meta span'
			]
		);

		// Meta color.
		$this->add_control(
			'grid_meta_style_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post .post-grid-meta span'      => 'color: {{VALUE}};',
					'{{WRAPPER}} .wpz-grid-container .wpz-post .post-grid-meta span a' => 'color: {{VALUE}};'
				]
			]
		);

		// Meta margin.
		$this->add_responsive_control(
			'grid_meta_style_margin',
			[
				'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post .post-grid-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				]
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style > Content.
	 *
	 * Registers the style content controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_style_content_options() {
		// Tab.
		$this->start_controls_section(
			'section_grid_content_style',
			[
				'label' => esc_html__( 'Content', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE
			]
		);

		// Content typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'grid_content_style_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector'  => '{{WRAPPER}} .wpz-grid-container .wpz-post .post-grid-excerpt p'
			]
		);

		// Content color.
		$this->add_control(
			'grid_content_style_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post .post-grid-excerpt p' => 'color: {{VALUE}};'
				]
			]
		);

		// Content margin
		$this->add_responsive_control(
			'grid_content_style_margin',
			[
				'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post .post-grid-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style > Readmore.
	 *
	 * Registers the style readmore controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_style_readmore_options() {
		// Tab.
		$this->start_controls_section(
			'section_grid_readmore_style',
			[
				'label' => esc_html__( 'Read More', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE
			]
		);

		// Readmore typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'grid_readmore_style_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn'
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn',
			]
		);

		$this->start_controls_tabs( 'grid_readmore_color_style' );

		// Normal tab.
		$this->start_controls_tab(
			'grid_readmore_style_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' )
			]
		);

		// Readmore color.
		$this->add_control(
			'grid_readmore_style_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn' => 'color: {{VALUE}};'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'grid_readmore_style_background',
				'label' => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'global' => [
							'default' => '',
						],
					],
				],
			]
		);

		$this->end_controls_tab();

		// Hover tab.
		$this->start_controls_tab(
			'grid_readmore_style_color_hover_tab',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' )
			]
		);

		// Readmore hover color.
		$this->add_control(
			'grid_readmore_style_hover_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn:hover' => 'color: {{VALUE}};'
				]
			]
		);
		
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'grid_readmore_style_background_hover',
				'label' => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn:hover',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'grid_readmore_style_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'grid_readmore_style_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'grid_readmore_style_border',
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'grid_readmore_style_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'grid_readmore_style_box_shadow',
				'selector' => '{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn',
			]
		);

		$this->add_responsive_control(
			'grid_readmore_style_text_padding',
			[
				'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		// Readmore margin
		$this->add_responsive_control(
			'grid_readmore_style_margin',
			[
				'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-grid-container .wpz-post a.read-more-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style > Load More.
	 *
	 * Registers the style Load More controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	private function wpz_style_loadmore_options() {
		
		// Tab.
		$this->start_controls_section(
			'section_grid_loadmore_style',
			[
				'label' => esc_html__( 'Load More Button', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'pagination_type' => 'load_more'
				]
			]
		);

		// Loadmore typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'grid_loadmore_style_typography',
				'selector' => '{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn'
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'grid_loadmore_text_shadow',
				'selector' => '{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn',
			]
		);

		$this->start_controls_tabs( 'grid_loadmore_color_style' );

		// Normal tab.
		$this->start_controls_tab(
			'grid_loadmore_style_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' )
			]
		);

		// Load more color.
		$this->add_control(
			'grid_loadmore_style_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn' => 'color: {{VALUE}};'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'grid_loadmore_style_background',
				'label' => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'global' => [
							'default' => '',
						],
					],
				],
			]
		);

		$this->end_controls_tab();

		// Hover tab.
		$this->start_controls_tab(
			'grid_loadmore_style_color_hover_tab',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' )
			]
		);

		// Loadmore hover color.
		$this->add_control(
			'grid_loadmore_style_hover_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn:hover' => 'color: {{VALUE}};'
				]
			]
		);
		
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'grid_loadmore_style_background_hover',
				'label' => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn:hover',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'grid_loadmore_style_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'grid_loadmore_style_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'grid_loadmore_style_border',
				'selector' => '{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'grid_loadmore_style_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'grid_loadmore_style_box_shadow',
				'selector' => '{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn',
			]
		);

		$this->add_responsive_control(
			'grid_loadmore_style_text_padding',
			[
				'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-posts-grid-load-more a.wpz-posts-grid-load-more-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		// Load more margin
		$this->add_responsive_control(
			'grid_loadmore_style_margin',
			[
				'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-posts-grid-load-more' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render the Widget.
	 *
	 * Renders the widget on the frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	protected function render( $instance = [] ) {

		// Get settings.
		$settings = $this->get_settings_for_display();

		if ( get_query_var('paged') ) {
			$paged = get_query_var('paged');
		} else if ( get_query_var('page') ) {
			$paged = get_query_var('page');
		} else {
			$paged = 1;
		}

		$posts_per_page = ( ! empty( $settings[ 'posts_per_page' ] ) ?  $settings[ 'posts_per_page' ] : 3 );
		$cats   = is_array( $settings[ 'post_categories' ] ) ? implode( ',', $settings[ 'post_categories' ] ) : $settings[ 'post_categories' ];
		$excats = is_array( $settings[ 'ex_post_categories' ] ) ? $settings[ 'ex_post_categories' ] : array();
		$category__not_in = array();
		
		foreach ( $excats as $excat )
		{
			$cat = get_category_by_slug( $excat );
			$cat and $category__not_in[] = $cat->term_id;
		}
		
		$grid_style = $settings[ 'grid_style' ];

		$query_args = array(
			'posts_per_page' 	  => absint( $posts_per_page ),
			'post_status'         => 'publish',
			'paged'				  => $paged,
			'post__not_in'        => get_option( 'sticky_posts' ),
			'ignore_sticky_posts' => true,
			'category_name' 	  => $cats
		);

		if( $excats ) {
			$query_args['category__not_in'] = $category__not_in;
		}

		if( 'none' == $settings['pagination_type'] ) {
			$query_args['no_found_rows'] = true;
		}

		// Order by.
		if ( ! empty( $settings[ 'orderby' ] ) ) {
			$query_args[ 'orderby' ] = $settings[ 'orderby' ];
		}

		// Order .
		if ( ! empty( $settings[ 'order' ] ) ) {
			$query_args[ 'order' ] = $settings[ 'order' ];
		}

		// Offset .
		$adjusted_offset = (int) $settings['offset'] + ( ( $paged - 1 ) * (int)$posts_per_page );
		$query_args[ 'offset' ] = $adjusted_offset;
		
		$all_posts = new \WP_Query( $query_args );
		
		//Need to pass offset to ajax data
		$offset = $adjusted_offset + (int)$posts_per_page;
		$query_args['offset'] = $offset;

		$data_posts_grid = array_merge( 
			$query_args, 
			array( 
				'show_image'          => $settings['show_image'],
				'post_thumbnail_size' => $settings['post_thumbnail_size'],
				'show_title'          => $settings['show_title'],
				'title_tag'           => $settings['title_tag'],
				'meta_data'           => $settings['meta_data'],
				'show_excerpt'        => $settings['show_excerpt'],
				'excerpt_length'      => $settings['excerpt_length'],
				'show_read_more'      => $settings['show_read_more'],
				'read_more_text'      => $settings['read_more_text'],
				'grid_style'          => $grid_style,
				'total'               => $all_posts->found_posts,
			) 
		);
		?>
		<div 
			class="wpz-grid" 
			data-uid="<?php echo esc_attr( $this->get_id() ); ?>"
			data-offset="<?php echo esc_attr( $offset ); ?>"
			data-posts-grid='<?php echo wp_json_encode( $data_posts_grid ); ?>'>
			<?php 

			$columns_desktop = ( ! empty( $settings[ 'columns' ] ) ? 'wpz-grid-desktop-' . $settings[ 'columns' ] : 'wpz-grid-desktop-3' );
			$columns_tablet = ( ! empty( $settings[ 'columns_tablet' ] ) ? ' wpz-grid-tablet-' . $settings[ 'columns_tablet' ] : ' wpz-grid-tablet-2' );
			$columns_mobile = ( ! empty( $settings[ 'columns_mobile' ] ) ? ' wpz-grid-mobile-' . $settings[ 'columns_mobile' ] : ' wpz-grid-mobile-1' );

			$grid_class = '';

			if( 5 == $grid_style ){
				$grid_class = ' grid-meta-bottom';
			} elseif( 7 == $grid_style ){
				$grid_class = ' grid-list-layout';
			} elseif( 8 == $grid_style ){
				$grid_class = ' grid-overlay-layout';
			}

			?>
			<div class="wpz-grid-container elementor-grid <?php echo esc_attr( $columns_desktop ); ?> <?php echo esc_attr( $columns_tablet ); ?> <?php echo esc_attr( $columns_mobile ); ?> <?php echo esc_attr( $grid_class ); ?>">
				<?php
				if ( $all_posts->have_posts() ) {
					if ( 8 == $grid_style ) {
						include( __DIR__ . '/layouts/layout-8.php' );
					} elseif ( 7 == $grid_style ) {
						include( __DIR__ . '/layouts/layout-7.php' );
					} elseif ( 6 == $grid_style ) {
						include( __DIR__ . '/layouts/layout-6.php' );
					} elseif ( 5 == $grid_style ) {
						include( __DIR__ . '/layouts/layout-5.php' );
					} elseif( 4 == $grid_style ) {
						include( __DIR__ . '/layouts/layout-4.php' );
					} elseif( 3 == $grid_style ) {
						include( __DIR__ . '/layouts/layout-3.php' );
					} elseif( 2 == $grid_style ) {
						include( __DIR__ . '/layouts/layout-2.php' );
					} else {
						include( __DIR__ . '/layouts/layout-1.php' );
					}
				} ?>
			</div>

			<?php if ( 'pagination' == $settings['pagination_type'] ) :
				$links = paginate_links( array(
					'total'		=> $all_posts->max_num_pages,
					'current'	=> $paged,
					'prev_next' => true,
					'prev_text' => '<span aria-hidden="true"><i class="fa fa-angle-left"></i></span>',
					'next_text' => '<span aria-hidden="true"><i class="fa fa-angle-right"></i></span>',
				) );

				if( !empty( $links ) ) {
					printf( '<div class="wpzoom-posts-grid-pagination"><nav class="navigation paging-navigation pagination" role="navigation">%s</nav></div>', $links );
				}

			endif;
			?>
			<?php if ( 'load_more' == $settings['pagination_type'] && !empty( $settings['load_more_text'] ) ) : ?>
				<div class="wpz-posts-grid-load-more">
					<a href="#" class="wpz-posts-grid-load-more-btn btn"><?php echo esc_html_e( $settings['load_more_text'] ); ?></a>
				</div>
				<?php wp_nonce_field( 'wpz_posts_grid_load_more', 'wpz_posts_grid_load_more' . $this->get_id() ); ?>
			<?php endif; ?>
		
		</div><!-- //.wpz-grid -->
		<?php
	}

	/**
	 * Filter Excerpt Length
	 *
	 * Filters the excerpt length to allow custom values.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Custom excerpt length.
	 */
	public function wpz_filter_excerpt_length( $length ) {
		
		$settings = $this->get_settings();
		$excerpt_length = ( !empty( $settings[ 'excerpt_length' ] ) ) ? absint( $settings[ 'excerpt_length' ] ) : 25;

		return absint( $excerpt_length );
	}

	/**
	 * Filter Excerpt More.
	 *
	 * Filters the read more value at the end of excerpts.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Excerpt more.
	 */
	public function wpz_filter_excerpt_more( $more ) {
		return '&hellip;';
	}

	/**
	 * Render Post Thumbnail.
	 *
	 * Outputs the markup for the post thumbnail.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	protected function render_thumbnail() {	
		$settings = $this->get_settings();

		$show_image = $settings[ 'show_image' ];

		if ( 'yes' !== $show_image ) {
			return;
		}

		$post_thumbnail_size = $settings[ 'post_thumbnail_size' ];
			
		if ( has_post_thumbnail() ) :  ?>
			<div class="post-grid-thumbnail">
				<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( get_the_title() ); ?>">
					<?php the_post_thumbnail( $post_thumbnail_size ); ?>
				</a>
			</div>
		<?php endif;
	}

	/**
	 * Render Post Title.
	 *
	 * Outputs the markup for the post title.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	protected function render_title() {	
		$settings = $this->get_settings();

		$show_title = $settings[ 'show_title' ];

		if ( 'yes' !== $show_title ) {
			return;
		}

		$title_tag = esc_attr( $settings[ 'title_tag' ] );
			
		?>
		<<?php echo Utils::validate_html_tag( $title_tag ); // WPCS: XSS OK. ?> class="title">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"><?php the_title(); ?></a>
		</<?php echo Utils::validate_html_tag( $title_tag ); // WPCS: XSS OK. ?>>
		<?php
	}

	/**
	 * Render Post Meta.
	 *
	 * Outputs the markup for the post meta.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $fields               Optionally show only given fields.
	 * @param  bool   $invert               Inverts whether to show or hide the given fields.
	 * @param  bool   $combined_author_date Whether to show the author, author picture, and date in a combined section.
	 * @return void
	 */
	protected function render_meta( $fields = array(), $invert = false, $combined_author_date = false ) {
		$settings = $this->get_settings();

		$meta_data = $settings[ 'meta_data' ];

		if ( empty( $meta_data ) ) {
			return;
		}

		$all_fields = empty( $fields );

		?>
		<div class="post-grid-meta">
			<?php
			if ( $combined_author_date ) {

				?><span class="author-date-wrap"><?php

			}

			if ( ( $all_fields || ( false === $invert && in_array( 'author_pic', $fields ) ) || ( true === $invert && ! in_array( 'author_pic', $fields ) ) ) && in_array( 'author_pic', $meta_data ) ) {

				?><span class="post-author-pic"><?php echo get_avatar( get_the_author_meta( 'ID' ), 36 ); ?></span><?php

			}

			if ( $combined_author_date ) {

				?><span class="author-date-inner-wrap"><?php

			}

			if ( ( $all_fields || ( false === $invert && in_array( 'author', $fields ) ) || ( true === $invert && ! in_array( 'author', $fields ) ) ) && in_array( 'author', $meta_data ) ) {

				?><span class="post-author"><?php the_author(); ?></span><?php

			}

			if ( ( $all_fields || ( false === $invert && in_array( 'date', $fields ) ) || ( true === $invert && ! in_array( 'date', $fields ) ) ) && in_array( 'date', $meta_data ) ) {

				?><span class="post-date"><?php echo apply_filters( 'the_date', get_the_date(), get_option( 'date_format' ), '', '' ); ?></span><?php

			}

			if ( $combined_author_date ) {

				?></span></span><?php

			}

			if ( ( $all_fields || ( false === $invert && in_array( 'categories', $fields ) ) || ( true === $invert && ! in_array( 'categories', $fields ) ) ) && in_array( 'categories', $meta_data ) ) {

				$categories_list = get_the_category_list( esc_html__( ', ', 'wpzoom-elementor-addons' ) ); 

				if ( $categories_list ) {
					printf( '<span class="post-categories">%s</span>', $categories_list ); // WPCS: XSS OK.
				}

			}

			if ( ( $all_fields || ( false === $invert && in_array( 'comments', $fields ) ) || ( true === $invert && ! in_array( 'comments', $fields ) ) ) && in_array( 'comments', $meta_data ) ) {

				?><span class="post-comments"><?php comments_number(); ?></span><?php

			}

			$other_fields = apply_filters( 'wpzoom_elementor_addons_posts_grid_meta_fields', array() );

			if ( ! empty( $other_fields ) ) {
				echo '<div class="other-meta">';

				foreach ( $other_fields as $field_id => $field_label ) {
					if ( ( $all_fields || ( false === $invert && in_array( $field_id, $fields ) ) || ( true === $invert && ! in_array( $field_id, $fields ) ) ) && in_array( $field_id, $meta_data ) ) {
						printf( '<span class="zoom-field_%s" title="%s">', esc_attr( $field_id ), esc_attr( $field_label ) );
						do_action( 'wpzoom_elementor_addons_posts_grid_meta_field_display', $field_id );
						echo '</span>';
					}
				}

				echo '</div>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render Post Excerpt.
	 *
	 * Outputs the markup for the post excerpt.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	protected function render_excerpt() {
		$settings = $this->get_settings_for_display();

		if ( 'yes' !== $settings[ 'show_excerpt' ] ) {
			return;
		}

		$excerpt_length = ( ! empty( $settings[ 'excerpt_length' ] ) ) ? absint( $settings[ 'excerpt_length' ] ) : 25;

		$post = get_post();

		if ( ! $post ) {
			return;
		}

		$source = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;

		$source = strip_shortcodes( $source );

		if ( function_exists( 'excerpt_remove_blocks' ) ) {
			$source = excerpt_remove_blocks( $source );
		}

		$excerpt = wp_trim_words( $source, $excerpt_length, $this->wpz_filter_excerpt_more( '' ) );

		printf( '<div class="post-grid-excerpt"><p>%s</p></div>', wp_kses_post( $excerpt ) );
	}

	/**
	 * Render Post Read More.
	 *
	 * Outputs the markup for the readmore button.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	protected function render_readmore() {
		$settings = $this->get_settings();

		$show_read_more = $settings[ 'show_read_more' ];
		$read_more_text = $settings[ 'read_more_text' ];

		if ( 'yes' !== $show_read_more ) {
			return;
		}

		?><a class="read-more-btn" href="<?php the_permalink(); ?>"><?php echo esc_html( $read_more_text ); ?></a><?php
	}

}
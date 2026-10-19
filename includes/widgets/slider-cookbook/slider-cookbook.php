<?php
namespace WPZOOMElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Embed;
use Elementor\Icons_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - cookbook Slider Widget.
 *
 * Elementor widget that inserts a customizable slider.
 *
 * @since 1.0.0
 */
class Slider_cookbook extends Widget_Base {
	
	/**
	 * @var \WP_Query
	 */
	private $query = null;
	
	
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

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-slider-cookbook', plugins_url( 'frontend.css', __FILE__ ), [ 'slick-slider', 'slick-slider-theme' ], WPZOOM_EL_ADDONS_VER );

		if ( ! wp_script_is( 'jquery-slick-slider', 'registered' ) ) {
			wp_register_script( 'jquery-slick-slider', WPZOOM_EL_ADDONS_URL . 'assets/vendors/slick/slick.min.js', [ 'jquery' ], WPZOOM_EL_ADDONS_VER, true );
		}

		wp_register_script( 'wpzoom-elementor-addons-js-frontend-slider-cookbook', plugins_url( 'frontend.js', __FILE__ ), [ 'jquery', 'jquery-slick-slider' ], WPZOOM_EL_ADDONS_VER, true );
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
		return 'wpzoom-elementor-addons-slider-cookbook';
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
		return esc_html__( 'CookBook Slideshow', 'wpzoom-elementor-addons' );
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
		return [ 'wpzoom-elementor-addons-cookbook' ];
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
			'wpzoom-elementor-addons-css-frontend-slider-cookbook'
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
			'wpzoom-elementor-addons-js-frontend-slider-cookbook'
		];
	}

	/**
	 * Get the query
	 *
	 * Returns the current query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return \WP_Query The current query.
	 */
	public function get_query() {
		return $this->query;
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
		
		if ( !WPZOOM_Elementor_Widgets::is_supported_theme( 'cookbook' ) ) {
			$this->register_restricted_controls();
		}
		else {
			$this->register_content_controls();
			$this->register_style_controls();
		}

	}

	/**
	 * Register restricted Controls.
	 *
	 * Registers all the controls for this widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_restricted_controls() {

		$this->start_controls_section(
			'section_restricted_cookbook_slideshow',
			array(
				'label' => esc_html__( 'Widget not available', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'restricted_widget_text',
			[
				'raw' => wp_kses_post( __( 'This widget is supported only by the <a href="https://www.wpzoom.com/themes/cookbook/?utm_source=wpadmin&utm_medium=wpzoom-elementor-addons&utm_campaign=theme-required">"CookBook"</a> theme', 'wpzoom-elementor-addons' ) ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
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
			'_section_cookbook_slider',
			array(
				'label' => esc_html__( 'CookBook Slideshow', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			)
		);
		
		$this->add_control(
			'featured_type',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => esc_html__( 'Content', 'wpzoom-elementor-addons' ),
				'options'     => array(
					'featured' => esc_html__( 'Featured Posts', 'wpzoom-elementor-addons' ),
					'latest' => esc_html__( 'Latest Posts', 'wpzoom-elementor-addons' ),
                    'random' => esc_html__( 'Random Posts', 'wpzoom-elementor-addons' ),
				),
				'default' => 'featured'
			)
		);
		$this->add_control(
			'slideshow_posts',
			array(
				'type'        => Controls_Manager::TEXT,
				'label'       => esc_html__( 'Number of Posts', 'wpzoom-elementor-addons' ),
				'default' => '5'
			)
		);
		$this->add_control(
			'slider_title',
			array(
				'label' => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__( 'Title to display at the top of the slider.', 'wpzoom-elementor-addons' ),
				'default' => esc_html__( 'Featured Recipes', 'wpzoom-elementor-addons' )
			)
		);
		$this->add_control(
			'slider_category',
			array(
				'label' => esc_html__( 'Display Category', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'default' => 'yes',
			)
		);
        $this->add_control(
            'slider_recipe_details',
            array(
                'label' => esc_html__( 'Display Cooking Time & Difficulty', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
                'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
                'default' => 'yes',
            )
        );
        $this->add_control(
            'slider_author_pic',
            array(
                'label' => esc_html__( 'Display Author Photo', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
                'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
                'default' => 'yes',
            )
        );
        $this->add_control(
            'slider_author_name',
            array(
                'label' => esc_html__( 'Display Author Name', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
                'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
                'default' => 'yes',
            )
        );
		$this->add_control(
			'slider_date',
			array(
				'label' => esc_html__( 'Display Date/Time', 'wpzoom-elementor-addons' ),
				'description' => wp_kses_post( __( '<strong>Date/Time format</strong> can be changed <a href=\'options-general.php\' target=\'_blank\'>here</a>.', 'wpzoom-elementor-addons' ) ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'default' => 'yes',
			)
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

		$this->add_control(
			'auto_height',
			[
				'label' => esc_html__( 'Automatic Height', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no'
			]
		);

		$this->add_responsive_control(
			'auto_height_size',
			[
				'label' => esc_html__( 'Automatic Height Size', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					]
				],
				'default' => [
					'unit' => '%',
					'size' => 100
				],
				'selectors' => [
					'{{WRAPPER}} .slick-slider' => 'height: {{SIZE}}vh;'
				],
				'condition' => [
					'auto_height' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'auto_height_max',
			[
				'label' => esc_html__( 'Automatic Height Maximum', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					]
				],
				'default' => [
					'unit' => 'px',
					'size' => 550
				],
				'desktop_default' => [
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
					'{{WRAPPER}} .slick-slider' => 'max-height: {{SIZE}}{{UNIT}};'
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
					'{{WRAPPER}} .slide-overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'selector' => '{{WRAPPER}} .slick-slide',
				'exclude' => [
					 'image'
				]
			]
		);

		//Category Styling
		$this->add_control(
			'_heading_category',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Category', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'cat_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .cat-links a' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( '_tabs_cat_color' );

		$this->start_controls_tab(
			'_tab_cat_color_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'cat_color',
			[
				'label' => esc_html__( 'Category Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cat-links a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_cat_color_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'cat_color_hover',
			[
				'label' => esc_html__( 'Category Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cat-links a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'category',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .cat-links',
			]
		);

		//Title Styling
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
					'{{WRAPPER}} .cookbook-slide-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( '_tabs_title_color' );

		$this->start_controls_tab(
			'_tab_title_color_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cookbook-slide-title a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_title_color_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => esc_html__( 'Title Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cookbook-slide-title a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .cookbook-slide-title',
			]
		);

		//Content Styling
		$this->add_control(
			'_heading_content',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Content', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => esc_html__( 'Content Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slide-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'content_text',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .slide-content',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_footer',
			[
				'label' => esc_html__( 'Slide Footer', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'footer_margin',
			[
				'label' => esc_html__( 'Top Margin', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .slide-footer' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);	
		$this->add_responsive_control(
			'footer_spacing',
			[
				'label' => esc_html__( 'Top Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .slide-footer' => 'padding-top: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'footer_border',
				'selector' => '{{WRAPPER}} .slide-footer',
			]
		);

		//Author Styling
		$this->add_control(
			'_heading_author',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Author', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'author_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .entry-author-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( '_tabs_author_color' );

		$this->start_controls_tab(
			'_tab_author_color_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'author_color',
			[
				'label' => esc_html__( 'Author Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .entry-author-name a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_author_color_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'author_color_hover',
			[
				'label' => esc_html__( 'Author Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .entry-author-name a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'author',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .entry-author-name',
			]
		);

		//Date Styling
		$this->add_control(
			'_heading_date',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Date', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_control(
			'date_color',
			[
				'label' => esc_html__( 'Date Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .entry-date' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'date',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .entry-date',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_navigation',
			[
				'label' => esc_html__( 'Slide Navigation', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( '_tabs_arrow_color' );

		$this->start_controls_tab(
			'_tab_arrow_color_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'arrow_color',
			[
				'label' => esc_html__( 'Arrows Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-arrow' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_arrow_color_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'arrow_color_hover',
			[
				'label' => esc_html__( 'Arrow Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .slick-arrow:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'nav_numbers',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .cookbook-slider-prevnext-number',
			]
		);
		$this->add_control(
			'number_nav_color',
			[
				'label' => esc_html__( 'Numbers Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cookbook-slider-prevnext-number' => 'color: {{VALUE}}',
				],
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
	 * @return void
	 */
	protected function render() {

		if ( !WPZOOM_Elementor_Widgets::is_supported_theme( 'cookbook' ) ) {
			if( current_user_can('editor') || current_user_can('administrator') ) {
				echo '<h3>' . esc_html__( 'Widget not available', 'wpzoom-elementor-addons' ) . '</h3>';
				echo wp_kses_post( __( 'This widget is supported only by the <a href="https://www.wpzoom.com/themes/cookbook/?utm_source=wpadmin&utm_medium=wpzoom-elementor-addons&utm_campaign=theme-required">"cookbook"</a> theme', 'wpzoom-elementor-addons' ) );
			}
			return;
		}

		$settings = $this->get_settings_for_display();
		
		$FeaturedSource  = $settings['featured_type'];
		$slideshow_posts = $settings['slideshow_posts'];

		$show_recipe_details = isset( $settings['slider_recipe_details'] ) ? ( 'yes' === $settings['slider_recipe_details'] ) : true;
		$show_author_pic     = isset( $settings['slider_author_pic'] ) ? ( 'yes' === $settings['slider_author_pic'] ) : true;
		$show_author_name    = isset( $settings['slider_author_name'] ) ? ( 'yes' === $settings['slider_author_name'] ) : true;
		$show_author_date    = isset( $settings['slider_date'] ) ? ( 'yes' === $settings['slider_date'] ) : true;


        if ($FeaturedSource == 'featured' ) {
    		$args = array(
    			'showposts'    => $slideshow_posts,
    			'post__not_in' => get_option( 'sticky_posts' ),
    			'meta_key'     => 'wpzoom_is_featured',
    			'meta_value'   => 1,
    			'orderby'     => 'menu_order date',
    			'post_status' => array( 'publish' ),
    			'post_type' => 'post'
            );

        } elseif ( $FeaturedSource == 'latest' ) {
            $args = array(
                'showposts'    => $slideshow_posts,
                'post__not_in' => get_option( 'sticky_posts' ),
                'orderby'     => 'date',
                'post_status' => array( 'publish' ),
                'post_type' => 'post'
            );
        } else {
            $args = array(
                'showposts'    => $slideshow_posts,
                'post__not_in' => get_option( 'sticky_posts' ),
                'orderby'     => 'rand',
                'post_status' => array( 'publish' ),
                'post_type' => 'post'
            );
        }
	
		$featured = new \WP_Query($args );
	
		if ( $featured->have_posts() ) : ?>

			<div class="cookbook-slider <?php echo get_theme_mod('slider-styles', zoom_customizer_get_default_option_value('slider-styles', cookbook_customizer_data()))?>">

				<div class="cookbook-slides">
					<?php while ( $featured->have_posts() ) : $featured->the_post(); ?>
						<?php
	
						$image_size = 'loop-sticky';
						$reatina_image_size = 'loop-sticky-retina';

						$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), $image_size);
						$retina_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), $reatina_image_size);
		
						$style = ' style="background-image:url(\'' . wpzoom_get_value($large_image_url, '', 0) . '\')" data-rjs="' . wpzoom_get_value($retina_image_url, '', 0) . '"';
		
						?>

						<div class="cookbook-slide">

							<div class="slide-overlay">

                                <div class="cookbook-slider-title">
                                    <h3><?php echo esc_html( isset( $settings['slider_title'] ) ? $settings['slider_title'] : __( 'Featured Recipes', 'wpzoom-elementor-addons' ) ); ?></h3>
                                </div>

								<div class="slide-header">

								   <?php if ( 'yes' == $settings['slider_category'] ) printf( '<span class="cat-links">%s</span>', get_the_category_list( ', ' ) ); ?>

									<?php the_title( sprintf( '<h3 class="cookbook-slide-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>

									<?php
									if ( $show_recipe_details ) {
										$time       = trim( get_post_meta( get_the_ID(), 'wpzoom_recipe_cook_time', true ) );
										$difficulty = trim( get_post_meta( get_the_ID(), 'wpzoom_recipe_difficulty', true ) );

										if ( ! empty( $time ) || ! empty( $difficulty ) ) {
											echo '<div class="entry-recipe-details">';

											if ( ! empty( $time ) ) {
												printf( '<span class="entry-recipe-details_time">%s</span>', esc_html( $time ) );
											}


                                            if ( ( '' != $difficulty ) && ( 'none' != $difficulty ) ) {

												printf( '<span class="entry-recipe-details_difficulty">%s</span>', esc_html( $difficulty ) );
											}

											echo '</div>';
										}
									}
									?>
								</div>

								<div class="slide-content">

									<?php the_excerpt(); ?>

								</div>

									<div class="slide-footer">
										<?php
										if ( $show_author_pic ) {
											$author_id = get_the_author_meta( 'ID' );
											printf( '<span class="entry-author-pic"><a href="%s">%s</a></span>', esc_url( get_author_posts_url( $author_id ) ), get_avatar( $author_id, 36 ) );
										}

										if ( $show_author_name || $show_author_date ) {
											echo '<div class="entry-meta-details">';

											if ( $show_author_name ) {
												printf( '<span class="entry-author-name"><a href="%s">%s</a></span>', esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ), esc_html( get_the_author() ) );
											}

											if ( $show_author_date ) {
												printf( '<span class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></span>', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) );
											}

											echo '</div>';
										}
										?>
									</div>

							</div>

							<div class="slide-background" <?php echo $style; ?>><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wpzoom-elementor-addons' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"></a></div>

						</div>
					<?php endwhile; ?>

				</div>

				<div class="cookbook-slider-prevnext">
					<div class="prevnext-wrapper">
						<button type="button" class="slick-prev cookbook-slider-prevnext-prev" title="<?php esc_attr_e( 'Previous', 'wpzoom-elementor-addons' ); ?>">
							<svg width="16" height="12" viewBox="0 0 16 12" xmlns="http://www.w3.org/2000/svg">
								<path d="M3.83 5L7.41 1.41L6 0L0 6L6 12L7.41 10.59L3.83 7L16 7V5L3.83 5Z" fill="currentColor"/>
							</svg>
						</button>

						<span class="cookbook-slider-prevnext-number">1/<?php echo esc_html( $featured->post_count ); ?></span>

						<button type="button" class="slick-next cookbook-slider-prevnext-next" title="<?php esc_attr_e( 'Next', 'wpzoom-elementor-addons' ); ?>">
							<svg width="16" height="12" viewBox="0 0 16 12" xmlns="http://www.w3.org/2000/svg">
								<path d="M12.17 7L8.59 10.59L10 12L16 6L10 -5.24537e-07L8.59 1.41L12.17 5L6.11959e-07 5L4.37114e-07 7L12.17 7Z" fill="currentColor"/>
							</svg>
						</button>
					</div>
				</div>
		
			</div>
		
		<?php else: ?>
		
			<?php if( current_user_can('editor') || current_user_can('administrator') ) { ?>
		
				<div class="empty-slider">
					<p><?php esc_html_e( 'If you want to add posts in the slider, just edit each post and mark it as <strong>Featured</strong>:', 'wpzoom-elementor-addons' ); ?></p>
		
					<img src="https://www.wpzoom.com/wp-content/uploads/2019/08/featured.gif" />
					<br />
		
					<p><?php esc_html_e( 'This option is located either in the Sidebar or below the editor', 'wpzoom-elementor-addons'); ?></p>
					<p>
						<?php
						printf(
							wp_kses_post( __( 'For more information about adding posts to the slider, please read <strong><a target="_blank" href="%1$s">theme documentation</a></strong>', 'wpzoom-elementor-addons' ) ),
							'https://www.wpzoom.com/documentation/cookbook/cookbook-configure-homepage-slider/?utm_source=wpadmin&utm_medium=wpzoom-elementor-addons&utm_campaign=theme-required'
						);
						?>
					</p>
				</div>
		
			<?php } ?>
		
		<?php endif;
			wp_reset_postdata();
	}
}
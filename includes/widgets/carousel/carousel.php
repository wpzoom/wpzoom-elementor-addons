<?php
namespace WPZOOMElementorWidgets;

use \Elementor\Widget_Base;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;

use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

use Elementor\Utils;
use Elementor\Icons_Manager;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - Carousel Widget.
 *
 * Elementor widget that inserts a customizable carousel.
 *
 * @since 1.0.0
 */
class Carousel extends Widget_Base {
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

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-carousel', plugins_url( 'frontend.css', __FILE__ ), [ 'slick-slider', 'slick-slider-theme' ], WPZOOM_EL_ADDONS_VER );

		if ( ! wp_script_is( 'jquery-slick-slider', 'registered' ) ) {
			wp_register_script( 'jquery-slick-slider', WPZOOM_EL_ADDONS_URL . 'assets/vendors/slick/slick.min.js', [ 'jquery' ], WPZOOM_EL_ADDONS_VER, true );
		}

		wp_register_script( 'wpzoom-elementor-addons-js-frontend-carousel', plugins_url( 'frontend.js', __FILE__ ), [ 'jquery', 'jquery-slick-slider' ], WPZOOM_EL_ADDONS_VER, true );
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
		return 'wpzoom-elementor-addons-carousel';
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
		return esc_html__( 'Carousel', 'wpzoom-elementor-addons' );
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
		return 'eicon-carousel';
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
			'wpzoom-elementor-addons-css-frontend-carousel'
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
			'wpzoom-elementor-addons-js-frontend-carousel'
		];
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
				'title_field' => '<# print(title || "' . esc_js( __( 'Carousel Item', 'wpzoom-elementor-addons' ) ) . '"); #>',
				'default' => array_fill( 0, 7, $placeholder )
			]
		);

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

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_settings',
			[
				'label' => esc_html__( 'Settings', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
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
				'frontend_available' => true,
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
				'frontend_available' => true,
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
				'style_transfer' => true,
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
				'style_transfer' => true,
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
					'both' => esc_html__( 'Arrow & Dots', 'wpzoom-elementor-addons' ),
				],
				'default' => 'arrow',
				'frontend_available' => true,
				'style_transfer' => true,
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
					6 => esc_html__( '6 Slides', 'wpzoom-elementor-addons' ),
				],
				'desktop_default' => 4,
				'tablet_default' => 3,
				'mobile_default' => 2,
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
					'library' => 'fa-solid',
				],
				'condition' => [
					'navigation' => ['arrow', 'both']
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
					'library' => 'fa-solid',
				],
				'condition' => [
					'navigation' => ['arrow', 'both']
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
			'_section_style_item',
			[
				'label' => esc_html__( 'Carousel Item', 'wpzoom-elementor-addons' ),
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
					'size' => 10,
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
				'size_units' => ['px'],
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
				'size_units' => ['px'],
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
				'size_units' => ['px', '%'],
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
				'size_units' => ['px', '%'],
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
				'size_units' => ['px', 'em'],
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
				'size_units' => ['px'],
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
				'size_units' => ['px'],
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
				'size_units' => ['px'],
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
				'size_units' => ['px'],
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

		if ( empty( $settings['slides'] ) ) {
			return;
		}

		?><div class="wpzjs-slick wpz-slick wpz-slick--carousel">

			<?php foreach ( $settings[ 'slides' ] as $slide ) :
				$image = wp_get_attachment_image_url( $slide[ 'image' ][ 'id' ], $settings[ 'thumbnail_size' ] );
				$image_alt = ! empty( $slide[ 'title' ] ) ? $slide[ 'title' ] : get_post_meta( $slide[ 'image' ][ 'id' ], '_wp_attachment_image_alt', true );

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
							<img class="wpz-slick-img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
						<?php endif; ?>

						<?php if ( $slide[ 'title' ] || $slide[ 'subtitle' ] ) : ?>
							<div class="wpz-slick-content">
								<?php if ( $slide[ 'title' ] ) : ?>
									<h2 class="wpz-slick-title"><?php echo WPZOOM_Elementor_Widgets::custom_kses( $slide[ 'title' ] ); ?></h2>
								<?php endif; ?>
								<?php if ( $slide[ 'subtitle' ] ) : ?>
									<p class="wpz-slick-subtitle"><?php echo WPZOOM_Elementor_Widgets::custom_kses( $slide[ 'subtitle' ] ); ?></p>
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

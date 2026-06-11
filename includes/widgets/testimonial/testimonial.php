<?php
namespace WPZOOMElementorWidgets;

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use \Elementor\Utils;
use \Elementor\Widget_Base;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - Testimonial Widget.
 *
 * Elementor widget that inserts a customizable customer testimonial.
 *
 * @since 1.0.0
 */
class Testimonial extends Widget_Base {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-testimonial', plugins_url( 'frontend.css', __FILE__ ), [], WPZOOM_EL_ADDONS_VER );
		// Register only — widgets pull it in via get_style_depends() when actually rendered.
		if ( ! wp_script_is( 'font-awesome-pro' ) && ! wp_style_is( 'font-awesome-5-all', 'registered' ) ) {
			wp_register_style(
				'font-awesome-5-all',
				self::get_fa_asset_url( 'all' ),
				[],
				WPZOOM_EL_ADDONS_VER
			);
		}
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
		return 'wpzoom-elementor-addons-testimonial';
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
		return esc_html__( 'Testimonial', 'wpzoom-elementor-addons' );
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
		return 'eicon-testimonial';
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
			'wpzoom-elementor-addons-css-frontend-testimonial',
			'font-awesome-5-all',
			'font-awesome-4-shim'
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
			'font-awesome-4-shim'
		];
	}

	private static function get_fa_asset_url( $filename, $ext_type = 'css', $add_suffix = true ) {
		static $is_test_mode = null;
		if ( null === $is_test_mode ) {
			$is_test_mode = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'ELEMENTOR_TESTS' ) && ELEMENTOR_TESTS;
		}
		$url = ELEMENTOR_ASSETS_URL . 'lib/font-awesome/' . $ext_type . '/' . $filename;
		if ( ! $is_test_mode && $add_suffix ) {
			$url .= '.min';
		}

		return $url . '.' . $ext_type;
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
  		$this->start_controls_section(
  			'wpz_section_testimonial_image',
  			[
  				'label' => esc_html__( 'Testimonial Image', 'wpzoom-elementor-addons' )
  			]
  		);

		$this->add_control(
			'wpz_testimonial_enable_avatar',
			[
				'label' => esc_html__( 'Display Avatar?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'image',
			[
				'label' => esc_html__( 'Testimonial Avatar', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'wpz_testimonial_enable_avatar' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'		=> 'image',
				'default'	=> 'thumbnail',
				'condition' => [
					'image[url]!' => '',
					'wpz_testimonial_enable_avatar' => 'yes',
				],
			]
		);

		$this->end_controls_section();

  		$this->start_controls_section(
  			'wpz_section_testimonial_content',
  			[
  				'label' => esc_html__( 'Testimonial Content', 'wpzoom-elementor-addons' )
  			]
  		);

		$this->add_control(
			'wpz_testimonial_name',
			[
				'label' => esc_html__( 'User Name', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'John Doe', 'wpzoom-elementor-addons' ),
				'dynamic' => [ 'active' => true ]
			]
		);

		$this->add_control(
			'wpz_testimonial_company_title',
			[
				'label' => esc_html__( 'Company Name', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Codetic', 'wpzoom-elementor-addons' ),
				'dynamic' => [ 'active' => true ]
			]
		);

		$this->add_control(
			'wpz_testimonial_description',
			[
				'label' => esc_html__( 'Testimonial Description', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Add testimonial description here. Edit and place your own text.', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'content_height',
			[
				'label' => esc_html__( 'Description Height', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units'	=> ['px', '%', 'em'],
				'range' => [
					'px' => [ 'max' => 300 ],
					'%'	=> [ 'max'	=> 100 ]
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wpz_testimonial_enable_rating',
			[
				'label' => esc_html__( 'Display Rating?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
		  'wpz_testimonial_rating_number',
		  [
			 'label'       => esc_html__( 'Rating Number', 'wpzoom-elementor-addons' ),
			 'type' => Controls_Manager::SELECT,
			 'default' => 'rating-five',
			 'options' => [
			 	'rating-one'  => esc_html__( '1', 'wpzoom-elementor-addons' ),
			 	'rating-two' => esc_html__( '2', 'wpzoom-elementor-addons' ),
			 	'rating-three' => esc_html__( '3', 'wpzoom-elementor-addons' ),
			 	'rating-four' => esc_html__( '4', 'wpzoom-elementor-addons' ),
			 	'rating-five'   => esc_html__( '5', 'wpzoom-elementor-addons' ),
			 ],
			'condition' => [
				'wpz_testimonial_enable_rating' => 'yes',
			],
		  ]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'wpz_section_testimonial_styles_general',
			[
				'label' => esc_html__( 'Testimonial Styles', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'wpz_testimonial_style',
			[
				'label'		=> esc_html__( 'Select Style', 'wpzoom-elementor-addons' ),
				'type'		=> Controls_Manager::SELECT,
				'default'	=> 'default-style',
				'options'	=> [
					'default-style'						=> esc_html__( 'Default', 'wpzoom-elementor-addons' ),
					'classic-style'						=> esc_html__( 'Classic', 'wpzoom-elementor-addons' ),
					'middle-style'						=> esc_html__( 'Content | Icon/Image | Bio', 'wpzoom-elementor-addons' ),
					'icon-img-left-content'				=> esc_html__( 'Icon/Image | Content', 'wpzoom-elementor-addons' ),
					'icon-img-right-content'			=> esc_html__( 'Content | Icon/Image', 'wpzoom-elementor-addons' ),
					'content-top-icon-title-inline'		=> esc_html__( 'Content Top | Icon Title Inline', 'wpzoom-elementor-addons' ),
					'content-bottom-icon-title-inline'	=> esc_html__( 'Content Bottom | Icon Title Inline', 'wpzoom-elementor-addons' )
				]
			]
		);

		$this->add_control(
			'wpz_testimonial_is_gradient_background',
			[
				'label' => esc_html__( 'Use Gradient Background', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wpz_testimonial_background',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-item' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'wpz_testimonial_is_gradient_background' => ''
				]
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'wpz_testimonial_gradient_background',
				'label' => esc_html__( 'Gradient Background', 'wpzoom-elementor-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpz-testimonial-item',
				'condition' => [
					'wpz_testimonial_is_gradient_background' => 'yes'
				]
			]
		);

		$this->add_control(
			'wpz_testimonial_alignment',
			[
				'label' => esc_html__( 'Layout Alignment', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => true,
				'options' => [
					'default' => [
						'title' => esc_html__( 'Default', 'wpzoom-elementor-addons' ),
						'icon' => 'fa fa-ban',
					],
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
				'default' => 'default',
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .wpz-testimonial-image' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wpz_testimonial_user_display_block',
			[
				'label' => esc_html__( 'Display User & Company Block?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => '',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'wpz_section_testimonial_image_styles',
			[
				'label' => esc_html__( 'Testimonial Image Style', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'	=> [
					'wpz_testimonial_enable_avatar'	=> 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'wpz_testimonial_image_width',
			[
				'label' => esc_html__( 'Image Width', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 150,
					'unit' => 'px',
				],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'size_units' => [ '%', 'px' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-image figure > img' => 'width:{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wpz_testimonial_max_image_width',
			[
				'label' => esc_html__( 'Image Max Width', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-image' => 'max-width:{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wpz_testimonial_image_margin',
			[
				'label' => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-image img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wpz_testimonial_image_padding',
			[
				'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-image img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'wpz_testimonial_image_border',
				'label' => esc_html__( 'Border', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-testimonial-image img',
			]
		);

		$this->add_control(
			'wpz_testimonial_image_rounded',
			[
				'label' => esc_html__( 'Rounded Avatar?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'testimonial-avatar-rounded',
				'default' => '',
			]
		);

		$this->add_control(
			'wpz_testimonial_image_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-image img' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
				],
				'condition' => [
					'wpz_testimonial_image_rounded!' => 'testimonial-avatar-rounded',
				],
			]
		);

		$this->end_controls_section();

		// color, Typography & Spacing
		$this->start_controls_section(
			'wpz_section_testimonial_typography',
			[
				'label' => esc_html__( 'Color, Typography &amp; Spacing', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'wpz_testimonial_name_heading',
			[
				'label' => esc_html__( 'User Name', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'wpz_testimonial_name_color',
			[
				'label' => esc_html__( 'User Name Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#272727',
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-user' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'wpz_testimonial_name_typography',
				'selector' => '{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-user',
			]
		);

		$this->add_control(
			'wpz_testimonial_name_margin',
			[
				'label' => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-user' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wpz_testimonial_company_heading',
			[
				'label' 	=> esc_html__( 'Company Name', 'wpzoom-elementor-addons' ),
				'type' 		=> Controls_Manager::HEADING,
				'separator'	=> 'before'
			]
		);

		$this->add_control(
			'wpz_testimonial_company_color',
			[
				'label' => esc_html__( 'Company Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#272727',
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-user-company' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'wpz_testimonial_position_typography',
				'selector' => '{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-user-company',
			]
		);

		$this->add_control(
			'wpz_testimonial_company_margin',
			[
				'label' => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-user-company' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wpz_testimonial_description_heading',
			[
				'label' => esc_html__( 'Testimonial Text', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator'	=> 'before'
			]
		);

		$this->add_control(
			'wpz_testimonial_description_color',
			[
				'label' => esc_html__( 'Testimonial Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#7a7a7a',
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
			 	'name' => 'wpz_testimonial_description_typography',
				'selector' => '{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-text',
			]
		);

		$this->add_control(
			'wpz_testimonial_description_margin',
			[
				'label' => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content .wpz-testimonial-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wpz_testimonial_rating_heading',
			[
				'label' => esc_html__( 'Rating', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator'	=> 'before'
			]
		);

		$this->add_control(
			'wpz_testimonial_rating_item_distance',
			[
				'label' => esc_html__( 'Distance Between Rating Item', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content .testimonial-star-rating li' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wpz_testimonial_rating_margin',
			[
				'label' => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-content .testimonial-star-rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'wpz_section_testimonial_quotation_typography',
			[
				'label' => esc_html__( 'Quotation Style', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'wpz_testimonial_quotation_color',
			[
				'label' => esc_html__( 'Quotation Mark Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.15)',
				'selectors' => [
					'{{WRAPPER}} .wpz-testimonial-quote' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'wpz_testimonial_quotation_typography',
				'selector' => '{{WRAPPER}} .wpz-testimonial-quote',
			]
		);

		$this->add_responsive_control(
			'wpz_testimonial_quotation_top',
			[
				'label' => esc_html__( 'Quotation Postion From Top', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 5,
					'unit' => '%',
				],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					]
				],
				'size_units' => [ '%' ],
				'selectors' => [
					'{{WRAPPER}} span.wpz-testimonial-quote' => 'top:{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wpz_testimonial_quotation_right',
			[
				'label' => esc_html__( 'Quotation Postion From Right', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 5,
					'unit' => '%',
				],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					]
				],
				'size_units' => [ '%' ],
				'selectors' => [
					'{{WRAPPER}} span.wpz-testimonial-quote' => 'right:{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render Testimonial Image.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function render_testimonial_image() {
		$settings = $this->get_settings();
		$image = Group_Control_Image_Size::get_attachment_image_html( $settings );

		if( ! empty( $image ) && ! empty( $settings[ 'wpz_testimonial_enable_avatar' ] ) ) {
			ob_start();

			?>
			<div class="wpz-testimonial-image">
				<?php if ( 'yes' == $settings[ 'wpz_testimonial_enable_avatar' ] ) : ?>
					<figure><?php echo Group_Control_Image_Size::get_attachment_image_html( $settings ); ?></figure>
				<?php endif; ?>
			</div>
			<?php

			echo ob_get_clean();
		}
	}

	/**
	 * Render Testimonial Rating.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function render_testimonial_rating() {
		$settings = $this->get_settings_for_display( 'wpz_testimonial_enable_rating' );

		if ( $settings == 'yes' ) :
			ob_start();

			?>
			<ul class="testimonial-star-rating">
				<li><i class="fas fa-star" aria-hidden="true"></i></li>
				<li><i class="fas fa-star" aria-hidden="true"></i></li>
				<li><i class="fas fa-star" aria-hidden="true"></i></li>
				<li><i class="fas fa-star" aria-hidden="true"></i></li>
				<li><i class="fas fa-star" aria-hidden="true"></i></li>
			</ul>
			<?php

			echo ob_get_clean();
		endif;
	}

	/**
	 * Render User Name and Company.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function render_user_name_and_company() {
		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings[ 'wpz_testimonial_name' ] ) ) :
			?><p <?php echo $this->get_render_attribute_string( 'wpz_testimonial_user' ); ?>><?php echo esc_html( $settings[ 'wpz_testimonial_name' ] ); ?></p><?php
		endif;

		if ( ! empty( $settings[ 'wpz_testimonial_company_title' ] ) ) :
			?><p class="wpz-testimonial-user-company"><?php echo esc_html( $settings[ 'wpz_testimonial_company_title' ] ); ?></p><?php
		endif;
	}

	/**
	 * Render Testimonial Quote.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function testimonial_quote() {
		echo '<span class="wpz-testimonial-quote"></span>';
	}

	/**
	 * Render Testimonial Description.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function testimonial_desc() {
		$settings = $this->get_settings_for_display();

		echo '<div class="wpz-testimonial-text">' . wp_kses_post( wpautop( $settings[ 'wpz_testimonial_description' ] ) ) . '</div>';
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
		$rating = $this->get_settings_for_display( 'wpz_testimonial_enable_rating' );

		$this->add_render_attribute(
			'wpz_testimonial_wrap',
			[
				'id'	=> 'wpz-testimonial-' . esc_attr( $this->get_id() ),
				'class'	=> [
					'wpz-testimonial-item',
					'clearfix',
					$this->get_settings( 'wpz_testimonial_image_rounded' ),
					esc_attr( $settings[ 'wpz_testimonial_style' ] ),
				]
			]
		);

		if ( $rating == 'yes' ) {
			$this->add_render_attribute( 'wpz_testimonial_wrap', 'class', $this->get_settings( 'wpz_testimonial_rating_number' ) );
		}

		$this->add_render_attribute('wpz_testimonial_user', 'class', 'wpz-testimonial-user');

		if ( ! empty( $settings[ 'wpz_testimonial_user_display_block' ] ) ) {
			$this->add_render_attribute( 'wpz_testimonial_user', 'style', 'display: block; float: none;' );
		}

		?>
		<div <?php echo $this->get_render_attribute_string( 'wpz_testimonial_wrap' ); ?>>

			<?php if ( 'classic-style' == $settings[ 'wpz_testimonial_style' ] ) { ?>
				<div class="wpz-testimonial-content">
					<?php $this->testimonial_desc(); ?>

					<div class="clearfix">
						<?php $this->render_user_name_and_company(); ?>
					</div>

					<?php $this->render_testimonial_rating( $settings ); ?>
				</div>

				<?php $this->render_testimonial_image(); ?>
			<?php } ?>

			<?php if ( 'middle-style' == $settings[ 'wpz_testimonial_style' ] ) { ?>
				<div class="wpz-testimonial-content">
					<?php $this->testimonial_desc(); ?>

					<?php $this->render_testimonial_image(); ?>

					<div class="clearfix">
						<?php $this->render_user_name_and_company(); ?>
					</div>

					<?php $this->render_testimonial_rating( $settings ); ?>
				</div>
			<?php } ?>

			<?php if ( 'default-style' == $settings[ 'wpz_testimonial_style' ] ) { ?>
				<?php $this->render_testimonial_image(); ?>
				<div class="wpz-testimonial-content">
					<?php
						$this->testimonial_desc();
						$this->render_testimonial_rating( $settings );
						$this->render_user_name_and_company();
					?>
				</div>
			<?php } ?>

			<?php if ( 'icon-img-left-content' == $settings[ 'wpz_testimonial_style' ] ) { ?>
				<?php $this->render_testimonial_image(); ?>

				<div class="wpz-testimonial-content">
					<?php
						$this->testimonial_desc();
						$this->render_testimonial_rating( $settings );
					?>

					<div class="bio-text clearfix">
						<?php $this->render_user_name_and_company(); ?>
					</div>
				</div>
			<?php } ?>

			<?php if ( 'icon-img-right-content' == $settings[ 'wpz_testimonial_style' ] ) { ?>
				<?php $this->render_testimonial_image(); ?>

				<div class="wpz-testimonial-content">
					<?php
						$this->testimonial_desc();
						$this->render_testimonial_rating( $settings );
					?>

					<div class="bio-text-right"><?php $this->render_user_name_and_company(); ?></div>
				</div>
			<?php } ?>

			<?php if ( 'content-top-icon-title-inline' == $settings[ 'wpz_testimonial_style' ] ) { ?>
				<div class="wpz-testimonial-content wpz-testimonial-inline-bio">
					<?php $this->render_testimonial_image(); ?>

					<div class="bio-text"><?php $this->render_user_name_and_company(); ?></div>

					<?php $this->render_testimonial_rating( $settings ); ?>
				</div>

				<div class="wpz-testimonial-content">
					<?php $this->testimonial_desc(); ?>
				</div>
			<?php } ?>

			<?php if ( 'content-bottom-icon-title-inline' == $settings[ 'wpz_testimonial_style' ] ) { ?>
				<div class="wpz-testimonial-content">
					<?php $this->testimonial_desc(); ?>
				</div>

				<div class="wpz-testimonial-content wpz-testimonial-inline-bio">
					<?php $this->render_testimonial_image(); ?>

					<div class="bio-text"><?php $this->render_user_name_and_company(); ?></div>

					<?php $this->render_testimonial_rating( $settings ); ?>
				</div>
			<?php } ?>

			<?php $this->testimonial_quote(); ?>

		</div><?php
	}

	/**
	 * Content Template.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function content_template() {}
}

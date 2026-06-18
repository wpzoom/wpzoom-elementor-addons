<?php
namespace WPZOOMElementorWidgets;

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;

use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - Team Members Widget.
 *
 * Elementor widget that inserts a customizable list of team members.
 *
 * @since 1.0.0
 */
class Team_Members extends Widget_Base {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-team-members', plugins_url( 'css/frontend.css', __FILE__ ), [], WPZOOM_EL_ADDONS_VER );
		wp_register_style( 
			'wpzoom-elementor-addons-awesomefont-brands', 
			plugins_url( 'css/brands.min.css', __FILE__ ), 
			[], 
			WPZOOM_EL_ADDONS_VER 
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
		return 'wpzoom-elementor-addons-team-members';
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
		return esc_html__( 'Team Members', 'wpzoom-elementor-addons' );
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
		return 'eicon-person';
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
			'font-awesome-5-all',
			'font-awesome-4-shim',
            'elementor-icons-fa-regular',
            'elementor-icons-fa-solid',
			'wpzoom-elementor-addons-css-frontend-team-members',
			'wpzoom-elementor-addons-awesomefont-brands'
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
			'font-awesome-4-shim',
		];
	}

	/**
	 * Get profile names.
	 *
	 * Retrieve the list of profile names.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Profile names.
	 */
	protected static function get_profile_names() {
		return [
			'500px'          => esc_html__( '500px', 'wpzoom-elementor-addons' ),
			'apple'          => esc_html__( 'Apple', 'wpzoom-elementor-addons' ),
			'behance'        => esc_html__( 'Behance', 'wpzoom-elementor-addons' ),
			'bitbucket'      => esc_html__( 'BitBucket', 'wpzoom-elementor-addons' ),
			'codepen'        => esc_html__( 'CodePen', 'wpzoom-elementor-addons' ),
			'delicious'      => esc_html__( 'Delicious', 'wpzoom-elementor-addons' ),
			'deviantart'     => esc_html__( 'DeviantArt', 'wpzoom-elementor-addons' ),
			'digg'           => esc_html__( 'Digg', 'wpzoom-elementor-addons' ),
			'dribbble'       => esc_html__( 'Dribbble', 'wpzoom-elementor-addons' ),
			'email'          => esc_html__( 'Email', 'wpzoom-elementor-addons' ),
			'facebook'       => esc_html__( 'Facebook', 'wpzoom-elementor-addons' ),
			'flickr'         => esc_html__( 'Flicker', 'wpzoom-elementor-addons' ),
			'foursquare'     => esc_html__( 'FourSquare', 'wpzoom-elementor-addons' ),
			'github'         => esc_html__( 'Github', 'wpzoom-elementor-addons' ),
			'houzz'          => esc_html__( 'Houzz', 'wpzoom-elementor-addons' ),
			'instagram'      => esc_html__( 'Instagram', 'wpzoom-elementor-addons' ),
			'jsfiddle'       => esc_html__( 'JS Fiddle', 'wpzoom-elementor-addons' ),
			'linkedin'       => esc_html__( 'LinkedIn', 'wpzoom-elementor-addons' ),
			'medium'         => esc_html__( 'Medium', 'wpzoom-elementor-addons' ),
			'pinterest'      => esc_html__( 'Pinterest', 'wpzoom-elementor-addons' ),
			'product-hunt'   => esc_html__( 'Product Hunt', 'wpzoom-elementor-addons' ),
			'reddit'         => esc_html__( 'Reddit', 'wpzoom-elementor-addons' ),
			'slideshare'     => esc_html__( 'Slide Share', 'wpzoom-elementor-addons' ),
			'snapchat'       => esc_html__( 'Snapchat', 'wpzoom-elementor-addons' ),
			'soundcloud'     => esc_html__( 'SoundCloud', 'wpzoom-elementor-addons' ),
			'spotify'        => esc_html__( 'Spotify', 'wpzoom-elementor-addons' ),
			'stack-overflow' => esc_html__( 'StackOverflow', 'wpzoom-elementor-addons' ),
			'tiktok'         => esc_html__( 'TikTok', 'wpzoom-elementor-addons' ),
            'tripadvisor'    => esc_html__( 'TripAdvisor', 'wpzoom-elementor-addons' ),
			'tumblr'         => esc_html__( 'Tumblr', 'wpzoom-elementor-addons' ),
			'twitch'         => esc_html__( 'Twitch', 'wpzoom-elementor-addons' ),
			'twitter'        => esc_html__( 'Twitter', 'wpzoom-elementor-addons' ),
			'vimeo'          => esc_html__( 'Vimeo', 'wpzoom-elementor-addons' ),
			'vk'             => esc_html__( 'VK', 'wpzoom-elementor-addons' ),
			'website'        => esc_html__( 'Website', 'wpzoom-elementor-addons' ),
			'whatsapp'       => esc_html__( 'WhatsApp', 'wpzoom-elementor-addons' ),
			'wordpress'      => esc_html__( 'WordPress', 'wpzoom-elementor-addons' ),
			'xing'           => esc_html__( 'Xing', 'wpzoom-elementor-addons' ),
			'x-twitter'      => esc_html__( 'X', 'wpzoom-elementor-addons' ),
			'yelp'           => esc_html__( 'Yelp', 'wpzoom-elementor-addons' ),
			'youtube'        => esc_html__( 'YouTube', 'wpzoom-elementor-addons' ),
			'substack'       => esc_html__( 'Substack', 'wpzoom-elementor-addons' ),
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
	 * Register Controls.
	 *
	 * Registers all the controls for this widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'_section_info',
			[
				'label' => esc_html__( 'Information', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'image',
			[
				'label' => esc_html__( 'Photo', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'thumbnail',
				'default' => 'large',
				'separator' => 'none',
			]
		);

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Name', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'type' => Controls_Manager::TEXT,
				'default' => 'Team Member Name',
				'placeholder' => esc_html__( 'Team Member Name', 'wpzoom-elementor-addons' ),
				'separator' => 'before',
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$this->add_control(
			'job_title',
			[
				'label' => esc_html__( 'Job Title', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Officer', 'wpzoom-elementor-addons' ),
				'placeholder' => esc_html__( 'Team Member Job Title', 'wpzoom-elementor-addons' ),
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$this->add_control(
			'bio',
			[
				'label' => esc_html__( 'Short Bio', 'wpzoom-elementor-addons' ),
				'description' => sprintf( __( 'This input field has support for the following HTML tags: %1$s', 'wpzoom-elementor-addons' ), '<code>' . esc_html( '<' . implode( '>,<', array_keys( WPZOOM_Elementor_Widgets::get_allowed_html_tags() ) ) . '>' ) . '</code>' ),
				'type' => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Write something about the team member', 'wpzoom-elementor-addons' ),
				'rows' => 5,
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => esc_html__( 'Title HTML Tag', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'span' => 'span',
					'p' => 'p',
				],
				'default' => 'h2',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
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
					'justify' => [
						'title' => esc_html__( 'Justify', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};'
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_social',
			[
				'label' => esc_html__( 'Social Profiles', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'name',
			[
				'label' => esc_html__( 'Profile Name', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'select2options' => [
					'allowClear' => false,
				],
				'options' => self::get_profile_names()
			]
		);

		$repeater->add_control(
			'link', [
				'label' => esc_html__( 'Profile Link', 'wpzoom-elementor-addons' ),
				'placeholder' => esc_html__( 'Add your profile link', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::URL,
				'label_block' => true,
				'autocomplete' => false,
				'show_external' => false,
				'condition' => [
					'name!' => 'email'
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'email', [
				'label' => esc_html__( 'Email Address', 'wpzoom-elementor-addons' ),
				'placeholder' => esc_html__( 'Add your email address', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => false,
				'input_type' => 'email',
				'condition' => [
					'name' => 'email'
				],
				'dynamic' => [
					'active' => true,
				]
			]
		);

		$repeater->add_control(
			'customize',
			[
				'label' => esc_html__( 'Want To Customize?', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'style_transfer' => true,
			]
		);

		$repeater->start_controls_tabs(
			'_tab_icon_colors',
			[
				'condition' => ['customize' => 'yes']
			]
		);
		$repeater->start_controls_tab(
			'_tab_icon_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > {{CURRENT_ITEM}}' => 'color: {{VALUE}}',
				],
				'condition' => ['customize' => 'yes'],
				'style_transfer' => true,
			]
		);

		$repeater->add_control(
			'bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > {{CURRENT_ITEM}}' => 'background-color: {{VALUE}}',
				],
				'condition' => ['customize' => 'yes'],
				'style_transfer' => true,
			]
		);

		$repeater->end_controls_tab();
		$repeater->start_controls_tab(
			'_tab_icon_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$repeater->add_control(
			'hover_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > {{CURRENT_ITEM}}:hover, {{WRAPPER}} .wpz-member-links > {{CURRENT_ITEM}}:focus' => 'color: {{VALUE}}',
				],
				'condition' => ['customize' => 'yes'],
				'style_transfer' => true,
			]
		);

		$repeater->add_control(
			'hover_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > {{CURRENT_ITEM}}:hover, {{WRAPPER}} .wpz-member-links > {{CURRENT_ITEM}}:focus' => 'background-color: {{VALUE}}',
				],
				'condition' => ['customize' => 'yes'],
				'style_transfer' => true,
			]
		);

		$repeater->add_control(
			'hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > {{CURRENT_ITEM}}:hover, {{WRAPPER}} .wpz-member-links > {{CURRENT_ITEM}}:focus' => 'border-color: {{VALUE}}',
				],
				'condition' => ['customize' => 'yes'],
				'style_transfer' => true,
			]
		);

		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();

		$this->add_control(
			'profiles',
			[
				'show_label' => false,
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '<# print(name.slice(0,1).toUpperCase() + name.slice(1)) #>',
				'default' => [
					[
						'link' => ['url' => 'https://facebook.com/'],
						'name' => 'facebook'
					],
					[
						'link' => ['url' => 'https://x.com/'],
						'name' => 'x-twitter'
					],
					[
						'link' => ['url' => 'https://linkedin.com/'],
						'name' => 'linkedin'
					]
				],
			]
		);

		$this->add_control(
			'show_profiles',
			[
				'label' => esc_html__( 'Show Profiles', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'separator' => 'before',
				'style_transfer' => true,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_button',
			[
				'label' => esc_html__( 'Details Button', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_details_button',
			[
				'label' => esc_html__( 'Show Button', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default' => '',
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'button_position',
			[
				'label' => esc_html__( 'Position', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'after',
				'style_transfer' => true,
				'options' => [
					'before' => esc_html__( 'Before Social Icons', 'wpzoom-elementor-addons' ),
					'after' => esc_html__( 'After Social Icons', 'wpzoom-elementor-addons' ),
				],
				'condition' => [
					'show_details_button' => 'yes',
				]
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => esc_html__( 'Text', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Show Details', 'wpzoom-elementor-addons' ),
				'placeholder' => esc_html__( 'Type button text here', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'show_details_button' => 'yes',
				]
			]
		);

		$this->add_control(
			'button_link',
			[
				'label' => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'show_details_button' => 'yes',
				],
				'default' => [
					'url' => '#',
				]
			]
		);

		$this->add_control(
			'button_icon',
			[
				'type' => Controls_Manager::ICONS,
				'label_block' => true,
				'show_label' => false,
				'condition' => [
					'show_details_button' => 'yes',
				]
			]
		);

		$this->add_control(
			'button_icon_position',
			[
				'label' => esc_html__( 'Icon Position', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options' => [
					'before' => [
						'title' => esc_html__( 'Before', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'after' => [
						'title' => esc_html__( 'After', 'wpzoom-elementor-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'after',
				'toggle' => false,
				'style_transfer' => true,
				'condition' => [
					'show_details_button' => 'yes',
					'button_icon[value]!' => ''
				]
			]
		);

		$this->add_control(
			'button_icon_spacing',
			[
				'label' => esc_html__( 'Icon Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10
				],
				'condition' => [
					'show_details_button' => 'yes',
					'button_icon[value]!' => ''
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-btn--icon-before .wpz-btn-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpz-btn--icon-after .wpz-btn-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register Style Controls.
	 *
	 * Registers the style controls.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	protected function register_style_controls() {
		$this->start_controls_section(
			'_section_style_image',
			[
				'label' => esc_html__( 'Photo', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label' => esc_html__( 'Width', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%'],
				'range' => [
					'%' => [
						'min' => 20,
						'max' => 100,
					],
					'px' => [
						'min' => 100,
						'max' => 700,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-figure' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label' => esc_html__( 'Height', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 700,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-figure' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-figure' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'image_padding',
			[
				'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-figure img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'image_border',
				'selector' => '{{WRAPPER}} .wpz-member-figure img'
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-figure img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'image_box_shadow',
				'exclude' => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .wpz-member-figure img'
			]
		);

		$this->add_control(
			'image_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-figure img' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_content',
			[
				'label' => esc_html__( 'Name, Job Title & Bio', 'wpzoom-elementor-addons' ),
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
					'{{WRAPPER}} .wpz-member-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'_heading_title',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Name', 'wpzoom-elementor-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-name' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .wpz-member-name',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'title_text_shadow',
				'selector' => '{{WRAPPER}} .wpz-member-name',
			]
		);

		$this->add_control(
			'_heading_job_title',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Job Title', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'job_title_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-position' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'job_title_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-position' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'job_title_typography',
				'selector' => '{{WRAPPER}} .wpz-member-position',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'job_title_text_shadow',
				'selector' => '{{WRAPPER}} .wpz-member-position',
			]
		);

		$this->add_control(
			'_heading_bio',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Short Bio', 'wpzoom-elementor-addons' ),
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'bio_spacing',
			[
				'label' => esc_html__( 'Bottom Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-bio' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'bio_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-bio' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'bio_typography',
				'selector' => '{{WRAPPER}} .wpz-member-bio',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'bio_text_shadow',
				'selector' => '{{WRAPPER}} .wpz-member-bio',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_social',
			[
				'label' => esc_html__( 'Social Icons', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'links_spacing',
			[
				'label' => esc_html__( 'Right Spacing', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'links_padding',
			[
				'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a' => 'padding: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'links_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'links_border',
				'selector' => '{{WRAPPER}} .wpz-member-links > a'
			]
		);

		$this->add_responsive_control(
			'links_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( '_tab_links_colors' );
		$this->start_controls_tab(
			'_tab_links_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'links_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'links_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'_tab_links_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'links_hover_color',
			[
				'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a:hover, {{WRAPPER}} .wpz-member-links > a:focus' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'links_hover_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a:hover, {{WRAPPER}} .wpz-member-links > a:focus' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'links_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-member-links > a:hover, {{WRAPPER}} .wpz-member-links > a:focus' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'links_border_border!' => '',
				]
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_style_button',
			[
				'label' => esc_html__( 'Details Button', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'label' => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .wpz-btn',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .wpz-btn',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpz-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .wpz-btn',
			]
		);

		$this->add_control(
			'hr',
			[
				'type' => Controls_Manager::DIVIDER,
				'style' => 'thick',
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
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .wpz-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-btn' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .wpz-btn:hover, {{WRAPPER}} .wpz-btn:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpzw-btn:hover, {{WRAPPER}} .wpz-btn:focus' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .wpz-btn:hover, {{WRAPPER}} .wpz-btn:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Overriding the parent method
	 *
	 * Add inline editing attributes.
	 *
	 * Define specific area in the element to be editable inline. The element can have several areas, with this method
	 * you can set the area inside the element that can be edited inline. You can also define the type of toolbar the
	 * user will see, whether it will be a basic toolbar or an advanced one.
	 *
	 * Note: When you use wysiwyg control use the advanced toolbar, with textarea control use the basic toolbar. Text
	 * control should not have toolbar.
	 *
	 * PHP usage (inside `Widget_Base::render()` method):
	 *
	 *    $this->add_inline_editing_attributes( 'text', 'advanced' );
	 *    echo '<div ' . $this->get_render_attribute_string( 'text' ) . '>' . $this->get_settings( 'text' ) . '</div>';
	 *
	 * @since 1.8.0
	 * @access protected
	 *
	 * @param string $key     Element key.
	 * @param string $toolbar Optional. Toolbar type. Accepted values are `advanced`, `basic` or `none`. Default is
	 *                        `basic`.
	 * @param string $setting_key Additional settings key in case $key != $setting_key
	 */
	public function add_inline_editing_attributes( $key, $toolbar = 'basic', $setting_key = '' ) {
		if ( ! Plugin::instance()->editor->is_edit_mode() ) {
			return;
		}

		if ( empty( $setting_key ) ) {
			$setting_key = $key;
		}

		$this->add_render_attribute( $key, [
			'class' => 'elementor-inline-editing',
			'data-elementor-setting-key' => $setting_key,
		] );

		if ( 'basic' !== $toolbar ) {
			$this->add_render_attribute( $key, [
				'data-elementor-inline-editing-toolbar' => $toolbar,
			] );
		}
	}

	/**
	 * Render button with icon
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args { old_icon, icon_pos, new_icon, text, link, class, text_class }
	 * @return void
	 */
	public function render_icon_button( $args = [] ) {
		$args = wp_parse_args( $args, [
			'old_icon'   => 'button_icon',
			'icon_pos'   => 'button_icon_position',
			'new_icon'   => 'button_selected_icon',
			'text'       => 'button_text',
			'link'       => 'button_link',
			'class'      => 'ha-btn ha-btn--link',
			'text_class' => 'ha-btn-text',
		] );

		$settings = $this->get_settings_for_display();
		$button_text = isset( $settings[ $args['text'] ] ) ? $settings[ $args['text'] ] : '';
		$has_new_icon = ( ! empty( $settings[ $args['new_icon'] ] ) && ! empty( $settings[ $args['new_icon'] ]['value'] ) ) ? true : false;
		$has_old_icon = ! empty( $settings[ $args['old_icon'] ] ) ? true : false;

		// Return as early as possible
		// Do not process anything if there is no icon and text
		if ( empty( $button_text ) && ! $has_new_icon && ! $has_old_icon ) {
			return;
		}

		$button_text = wp_kses_post( $button_text );

		$this->add_inline_editing_attributes( $args['text'], 'none' );
		$this->add_render_attribute( $args['text'], 'class', $args['text_class'] );

		$this->add_render_attribute( 'button', 'class', $args['class'] );
		$this->add_link_attributes( 'button', $settings[ $args['link'] ] );

		if ( $button_text && ( empty( $has_new_icon ) && empty( $has_old_icon ) ) ) :
			printf( '<a %1$s>%2$s</a>',
				$this->get_render_attribute_string( 'button' ),
				sprintf( '<span %1$s>%2$s</span>', $this->get_render_attribute_string( $args['text'] ), $button_text )
			);
		elseif ( empty( $button_text ) && ( ! empty( $has_old_icon ) || ! empty( $has_new_icon ) ) ) : ?>
			<a <?php $this->print_render_attribute_string( 'button' ); ?>><?php Icons_Manager::render_icon( $settings[ $args['new_icon'] ] ); ?></a>
		<?php elseif ( $button_text && ( ! empty( $has_old_icon ) || ! empty( $has_new_icon ) ) ) :
			if ( $settings[ $args['icon_pos'] ] === 'before' ) :
				$this->add_render_attribute( 'button', 'class', 'ha-btn--icon-before' );
				$button_text = sprintf( '<span %1$s>%2$s</span>', $this->get_render_attribute_string( $args['text'] ), $button_text );
				?>
				<a <?php $this->print_render_attribute_string( 'button' ); ?>><?php Icons_Manager::render_icon( $settings[ $args['new_icon'] ], ['class' => 'wpz-btn-icon'] ); ?> <?php echo $button_text; ?></a>
				<?php
			else :
				$this->add_render_attribute( 'button', 'class', 'ha-btn--icon-after' );
				$button_text = sprintf( '<span %1$s>%2$s</span>', $this->get_render_attribute_string( $args['text'] ), $button_text );
				?>
				<a <?php $this->print_render_attribute_string( 'button' ); ?>><?php echo $button_text; ?> <?php Icons_Manager::render_icon( $settings[ $args['new_icon'] ], ['class' => 'wpz-btn-icon'] ); ?></a>
				<?php
			endif;
		endif;
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

		$button_position = ! empty( $settings['button_position'] ) ? $settings['button_position'] : 'after';

		$show_button = false;
		if ( ! empty( $settings['show_details_button'] ) && $settings['show_details_button'] === 'yes'  ) {
			$show_button = true;
		}

		$this->add_inline_editing_attributes( 'title', 'basic' );
		$this->add_render_attribute( 'title', 'class', 'wpz-member-name' );

		$this->add_inline_editing_attributes( 'job_title', 'basic' );
		$this->add_render_attribute( 'job_title', 'class', 'wpz-member-position' );

		$this->add_inline_editing_attributes( 'bio' );
		$this->add_render_attribute( 'bio', 'class', 'wpz-member-bio' );
		?>

		<?php if ( $settings['image']['url'] || $settings['image']['id'] ) :
			$settings['hover_animation'] = 'disable-animation'; // hack to prevent image hover animation
			?>
			<figure class="wpz-member-figure">
				<?php echo Group_Control_Image_Size::get_attachment_image_html( $settings, 'thumbnail', 'image' ); ?>
			</figure>
		<?php endif; ?>

		<div class="wpz-member-body">
			<?php if ( $settings['title'] ) :
				// Security: Validate title_tag against whitelist to prevent XSS
				$allowed_title_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );
				$title_tag = in_array( $settings['title_tag'], $allowed_title_tags, true ) ? $settings['title_tag'] : 'h2';

				printf( '<%1$s %2$s>%3$s</%1$s>',
					tag_escape( $title_tag ),
					$this->get_render_attribute_string( 'title' ),
					WPZOOM_Elementor_Widgets::custom_kses( $settings[ 'title' ] )
				);
			endif; ?>

			<?php if ( $settings['job_title' ] ) : ?>
				<div <?php $this->print_render_attribute_string( 'job_title' ); ?>><?php echo WPZOOM_Elementor_Widgets::custom_kses( $settings[ 'job_title' ] ); ?></div>
			<?php endif; ?>

			<?php if ( $settings['bio'] ) : ?>
				<div <?php $this->print_render_attribute_string( 'bio' ); ?>>
					<p><?php echo WPZOOM_Elementor_Widgets::custom_kses( $settings[ 'bio' ] ); ?></p>
				</div>
			<?php endif; ?>

			<?php
			if ( $show_button && $button_position === 'before' ) {
				$this->render_icon_button( [ 'new_icon' => 'button_icon', 'old_icon' => '' ] );
			}
			?>

			<?php if ( $settings[ 'show_profiles' ] && is_array( $settings[ 'profiles' ] ) ) : ?>
				<div class="wpz-member-links">
					<?php
					foreach ( $settings['profiles'] as $profile ) :
						
						$icon = $profile['name'];

                        if ( $profile['name'] === 'email' ) {
                            $url = 'mailto:' . antispambot( $profile['email'] );
                        } else {
						  $url = esc_url( $profile['link']['url'] );
                        }

						if ( $profile['name'] === 'website' ) {
							$icon = 'globe fa';
						} elseif ( $profile['name'] === 'email' ) {
							$icon = 'envelope fab';
							$url = 'mailto:' . antispambot( $profile['email'] );
						} else {
							$icon .= ' fab';
						}

						if ( $profile['name'] === 'substack' ) {
							printf(
								'<a target="_blank" rel="noopener" href="%s" class="elementor-repeater-item-%s"><svg xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><title>Substack</title><path d="M22.539 8.242H1.46V5.406h21.08v2.836zM1.46 10.812V24L12 18.11 22.54 24V10.812H1.46zM22.54 0H1.46v2.836h21.08V0z"/></svg></a>',
								esc_url( $url ),
								esc_attr( $profile['_id'] )
							);
						} else {
							printf( '<a target="_blank" rel="noopener" href="%s" class="elementor-repeater-item-%s"><i class="fa-%s" aria-hidden="true"></i></a>',
								esc_url( $url ),
								esc_attr( $profile['_id'] ),
								esc_attr( $icon )
							);
						}
					endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			if ( $show_button && $button_position === 'after' ) {
				$this->render_icon_button( [ 'new_icon' => 'button_icon', 'old_icon' => '' ] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * JS Content Template.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	/*public function _content_template() {
		return '';
		?>
		<#
		view.addInlineEditingAttributes( 'title', 'basic' );
		view.addRenderAttribute( 'title', 'class', 'wpz-member-name' );

		view.addInlineEditingAttributes( 'job_title', 'basic' );
		view.addRenderAttribute( 'job_title', 'class', 'wpz-member-position' );

		view.addInlineEditingAttributes( 'bio', 'intermediate' );
		view.addRenderAttribute( 'bio', 'class', 'wpz-member-bio' );

		if ( settings.image.url || settings.image.id ) {
			var image = {
				id: settings.image.id,
				url: settings.image.url,
				size: settings.thumbnail_size,
				dimension: settings.thumbnail_custom_dimension,
				model: view.getEditModel()
			};

			var image_url = elementor.imagesManager.getImageUrl( image );
			#>
			<figure class="wpz-member-figure">
				<img src="{{ image_url }}">
			</figure>
		<# } #>
		<div class="wpz-member-body">
			<# if (settings.title) { #>
				<{{ settings.title_tag }} {{{ view.getRenderAttributeString( 'title' ) }}}>{{ settings.title }}</{{ settings.title_tag }}>
			<# } #>
			<# if (settings.job_title) { #>
				<div {{{ view.getRenderAttributeString( 'job_title' ) }}}>{{ settings.job_title }}</div>
			<# } #>
			<# if (settings.bio) { #>
				<div {{{ view.getRenderAttributeString( 'bio' ) }}}>
					<p>{{{ settings.bio }}}</p>
				</div>
			<# } #>

			<# if ( !_.isUndefined( settings['button_position'] ) && settings['button_position'] === 'before' ) {
				print( haGetButtonWithIcon( view, {newIcon: 'button_icon', oldIcon: ''} ) );
			} #>

			<# if (settings.show_profiles && _.isArray(settings.profiles)) { #>
				<div class="wpz-member-links">
					<# _.each(settings.profiles, function(profile, index) {
						var icon = profile.name,
							url = profile.link.url,
							linkKey = view.getRepeaterSettingKey( 'profile', 'profiles', index);

						if (profile.name === 'website') {
							icon = 'globe';
						} else if (profile.name === 'email') {
							icon = 'envelope'
							url = 'mailto:' + profile.email;
						}

						view.addRenderAttribute( linkKey, 'class', 'elementor-repeater-item-' + profile._id );
						view.addRenderAttribute( linkKey, 'href', url ); #>
						<a {{{view.getRenderAttributeString( linkKey )}}}><i class="fa fab fa-{{{icon}}}"></i></a>
					<# }); #>
				</div>
			<# } #>

			<# if ( !_.isUndefined( settings['button_position'] ) && settings['button_position'] === 'after' ) {
				print( haGetButtonWithIcon( view, {newIcon: 'button_icon', oldIcon: ''} ) );
			} #>
		</div>
		<?php
	}*/
}

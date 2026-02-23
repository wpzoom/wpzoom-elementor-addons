<?php
namespace WPZOOMElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Embed;
use Elementor\Icons_Manager;

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
class Slider_Pro extends Widget_Base {
	
	/**
	 * @var \WP_Query
	 */
	private $query = null;

	/**
	 * $post_type
	 * @var string
	 */
	private $post_type = 'slider';

	/**
	 * $taxonomies
	 * @var array
	 */
	private $taxonomies = array( 'slide-category' );
	
	
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
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
		return 'wpzoom-elementor-addons-slider-pro';
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
		return esc_html__( 'Inspiro Slideshow (old)', 'wpzoom-elementor-addons' );
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
		return [ 'wpzoom-elementor-addons-inspiro' ];
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
		return array();
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
		
		if ( !WPZOOM_Elementor_Widgets::is_supported_theme() ) {
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
			'section_restricted_portfolio_showcase',
			array(
				'label' => esc_html__( 'Widget not available', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'restricted_widget_text',
			[
				'raw' => wp_kses_post( __( 'This widget is supported only by the <a href="https://www.wpzoom.com/themes/inspiro/">"Inspiro Premium"</a> and <a href="https://www.wpzoom.com/themes/inspiro-pro/">"Inspiro PRO"</a> themes', 'wpzoom-elementor-addons' ) ),
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
			'_section_slider_pro',
			array(
				'label' => esc_html__( 'Inspiro Slideshow', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => esc_html__( 'Slideshow Category to show:', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'You can choose here a category from which slides are shown.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 0,
				'options'     => $this->get_slider_taxonomies(),
			)
		);
		$this->add_control(
			'show_count',
			array(
				'label'       => esc_html__( 'Number of Posts in Slider', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'How many posts should appear in Slider on the homepage? Default: 5.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 5,
			)
		);

		$this->add_control(
			'slides_order',
			array(
				'label'       => esc_html__( 'Slides Order', 'wpzoom-elementor-addons' ),
				'description' => wp_kses_post( __( '<strong>Default:</strong> Uses menu order which can be changed from <a href="edit.php?post_type=slider&page=wpzoom_featured_sorting_menu_slider" target="_blank">this page</a>.', 'wpzoom-elementor-addons' ) ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'default',
				'options'     => array(
					'default' => esc_html__( 'Default (Menu Order)', 'wpzoom-elementor-addons' ),
					'random'  => esc_html__( 'Random', 'wpzoom-elementor-addons' ),
				),
			)
		);

		$this->add_control(
			'heading_slide_title',
			[
				'label' => esc_html__( 'Slide Title', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_control(
			'responsive_description_slide_title',
			[
				'raw' => esc_html__( 'Responsive visibility will take effect only on preview or live page, and not while editing in Elementor.', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
			]
		);
		$this->add_control(
			'hide_title_desktop',
			[
				'label' => esc_html__( 'Hide On Desktop', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'wpzoom-',
				'label_on' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'return_value' => 'elementor-hidden-desktop',
			]
		);

		$this->add_control(
			'hide_title_tablet',
			[
				'label' => esc_html__( 'Hide On Tablet', 'wpzoom-elementor-addons'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'wpzoom-',
				'label_on' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'return_value' => 'elementor-hidden-tablet',
			]
		);

		$this->add_control(
			'hide_title_mobile',
			[
				'label' => esc_html__( 'Hide On Mobile', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'wpzoom-',
				'label_on' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'return_value' => 'elementor-hidden-phone',
			]
		);

		$this->add_control(
			'heading_slide_excerpt',
			[
				'label' => esc_html__( 'Slide Content', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_control(
			'responsive_description_slide_excerpt',
			[
				'raw' => esc_html__( 'Responsive visibility will take effect only on preview or live page, and not while editing in Elementor.', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
			]
		);
		$this->add_control(
			'hide_excerpt_desktop',
			[
				'label' => esc_html__( 'Hide On Desktop', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'wpzoom-',
				'label_on' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'return_value' => 'elementor-hidden-desktop',
			]
		);

		$this->add_control(
			'hide_excerpt_tablet',
			[
				'label' => esc_html__( 'Hide On Tablet', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'wpzoom-',
				'label_on' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'return_value' => 'elementor-hidden-tablet',
			]
		);

		$this->add_control(
			'hide_excerpt_mobile',
			[
				'label' => esc_html__( 'Hide On Mobile', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'wpzoom-',
				'label_on' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'return_value' => 'elementor-hidden-phone',
			]
		);

		$this->add_control(
			'slideshow_scroll',
			array(
				'label'       => esc_html__( 'Display Scroll to Content Pointer?', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'This pointer is located at the bottom center of the slideshow and when you click it the page scrolls to the next section located below the slideshow.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off'   => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'default'     => 'yes',
				'separator'   => 'before',
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
			'_section_style_slider_pro',
			array(
				'label' => esc_html__( 'Slider Options', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'slideshow_height_desktop',
			array(
				'label'       => esc_html__( 'Slider Height (In Percents)', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'Slider height in regard to browser height.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'vh' ),
				'range'  => array(
					'vh' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default' => array(
					'unit' => 'vh',
					'size' => 100
				),
				'tablet_default' => array(
					'unit' => 'vh',
					'size' => 100
				),
				'mobile_default' => array(
					'unit' => 'vh',
					'size' => 100
				),
				'selectors' => array(
					'{{WRAPPER}} #slider .flex-viewport,{{WRAPPER}} #slider .slides,{{WRAPPER}} #slider .slides > li' => 'height: {{SIZE}}vh !important;',
				)
			)
		);


		$current_theme = get_template();

        /* Option for Inspiro Premium*/
        if( 'inspiro' === $current_theme  ) {

            $this->add_control(
                'slideshow_align',
                [
                    'label' => esc_html__( 'Content Alignment', 'wpzoom-elementor-addons' ),
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
                    'default' => 'center'
                ]
            );

            $this->add_control(
                'slideshow_align_vertical',
                [
                    'label' => esc_html__( 'Content Position (Vertical)', 'wpzoom-elementor-addons' ),
                    'type' => Controls_Manager::CHOOSE,
                    'label_block' => false,
                    'options' => [
                        'bottom' => [
                            'title' => esc_html__( 'Bottom', 'wpzoom-elementor-addons' ),
                            'icon' => 'eicon-v-align-bottom',
                        ],
                        'middle' => [
                            'title' => esc_html__( 'Middle', 'wpzoom-elementor-addons' ),
                            'icon' => 'eicon-v-align-middle',
                        ],
                    ],

                    'condition'   =>  array(
                        'slideshow_align!' => 'center',
                    ),
                    'toggle' => true,
                    'default' => 'middle'
                ]
            );


        }


		/* Option for Inspiro PRO*/
		if( 'wpzoom-inspiro-pro' === $current_theme  ) {

            $this->add_control(
                'slideshow_align',
                [
                    'label' => esc_html__( 'Content Alignment', 'wpzoom-elementor-addons' ),
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
                    'default' => 'left'
                ]
            );

            $this->add_control(
                'slideshow_align_vertical',
                [
                    'label' => esc_html__( 'Content Position (Vertical)', 'wpzoom-elementor-addons' ),
                    'type' => Controls_Manager::CHOOSE,
                    'label_block' => false,
                    'options' => [
                        'bottom' => [
                            'title' => esc_html__( 'Bottom', 'wpzoom-elementor-addons' ),
                            'icon' => 'eicon-v-align-bottom',
                        ],
                        'middle' => [
                            'title' => esc_html__( 'Middle', 'wpzoom-elementor-addons' ),
                            'icon' => 'eicon-v-align-middle',
                        ],
                    ],

                    'condition'   =>  array(
                        'slideshow_align!' => 'center',
                    ),
                    'toggle' => true,
                    'default' => 'bottom'
                ]
            );


        }


		$this->add_control(
			'slideshow_overlay',
			array(
				'label'        => esc_html__( 'Enable overlay background?', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off'    => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'slider_pro_overlay_bg',
				'label'    => esc_html__( 'Overlay Background', 'wpzoom-elementor-addons' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} #slider .slide-background-overlay',
				'condition' => array(
					'slideshow_overlay' => 'yes'
				)
			)
		);

		$this->end_controls_section();


        $this->start_controls_section(
            '_section_colors_title',
            [
                'label' => esc_html__( 'Slider Title', 'wpzoom-elementor-addons' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        /* Option for Inspiro PRO*/
        if( 'wpzoom-inspiro-pro' === $current_theme  ) {

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'title',
                    'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
                    'selector' => '{{WRAPPER}} .slides li h1, {{WRAPPER}} .slides li h3',
                    'fields_options' => [
                        // first mimic the click on Typography edit icon
                        'typography' => ['default' => 'yes'],
                        // then redifine the Elementor defaults
                        'font_family' => ['default' => 'Poppins'],
                        'font_size' => ['default' => ['size' => 54], 'tablet_default' => ['size' => 30], 'mobile_default' => ['size' => 24]],
                        'font_weight' => ['default' => 600],
                    ],
                ]
            );
        } else {

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'title',
                    'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
                    'selector' => '{{WRAPPER}} .slides li h1, {{WRAPPER}} .slides li h3',
                    'fields_options' => [
                        // first mimic the click on Typography edit icon
                        'typography' => ['default' => 'yes'],
                        // then redifine the Elementor defaults
                        'font_family' => ['default' => 'Inter'],
                        'font_size' => ['default' => ['size' => 72], 'tablet_default' => ['size' => 38], 'mobile_default' => ['size' => 24]],
                        'font_weight' => ['default' => 200],
                    ],
                ]
            );

        }

        $this->start_controls_tabs( '_tabs_title' );

        $this->start_controls_tab(
            '_tab_title_normal',
            [
                'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .slides li h1' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .slides li h1 a' => 'color: {{VALUE}}',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            '_tab_title_hover',
            [
                'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .slides li h1 a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        // Title margin.
        $this->add_responsive_control(
            'title_spacing',
            [
                'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .slides li h1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->end_controls_section();



        $this->start_controls_section(
            '_section_text_style',
            [
                'label' => esc_html__( 'Slider Text', 'wpzoom-elementor-addons' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt',
                'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
                'selector' => '{{WRAPPER}} .slides > li .excerpt',
                'fields_options' => [
                    // first mimic the click on Typography edit icon
                    'typography' => ['default' => 'yes'],
                    // then redifine the Elementor defaults
                    'font_family' => ['default' => 'Inter'],
                    'font_size' => ['default' => ['size' => 18]],
                    'font_weight' => ['default' => 400],
                ],
            ]
        );


        $this->add_control(
            'excerpt_color',
            [
                'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .slides > li .excerpt' => 'color: {{VALUE}}',
                ],
            ]
        );

        // Text margin.
        $this->add_responsive_control(
            'excerpt_spacing',
            [
                'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .slides > li .excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->end_controls_section();



        $this->start_controls_section(
            '_section_colors_button',
            [
                'label' => esc_html__( 'Slider Button', 'wpzoom-elementor-addons' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button',
                'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
                'selector' => '{{WRAPPER}} .slides > li .slide_button a',
                'fields_options' => [
                    // first mimic the click on Typography edit icon
                    'typography' => ['default' => 'yes'],
                    // then redifine the Elementor defaults
                    'font_family' => ['default' => 'Inter'],
                    'font_size' => ['default' => ['size' => 18]],
                    'font_weight' => ['default' => 600],
                ],
            ]
        );

        $this->start_controls_tabs( '_tabs_btn' );

        $this->start_controls_tab(
            '_tab_btn_normal',
            [
                'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->add_control(
            'btn_color',
            [
                'label' => esc_html__( 'Button Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .slides > li .slide_button a' => 'color: {{VALUE}}',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'btn_style_background',
                'label' => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
                'types' => [ 'classic', 'gradient' ],
                'exclude' => [ 'image' ],
                'selector' => '{{WRAPPER}} .slides > li .slide_button a',
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

        $this->start_controls_tab(
            '_tab_btn_hover',
            [
                'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
            ]
        );

        $this->add_control(
            'btn_color_hover',
            [
                'label' => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .slides > li .slide_button a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'btn_style_background_hover',
                'label' => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
                'types' => [ 'classic', 'gradient' ],
                'exclude' => [ 'image' ],
                'selector' => '{{WRAPPER}} .slides > li .slide_button a:hover',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                ],
            ]
        );

        $this->add_control(
            'btn_style_hover_border_color',
            [
                'label' => esc_html__( 'Border Color', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'btn_style_border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .slides > li .slide_button a:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'btn_style_border',
                'selector' => '{{WRAPPER}} .slides > li .slide_button a',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'btn_style_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .slides > li .slide_button a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'btn_style_box_shadow',
                'selector' => '{{WRAPPER}} .slides > li .slide_button a',
            ]
        );

        $this->add_responsive_control(
            'btn_style_text_padding',
            [
                'label' => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .slides > li .slide_button a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        // Readmore margin
        $this->add_responsive_control(
            'btn_style_margin',
            [
                'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .slides > li .slide_button a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        $this->end_controls_section();

	}

	/**
	 * Get slider taxonomies.
	 *
	 * Retrieve a list of all slider taxonomies.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array All slider taxonomies.
	 */
	protected function get_slider_taxonomies() {

		$slider_tax = array(
			'0' => esc_html__( 'All', 'wpzoom-elementor-addons' )
		);

		$tax_args = array(
			'orderby'    => 'name',
			'order'      => 'asc',
			'hide_empty' => false,
		);

		$terms = get_terms( 'slide-category', $tax_args );

		if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
			foreach ( $terms as $key => $taxonomy ) {
				if ( is_object( $taxonomy ) && property_exists( $taxonomy, 'slug' ) && property_exists( $taxonomy, 'name' ) ) {
					$slider_tax[ $taxonomy->slug ] = $taxonomy->name;
				}
			}
		}

		return $slider_tax;

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

		if ( !WPZOOM_Elementor_Widgets::is_supported_theme() ) {
			if( current_user_can('editor') || current_user_can('administrator') ) {
				echo '<h3>' . esc_html__( 'Widget not available', 'wpzoom-elementor-addons' ) . '</h3>';
				echo wp_kses_post( __( 'This widget is supported only by the <a href="https://www.wpzoom.com/themes/inspiro/">"Inspiro Premium"</a> and <a href="https://www.wpzoom.com/themes/inspiro-pro/">"Inspiro PRO"</a> themes', 'wpzoom-elementor-addons' ) );
			}
			return;
		}


		$settings = $this->get_settings_for_display();
		$current_theme = get_template();

		$align = isset( $settings['slideshow_align'] ) ? $settings['slideshow_align'] : '';
        $align_vertical = isset( $settings['slideshow_align_vertical'] ) ? $settings['slideshow_align_vertical'] : '';

		$this->add_render_attribute( '_li-wrap', 'class', 'li-wrap wpz-' . $align . '-slider-wrap wpz-' . $align_vertical . '-slider-wrap' );

		$this->add_render_attribute( '_slide_title', 'class', [ $settings['hide_title_desktop'], $settings['hide_title_tablet'], $settings['hide_title_mobile'] ] );
		
		$this->add_render_attribute( '_slide_excerpt', 'class', 'excerpt' );
		$this->add_render_attribute( '_slide_excerpt', 'class', [ $settings['hide_excerpt_desktop'], $settings['hide_excerpt_tablet'], $settings['hide_excerpt_mobile'] ] );

		$show_count = $settings['show_count'];
		$category   = $settings['category'];

		$slideshow_scroll  = ( 'yes' === $settings['slideshow_scroll'] ? true : false );

		$args = array(
			'post_type'      => 'slider',
			'posts_per_page' => $show_count,
			'orderby'        => $settings['slides_order'] === 'random' ? 'rand' : 'menu_order date',
			'post_status'    => 'publish'
		);

		if ( $category ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'slide-category',
					'terms'    => $category,
					'field'    => 'slug',
				)
			);
		}

		$sliderLoop = new \WP_Query( $args );
		$slide_counter = 0;
	
		if ( $sliderLoop->have_posts() ) : ?>
			<div id="slider" class="flexslider" data-posts="<?php echo count( $sliderLoop->posts ); ?>">
				<ul class="slides">
					<?php while ( $sliderLoop->have_posts() ) : $sliderLoop->the_post();

						$slide_url                       = trim( get_post_meta( get_the_ID(), 'wpzoom_slide_url', true ) );
						$btn_title                       = trim( get_post_meta( get_the_ID(), 'wpzoom_slide_button_title', true ) );
						$btn_url                         = trim( get_post_meta( get_the_ID(), 'wpzoom_slide_button_url', true ) );
						$large_image_url                 = wp_get_attachment_image_src( get_post_thumbnail_id(), 'featured@2x' );
                        $medium_image_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'featured');
						$small_image_url                 = wp_get_attachment_image_src( get_post_thumbnail_id(), 'featured-small' );
						$video_background_mp4            = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_bg_url_mp4', true );
						$video_background_webm           = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_bg_url_webm', true );
						$video_mobile_background_mp4     = get_post_meta( get_the_ID(), 'wpzoom_home_slider_mobile_video_bg_url_mp4', true );
						$video_background_external_url   = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_external_url', true );
						$video_background_popup_url      = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_popup_url', true );
						$video_background_popup_url_mp4  = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_popup_url_mp4', true );
						$video_background_popup_url_webm = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_popup_url_webm', true );
						$post_meta_of_external_hosted    = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_type', true);
						$show_play_button                = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_slide_play_button', true ) == '' ? true : get_post_meta( get_the_ID(), 'wpzoom_slide_play_button', true ) );
						$show_sound_button               = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_slide_mute_button', true ) == '' ? true : get_post_meta( get_the_ID(), 'wpzoom_slide_mute_button', true ) );
						$autoplay                        = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_slide_autoplay_video_action', true ) == '' ? true : get_post_meta( get_the_ID(), 'wpzoom_slide_autoplay_video_action', true ) );
						$loop                            = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_slide_loop_video_action', true ) == '' ? true : get_post_meta( get_the_ID(), 'wpzoom_slide_loop_video_action', true ) );
						$dnt                             = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_slide_dnt_video_action', true ) == '' ? true : get_post_meta( get_the_ID(), 'wpzoom_slide_dnt_video_action', true ) );
						$sound                           = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_slide_mute_video_action', true ) == '' ? true : get_post_meta( get_the_ID(), 'wpzoom_slide_mute_video_action', true ) );
						$v_type                          = get_post_meta( get_the_ID(), 'wpzoom_home_slider_popup_video_type', true );
						$vimeo_video_id                  = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_vimeo_pro_video_id', true );
						$vimeo_pro_video_url             = get_post_meta( get_the_ID(), 'wpzoom_home_slider_video_vimeo_pro', true );
						$popup_video_type                = ! empty( $v_type ) ? $v_type : 'external_hosted';
						$popup_final_external_src        = ! empty( $video_background_popup_url_mp4 ) ? $video_background_popup_url_mp4 : $video_background_popup_url_webm;
						$is_vimeo_pro                    = 'vimeo_pro' === $post_meta_of_external_hosted && !empty( $vimeo_video_id );
						$is_video_slide                  = ( $video_background_mp4 || $video_background_webm ) && 'self_hosted' === $post_meta_of_external_hosted;
						$is_video_popup                  = $video_background_popup_url_mp4 || $video_background_popup_url_webm;
						$is_video_external               = ! empty( $video_background_external_url ) && ( ! ( filter_var( $video_background_external_url, FILTER_VALIDATE_URL ) === false ) );
						$is_formstone                    = in_array( $post_meta_of_external_hosted, array( 'self_hosted', 'external_hosted' ) );

						$lightbox_video_autoplay = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_portfolio_lightbox_video_autoplay', true ) == '' ? true : get_post_meta( get_the_ID(), 'wpzoom_portfolio_lightbox_video_autoplay', true ) );
						$lightbox_video_mute     = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_portfolio_lightbox_video_mute', true ) == '' ? false : get_post_meta( get_the_ID(), 'wpzoom_portfolio_lightbox_video_mute', true ) );
						$lightbox_video_loop     = (bool) ( get_post_meta( get_the_ID(), 'wpzoom_portfolio_lightbox_video_loop', true ) == '' ? false : get_post_meta( get_the_ID(), 'wpzoom_portfolio_lightbox_video_loop', true ) );
		
						$encode_lightbox_video_opts = array(
							'autoplay' => $lightbox_video_autoplay,
							'mute'     => $lightbox_video_mute,
							'loop'     => $lightbox_video_loop
						);

						$slide_counter++;

						$style = '';

						$source = $mobile_source = array(
							'poster' => ''
						);

						if ( ! empty( $large_image_url ) ) {
							$source['poster'] = $large_image_url[0];
						}
						if ( $is_video_external && 'external_hosted' == $post_meta_of_external_hosted ) {
							$source['video'] = $video_background_external_url;
						}
						if ( ! empty( $video_background_mp4 ) && 'self_hosted' == $post_meta_of_external_hosted ) {
							$source['mp4'] = $video_background_mp4;
						}
						if ( ! empty( $video_background_webm ) && 'self_hosted' == $post_meta_of_external_hosted ) {
							$source['webm'] = $video_background_webm;
						}

						if ( ! empty( $video_mobile_background_mp4 ) && 'self_hosted' == $post_meta_of_external_hosted ) {
							$mobile_source['mp4'] = $video_mobile_background_mp4;
						}

						$encode_array = array(
							'source'       => $source,
							'mobileSource' => $mobile_source,
							'autoPlay'     => $autoplay,
							'mute'         => $sound,
							'loop'         => $loop
						);

						$vimeo_player_args = array(
							'autoplay'   => $autoplay,
							'muted'      => $sound,
							'loop'       => $loop,
							'byline'     => 0,
							'title'      => 0,
							'id'         => $vimeo_video_id,
							'url'        => $vimeo_pro_video_url,
							'background' => 1,
							'dnt'        => !$dnt
						);

						$video_on_mobile = get_theme_mod( 'featured_video_mobile', zoom_customizer_get_default_option_value( 'featured_video_mobile', inspiro_customizer_data() ) );

						if ( ! $is_video_slide || \option::is_on( 'slideshow_video_fallback' ) ) {

                            $data_smallimg = isset( $small_image_url[0] ) ? ' data-smallimg="' . esc_attr( $small_image_url[0] ) . '"' : '';
                            $data_bigimg   = isset( $large_image_url[0] ) ? ' data-bigimg="' . esc_attr( $large_image_url[0] ) . '"' : '';
                            $data_mediumimg   = isset( $medium_image_url[0] ) ? ' data-mediumimg="' . esc_attr( $medium_image_url[0] ) . '"' : '';

                            $style = $data_smallimg . $data_bigimg . $data_mediumimg;

						}
						?>
						<li <?php echo $style; // WPCS: XSS OK. ?> <?php if ( $is_formstone && ( $is_video_slide || $is_video_external ) ): ?>data-formstone-options='<?php echo json_encode( $encode_array ); ?>' <?php endif; ?> <?php if ( $is_vimeo_pro ): ?> class="is-vimeo-pro-slide" data-vimeo-options='<?php echo json_encode( $vimeo_player_args ); ?>' <?php endif; ?>>

							<div class="slide-background-overlay"></div>

                            <?php 
							/* Markup for Inspiro PRO*/
							if( ('wpzoom-inspiro-pro' === $current_theme && 'center' != $align ) || ('inspiro' === $current_theme && 'bottom' === $align_vertical ) ) { ?>

                            <?php if($popup_video_type === 'self_hosted' && $is_video_popup): ?>
                                <div id="zoom-popup-<?php echo get_the_ID(); ?>"  class="animated slow mfp-hide" data-src ="<?php echo esc_url( $popup_final_external_src ); ?>">

                                    <div class="mfp-iframe-scaler">

                                        <?php
                                        echo wp_video_shortcode(
                                            array(
                                                'src' => $popup_final_external_src,
                                                'preload' => 'none',
                                                'loop'     => $lightbox_video_loop,
                                                'muted'    => $lightbox_video_mute
                                            ));
                                        ?>

                                    </div>
                                </div>
                                <a href="#zoom-popup-<?php echo get_the_ID(); ?>" data-popup-video-options='<?php echo json_encode( $encode_lightbox_video_opts ); ?>' data-popup-type="inline" class="popup-video" aria-label="Watch Video"></a>

                                <?php elseif(!empty($video_background_popup_url)): ?>
                                    <a data-popup-type="iframe" data-popup-video-options='<?php echo json_encode( $encode_lightbox_video_opts ); ?>' class="popup-video animated slow pulse" href="<?php echo esc_url( $video_background_popup_url ); ?>" aria-label="Watch Video"></a>
                                <?php endif; ?>

                            <?php } /* End Inspiro PRO markup */ ?>

                            <div <?php echo $this->get_render_attribute_string( '_li-wrap' ); ?>>

                                <?php if ($slide_counter == 1) { ?>

									<?php if ( empty( $slide_url ) ) { 
										$this->add_render_attribute( '_slide_title', 'class', 'missing-url' );
										?>
										<?php the_title( '<h1 ' . $this->get_render_attribute_string( '_slide_title' ) . '>', '</h1>'); ?>
									<?php } else { ?>
										<?php the_title( sprintf( '<h1 ' . $this->get_render_attribute_string( '_slide_title' ) . '><a href="%s">', esc_url( $slide_url ) ), '</a></h1>'); ?>
									<?php } ?>

                                <?php } else { ?>

                                    <?php if ( empty( $slide_url ) ) {
                                        $this->add_render_attribute( '_slide_title', 'class', 'missing-url' );
                                        ?>
                                        <?php the_title( '<h3 ' . $this->get_render_attribute_string( '_slide_title' ) . '>', '</h3>'); ?>
                                    <?php } else { ?>
                                        <?php the_title( sprintf( '<h3 ' . $this->get_render_attribute_string( '_slide_title' ) . '><a href="%s">', esc_url( $slide_url ) ), '</a></h3>'); ?>
                                    <?php } ?>

                                <?php } ?>

									<div <?php echo $this->get_render_attribute_string( '_slide_excerpt' ); ?>><?php the_content(); ?></div>

								<?php if ( ! empty( $btn_title ) && ! empty( $btn_url ) ) {
									?>
									<div class="slide_button">
										<a href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_title ); ?></a>
									</div><?php
								} 
								?>
                                <?php
                                    /* Markup for Inspiro Premium*/
                                     if( ( 'inspiro' === $current_theme && class_exists( 'WPZOOM' ) && $align_vertical != 'bottom' ) || ( 'wpzoom-inspiro-pro' === $current_theme && $align == 'center' ) ) {
								?>
                                <?php if($popup_video_type === 'self_hosted' && $is_video_popup): ?>
                                    <div id="zoom-popup-<?php echo get_the_ID(); ?>"  class="animated slow mfp-hide" data-src ="<?php echo esc_url( $popup_final_external_src ); ?>">

                                        <div class="mfp-iframe-scaler">

                                            <?php echo wp_video_shortcode(
												array(
													'src' => $popup_final_external_src,
													'preload' => 'none',
													'loop'     => $lightbox_video_loop,
													'muted'    => $lightbox_video_mute,
												));
                                            ?>

                                        </div>
                                    </div>
                                    <a href="#zoom-popup-<?php echo get_the_ID(); ?>" data-popup-video-options='<?php echo json_encode( $encode_lightbox_video_opts ); ?>' data-popup-type="inline" class="popup-video" aria-label="Watch Video"></a>

                                <?php elseif(!empty($video_background_popup_url)): ?>
                                    <a data-popup-type="iframe" data-popup-video-options='<?php echo json_encode( $encode_lightbox_video_opts ); ?>' class="popup-video animated slow pulse" href="<?php echo esc_url( $video_background_popup_url ); ?>" aria-label="Watch Video"></a>
                                <?php endif; ?>

                                <?php } /* End Inspiro Premium markup */ ?>

							</div>

							<?php if ( ! empty( $video_background_mp4 ) || ! empty( $video_background_webm ) || ! empty( $is_vimeo_pro ) || $is_video_external ): ?>

								<div class="background-video-buttons-wrapper">

									<?php if ( $show_play_button || ! $autoplay ): ?>
										<a class="wpzoom-button-video-background-play display-none" href="#"><?php esc_html_e( 'Play', 'wpzoom-elementor-addons' ); ?></a>
										<a class="wpzoom-button-video-background-pause display-none" href="#"><?php esc_html_e( 'Pause', 'wpzoom-elementor-addons' ); ?></a>

									<?php endif; ?>

									<?php if ( $show_sound_button ): ?>
										<a class="wpzoom-button-sound-background-unmute display-none" href="#"><?php esc_html_e( 'Unmute', 'wpzoom-elementor-addons' ); ?></a>
										<a class="wpzoom-button-sound-background-mute display-none" href="#"><?php esc_html_e( 'Mute', 'wpzoom-elementor-addons' ); ?></a>

									<?php endif; ?>

								</div>
							<?php endif; ?>
						</li>
					<?php endwhile; ?>

				</ul>
				<?php if( $slideshow_scroll ) { ?>
					<div id="scroll-to-content" title="<?php esc_attr_e( 'Scroll to Content', 'wpzoom-elementor-addons' ); ?>">
						<?php esc_html_e('Scroll to Content', 'wpzoom-elementor-addons' ); ?>
					</div>
				<?php } ?>
			</div>
		<?php else: ?>
			<div class="empty-slider">
				<div class="inner-wrap">
					<p><strong><?php esc_html_e('You are now ready to set-up your Slideshow content.', 'wpzoom-elementor-addons' ); ?></strong></p>
					<p>
						<?php
						printf(
							__('For more information about adding posts to the slider, please <strong><a href="%1$s">read the documentation</a></strong> or <a href="%2$s">add a new post</a>.', 'wpzoom-elementor-addons' ),
							'https://www.wpzoom.com/documentation/inspiro/',
							admin_url('post-new.php?post_type=slider')
						);
						?>
					</p>
				</div>
			</div>
		<?php 
			endif;
			wp_reset_postdata();
	}
}
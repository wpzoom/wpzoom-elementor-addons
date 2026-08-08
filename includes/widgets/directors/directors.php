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

use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Embed;
use Elementor\Icons_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WPZOOM Elementor Widgets - Directors Widget.
 *
 * Elementor widget that inserts a customizable slider.
 *
 * @since 1.0.0
 */
class Directors extends Widget_Base {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-directors', plugins_url( 'frontend.css', __FILE__ ), null, WPZOOM_EL_ADDONS_VER );
		wp_register_script( 'wpzoom-elementor-addons-js-frontend-directors', plugins_url( 'frontend.js', __FILE__ ), array( 'jquery' ), WPZOOM_EL_ADDONS_VER, true );
	
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
		return 'wpzoom-elementor-addons-directors';
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
		return esc_html__( 'Directors', 'wpzoom-elementor-addons' );
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
		return 'eicon-accordion';
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
		return array(
			'wpzoom-elementor-addons-css-frontend-directors'
		);
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
			'wpzoom-elementor-addons-js-frontend-directors'
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
			'_section_directors',
			array(
				'label' => esc_html__( 'Directors', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'style',
			array(
				'label'   => esc_html__( 'Style', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'default'  => esc_html__( 'Default', 'wpzoom-elementor-addons' ),
					'fullview' => esc_html__( 'Full View', 'wpzoom-elementor-addons' ),
				),
				'default' => 'default'
			)
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
			'insert_url',
			[
				'label' => esc_html__( 'External URL', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
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
					'insert_url' => ''
				],
				'frontend_available' => true
			]
		);

		$repeater->add_control(
			'external_url',
			[
				'label' => esc_html__( 'URL', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'Supports only MP4 video format', 'wpzoom-elementor-addons' ),
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
					'insert_url' => 'yes'
				],
				'frontend_available' => true
			]
		);

		$repeater->end_popover();

		$repeater->add_control(
			'title',
			[
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'label' => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
				'placeholder' => esc_html__( 'Type title here', 'wpzoom-elementor-addons' ),
				'default' => array_rand( array_flip( array( 'Burnish Creative', 'Vita Race', 'Fabian Ferdinand Fallend', 'Cargo The Film', 'Vita Titan' ) ), 1 ),
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
			'directors',
			[
				'show_label' => false,
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '<# print(title || "' . esc_js( __( 'Director Item', 'wpzoom-elementor-addons' ) ) . '"); #>',
				'default' => array(
					array(
						'image' => array( 'url' =>  Utils::get_placeholder_image_src() ),
						'title' => 'Burnish Creative'
					),
					array(
						'image' => array( 'url' =>  Utils::get_placeholder_image_src() ),
						'title' => 'Vita Race'
					),
					array(
						'image' => array( 'url' =>  Utils::get_placeholder_image_src() ),
						'title' => 'Fabian Ferdinand'
					),
					array(
						'image' => array( 'url' =>  Utils::get_placeholder_image_src() ),
						'title' => 'Cargo The Film'
					),
					array(
						'image' => array( 'url' =>  Utils::get_placeholder_image_src() ),
						'title' => 'Vita Titan'
					),
				)
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
			'_section_style_directors',
			array(
				'label' => esc_html__( 'Directors', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title',
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-directors-nav ul li, {{WRAPPER}} .wpz-directors-nav ul li a',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
			]
		);

		$this->start_controls_tabs( '_tabs_nav_item' );

		$this->start_controls_tab(
			'_tab_nav_item_normal',
			[
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'nav_item_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-directors-nav ul li, {{WRAPPER}} .wpz-directors-nav ul li a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'_tab_nav_item_hover',
			[
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			]
		);

		$this->add_control(
			'nav_item_hover_color',
			[
				'label' => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpz-directors-nav ul li:hover, {{WRAPPER}} .wpz-directors-nav ul li a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'label_nav_align',
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
					'{{WRAPPER}} .wpz-directors-nav ul li.wpz-director-nav-item' => 'text-align: {{VALUE}}'
				]
			]
		);

		$this->add_control(
			'director_title_opacity',
			array(
				'label'     => esc_html__( 'Title Opacity', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'default'   => array(
					'size' => 0.5,
				),
				'selectors' => array(
					'{{WRAPPER}} .wpz-directors-nav:hover li:not(:hover),{{WRAPPER}} .wpz-directors-nav:hover li a:not(:hover)' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_control(
			'director_background_opacity',
			array(
				'label'     => esc_html__( 'Background Opacity', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'default'   => array(
					'size' => 1,
				),
				'selectors' => array(
					'{{WRAPPER}} .wpz-director-object-container' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_responsive_control(
			'director_margin',
			array(
				'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-directors-nav ul li.wpz-director-nav-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
				]
			)
		);
		$this->add_responsive_control(
			'director_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-directors-nav ul li.wpz-director-nav-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
				]
			)
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'_section_style_hr',
			[
				'label' => esc_html__( 'Horizontal Rule', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'style!' => 'fullview',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'selector' => '{{WRAPPER}} .wpz-directors-nav ul li hr',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label' => esc_html__( 'HR Width', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'px' => [
						'max' => 1000,
					],
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .wpz-directors-nav ul li hr' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'hr_style_margin',
			array(
				'label'      => esc_html__( 'Margin', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpz-directors-nav ul li hr' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
				]
			)
		);


		$this->end_controls_section();

	}

	/**
	 * Get the URL of a hosted video.
	 *
	 * @param array $director The director to get the data from.
	 * @since 1.0.0
	 * @access private
	 * @return string
	 */
	private function get_hosted_video_url( $director ) {
		
		if ( ! empty( $director[ 'insert_url' ] ) ) {
			$video_url = $director[ 'external_url' ][ 'url' ];
		} else {
			$video_url = ! empty( $director[ 'hosted_url' ][ 'url' ] ) ? $director[ 'hosted_url' ][ 'url' ] : '';
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
	private function render_hosted_video( $director ) {
		$video_url = $this->get_hosted_video_url( $director );

		if ( empty( $video_url ) ) {
			return;
		}

		?>
		<video data-video-bg="true" autoplay loop muted class="wpzoom-elementor-video" src="<?php echo esc_url( $video_url ); ?>" ></video>
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

		$settings  = $this->get_settings_for_display();
		$style     = $settings['style'];
		$directors = $settings['directors'];

		$this->add_render_attribute( 'title', 'class', 'wpz-director-nav-item' );
		
		$this->add_render_attribute( 'directors', 'id', 'wpz-directors' );
		$this->add_render_attribute( 'directors', 'class', 'wpz-directors-wrapper' );
		$this->add_render_attribute( 'directors', 'class', 'wpz-' . $style . '-directors' );
		$this->add_render_attribute( 'directors', 'data-active-onhover', 'true' );
		if( 'fullview' === $style ) {
			$this->add_render_attribute( 'directors', 'data-active-onhover-options', wp_json_encode( array( 'triggers' => '.wpz-directors-nav li', 'targets' => '.wpz-director-object-container .wpz-director-bg' ) ) );
		}
		elseif( 'default' === $style ) {
			$this->add_render_attribute( 'directors', 'data-active-onhover-options', wp_json_encode( array( 'triggers' => '.wpz-directors-nav li', 'targets' => '.wpz-directors-nav li' ) ) );
		}

	?>
	<div <?php $this->print_render_attribute_string( 'directors' ); ?>>
		<?php if( 'fullview' == $style ) :  ?>
		<div class="wpz-director-object-container">
			<?php foreach ( $directors as $director ) : ?>
					<?php 
						
						$video_url = $this->get_hosted_video_url( $director );
						
						if( !empty( $video_url ) && !empty( $director['title'] ) ) { 
						?>
						<div class="wpz-director-bg">
							<?php $this->render_hosted_video( $director ); ?>
						</div>
					<?php 
					continue;
					} else {
							$image = wp_get_attachment_image_url( $director[ 'image' ][ 'id' ], 'full' );
							if ( ! $image ) {
								$image = $director[ 'image' ][ 'url' ];
							}
						}
					?>

					<?php if ( $image && !empty( $director['title'] ) ) : ?>
						<div class="wpz-director-bg" data-responsive-bg="true">
							<figure>
								<img class="wpz-directors-img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $director[ 'title' ] ); ?>">
							</figure>
						</div>
					<?php endif; ?>
				
			<?php endforeach; ?>
		</div><!--//.wpz-directors-bg-container-->
		<?php endif; ?>


		<nav class="wpz-directors-nav">
			<ul>
			<?php foreach ( $directors as $director ) : ?>
				<?php if( !empty( $director['title']) ) : ?>
					<?php 
						$id = 'wpz-director-item-link-' . $director['_id' ];
						$this->add_render_attribute( $id, 'class', 'wpz-director-item-link' );
					?>
					<li <?php $this->print_render_attribute_string( 'title' ); ?>>
						<?php if( 'fullview' !== $style ) : ?>
							<?php 						
								$video_url = $this->get_hosted_video_url( $director );
								
								if( !empty( $video_url ) && !empty( $director['title'] ) ) { 
								?>
								<div class="wpz-director-bg">
									<?php $this->render_hosted_video( $director ); ?>
								</div>
									<span><?php esc_html_e( $director['title'] ); ?></span>
									<hr/>
								</li>
							<?php 
								continue;
								} else {
									$image = wp_get_attachment_image_url( $director[ 'image' ][ 'id' ], 'full' );
									if ( ! $image ) {
										$image = $director[ 'image' ][ 'url' ];
									}
								}
							?>
							<?php if ( $image && !empty( $director['title'] ) ) { ?>
								<div class="wpz-director-bg" data-responsive-bg="true">
									<figure class="hidden">
										<img class="wpz-directors-img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $director[ 'title' ] ); ?>">
									</figure>
								</div>
							<?php }; ?>
						<?php endif; ?>
						<?php 
							if ( isset( $director[ 'link' ] ) && ! empty( $director[ 'link' ][ 'url' ] ) ) { 
							$this->add_link_attributes( $id, $director[ 'link' ] );
						?>
							<a <?php $this->print_render_attribute_string( $id ); ?>>
						<?php } ?>
								<span><?php esc_html_e( $director['title'] ); ?></span>
						<?php if ( isset( $director[ 'link' ] ) && ! empty( $director[ 'link' ][ 'url' ] ) ) { ?>
							</a>
						<?php } ?>
						<hr/>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
			</ul>
		</nav>


	</div><!--//.wpz-directors-wrapper-->

<?php
	}
}

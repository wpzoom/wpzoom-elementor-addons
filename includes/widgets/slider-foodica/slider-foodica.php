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
 * ZOOM Elementor Widgets - Foodica Slider Widget.
 *
 * Elementor widget that inserts a customizable slider.
 *
 * @since 1.0.0
 */
class Slider_Foodica extends Widget_Base {
	
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
		return 'wpzoom-elementor-addons-slider-foodica';
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
		return esc_html__( 'Foodica Slideshow', 'wpzoom-elementor-addons' );
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
		return [ 'wpzoom-elementor-addons-foodica' ];
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
		
		if ( !WPZOOM_Elementor_Widgets::is_supported_theme( 'foodica' ) ) {
			$this->register_restricted_controls();
		}
		else {
			$this->register_content_controls();
			//$this->register_style_controls();
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
			'section_restricted_foodica_slideshow',
			array(
				'label' => esc_html__( 'Widget not available', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'restricted_widget_text',
			[
				'raw' => wp_kses_post( __( 'This widget is supported only by the <a href="https://www.wpzoom.com/themes/foodica/">"Foodica PRO"</a> theme', 'wpzoom-elementor-addons' ) ),
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
			'_section_foodica_slider',
			array(
				'label' => esc_html__( 'Foodica Slideshow', 'wpzoom-elementor-addons' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			)
		);
		
		$this->add_control(
			'featured_type',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => esc_html__( 'Content Source', 'wpzoom-elementor-addons' ),
				'description' => wp_kses_post( __( 'Select the type of content that should be displayed in the slider. <strong>Slides are ordered by date</strong>.', 'wpzoom-elementor-addons' ) ),
				'options'     => array(
					'post' => esc_html__( 'Featured Posts', 'wpzoom-elementor-addons' ),
					'page' => esc_html__( 'Featured Pages', 'wpzoom-elementor-addons' ),
				),
				'default' => 'post'
			)
		);
		$this->add_control(
			'slideshow_posts',
			array(
				'type'        => Controls_Manager::TEXT,
				'label'       => esc_html__( 'Number of Posts/Pages in Slider', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'How many posts or pages should appear in the Slider on the homepage? Default: 5.', 'wpzoom-elementor-addons' ),
				'default' => '5'
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
		$this->add_control(
			'slider_comments',
			array(
				'label' => esc_html__( 'Display Comments Count', 'wpzoom-elementor-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off' => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'default' => 'yes',
			)
		);
		$this->add_control(
			'slider_button',
			array(
				'label' => esc_html__( 'Display Read More Button', 'wpzoom-elementor-addons' ),
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
			'_section_style_foodica_slideshow',
			array(
				'label' => esc_html__( 'Foodica Slideshow', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
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

		if ( !WPZOOM_Elementor_Widgets::is_supported_theme( 'foodica' ) ) {
			if( current_user_can('editor') || current_user_can('administrator') ) {
				echo '<h3>' . esc_html__( 'Widget not available', 'wpzoom-elementor-addons' ) . '</h3>';
				echo wp_kses_post( __( 'This widget is supported only by the <a href="https://www.wpzoom.com/themes/foodica/">"Foodica PRO"</a> theme', 'wpzoom-elementor-addons' ) );
			}
			return;
		}

		$settings = $this->get_settings_for_display();
		
		$FeaturedSource  = $settings['featured_type'];
		$slideshow_posts = $settings['slideshow_posts'];

		$args = array(
			'showposts'    => $slideshow_posts,
			'post__not_in' => get_option( 'sticky_posts' ),
			'meta_key'     => 'wpzoom_is_featured',
			'meta_value'   => 1,
			'orderby'     => 'menu_order date',
			'post_status' => array( 'publish' ),
			'post_type' => $FeaturedSource
		);
	
		$featured = new \WP_Query($args );
	
		if ( $featured->have_posts() ) : ?>

			<div id="slider" class="<?php echo get_theme_mod('slider-styles', zoom_customizer_get_default_option_value('slider-styles', foodica_customizer_data()))?>">		
				<ul class="slides clearfix">
					<?php while ( $featured->have_posts() ) : $featured->the_post(); ?>
						<?php
	
							$slider_style = get_theme_mod('slider-styles', zoom_customizer_get_default_option_value('slider-styles', foodica_customizer_data()));
		
						if ($slider_style == 'slide-style-3') {
							$image_size = 'loop-full';
							$reatina_image_size = 'loop-full-retina';
						} else {
							$image_size = 'loop-sticky';
							$reatina_image_size = 'loop-sticky-retina';
						}
		
		
		
						$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), $image_size);
						$retina_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), $reatina_image_size);
		
						$style = ' style="background-image:url(\'' . wpzoom_get_value($large_image_url, '', 0) . '\')" data-rjs="' . wpzoom_get_value($retina_image_url, '', 0) . '"';
		
						?>
		
						<li class="slide">
		
							<div class="slide-overlay">
		
								<div class="slide-header">
		
								   <?php if ( 'yes' == $settings['slider_category'] && $FeaturedSource == 'post' ) printf( '<span class="cat-links">%s</span>', get_the_category_list( ', ' ) ); ?>
		
									<?php the_title( sprintf( '<h3><a href="%s">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>
		
		
									<?php if ($FeaturedSource == 'post') { ?>
										<div class="entry-meta">
											<?php if ( 'yes' == $settings['slider_date'] )     printf( '<span class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></span>', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) ); ?>
											<?php if ( 'yes' == $settings['slider_comments'] ) { ?><span class="comments-link"><?php comments_popup_link( __('0 comments', 'wpzoom-elementor-addons'), __('1 comment', 'wpzoom-elementor-addons'), __('% comments', 'wpzoom-elementor-addons'), '', __('Comments are Disabled', 'wpzoom-elementor-addons')); ?></span><?php } ?>
										</div>
									<?php } ?>
		
									<?php if ($FeaturedSource == 'page') { ?>
		
										<?php the_excerpt(); ?>
		
									<?php } ?>
		
		
									<?php if ( 'yes' == $settings['slider_button'] ) { ?>
										<div class="slide_button">
											<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wpzoom-elementor-addons' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php _e('Read More', 'wpzoom-elementor-addons'); ?></a>
										</div>
									<?php } ?>
		
								</div>
		
							</div>
		
							<div class="slide-background" <?php echo $style; ?>>
							</div>
						</li>
					<?php endwhile; ?>
		
				</ul>
		
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
							'https://www.wpzoom.com/documentation/foodica/foodica-configure-homepage-slider/'
						);
						?>
					</p>
				</div>
		
			<?php } ?>
		
		<?php endif;
			wp_reset_postdata();
	}
}
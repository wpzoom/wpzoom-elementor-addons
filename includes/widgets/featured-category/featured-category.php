<?php
/**
 * Featured Category Widget.
 *
 * Elementor widget that shows a featured category.
 *
 * @package WPZOOM_Elementor_Addons
 * @since   1.2.0
 */

namespace WPZOOMElementorWidgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Text_Stroke;
use \Elementor\Group_Control_Text_Shadow;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Css_Filter;

use \Elementor\Group_Control_Typography;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * ZOOM Elementor Widgets - Featured Category Widget.
 *
 * Elementor widget that shows a featured category.
 *
 * @since 1.2.0
 */
class Featured_Category extends Widget_Base {
	/**
	 * Constructor.
	 *
	 * @since  1.2.0
	 * @access public
	 * @param  array      $data Widget data. Default is an empty array.
	 * @param  array|null $args Optional. Widget default arguments. Default is null.
	 * @return mixed
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		// backend.css holds Elementor editor control styles — never needed by visitors.
		if ( is_admin() || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) ) {
			wp_enqueue_style( 'wpzoom-elementor-addons-css-backend-featured-category', plugins_url( 'backend.css', __FILE__ ), array(), WPZOOM_EL_ADDONS_VER );
		}
		wp_register_style( 'wpzoom-elementor-addons-css-frontend-featured-category', plugins_url( 'frontend.css', __FILE__ ), array(), WPZOOM_EL_ADDONS_VER );
	}

	/**
	 * Get widget name.
	 *
	 * Retrieve widget name.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wpzoom-elementor-addons-featured-category';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve widget title.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Featured Category', 'wpzoom-elementor-addons' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve widget icon.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-featured-image';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'wpzoom-elementor-addons' );
	}

	/**
	 * Style Dependencies.
	 *
	 * Returns all the styles the widget depends on.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return array Style slugs.
	 */
	public function get_style_depends() {
		return array( 'wpzoom-elementor-addons-css-frontend-featured-category' );
	}

	/**
	 * Get All Post Categories.
	 *
	 * Returns a list of all post categories.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return array Array of categories.
	 */
	private function get_all_categories() {
		$options = array();

		$taxonomy = 'category';

		if ( ! empty( $taxonomy ) ) {
			// Get categories for post type.
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
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
	 * @since  1.2.0
	 * @access public
	 * @return void
	 */
	protected function register_controls() {
		$this->content_options();
		$this->style_options();
	}

	/**
	 * Content Options.
	 *
	 * Registers the content controls.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return void
	 */
	private function content_options() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Content', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => esc_html__( 'Category', 'wpzoom-elementor-addons' ),
				'label_block' => true,
                'description' => wp_kses_post( __( 'Select the category to display.<br /><br/>The <strong>Featured Image</strong> for each category can be uploaded <a href=\'edit-tags.php?taxonomy=category\' target=\'_blank\'>here</a>.', 'wpzoom-elementor-addons' ) ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => false,
				'options'     => $this->get_all_categories(),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'        => 'category_image_size',
				'label'       => esc_html__( 'Category Image Scale', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'description' => esc_html__( 'Select the size at which to display the category image.', 'wpzoom-elementor-addons' ),
				'default'     => 'large',
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'show_post_count',
			array(
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Post Count', 'wpzoom-elementor-addons' ),
				'label_on'     => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'label_off'    => esc_html__( 'Hide', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'post_count_format',
			array(
				'type'        => Controls_Manager::TEXT,
				'label'       => esc_html__( 'Post Count Label', 'wpzoom-elementor-addons' ),
				'description' => __( 'The <strong><code>%</code></strong> character is a placeholder that will be replaced with the actual amount of posts when displayed on the frontend.', 'wpzoom-elementor-addons' ),
				// translators: % will be replaced in final output with the amount of posts in the category.
				'default'     => esc_html__( '% Posts', 'wpzoom-elementor-addons' ),
				// translators: % will be replaced in final output with the amount of posts in the category.
				'placeholder' => esc_html__( '% Posts', 'wpzoom-elementor-addons' ),
				'condition'   => array(
					'show_post_count' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Options.
	 *
	 * Registers the style controls.
	 *
	 * @since  1.2.0
	 * @access public
	 * @return void
	 */
	private function style_options() {
		$this->start_controls_section(
			'section_style_typography',
			array(
				'label' => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'section_style_typography_tabs' );

		$this->start_controls_tab(
			'section_style_typography_tabs_normal',
			array(
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'style_normal_typography',
				'label'          => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'fields_options' => array(
					'typography'      => array(
						'default' => 'yes',
					),
					'font_size'       => array(
						'default' => array(
							'size' => 2,
							'unit' => 'rem',
						),
					),
					'font_weight'     => array(
						'default' => 'bold',
					),
					'font_style'      => array(
						'default' => 'normal',
					),
					'text_decoration' => array(
						'default' => 'none',
					),
					'text_transform'  => array(
						'default' => 'none',
					),
					'line_height'     => array(
						'default' => array(
							'size' => 3,
							'unit' => 'rem',
						),
					),
				),
				'selector'       => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link > span',
			)
		);

		$this->add_control(
			'style_normal_typography_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link > span' => 'color: {{VALUE}};',
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link .wpzoom-elementor-addons-featured-category-post-count::before' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => 'style_normal_typography_stroke',
				'label'    => esc_html__( 'Stroke', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link > span',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'style_normal_typography_shadow',
				'label'    => esc_html__( 'Shadow', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link > span',
			)
		);

		$this->add_control(
			'style_normal_typography_align',
			array(
				'label'                => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'top_left'      => array(
						'title' => esc_html__( 'Top Left', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-up',
					),
					'top_center'    => array(
						'title' => esc_html__( 'Top Center', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-up',
					),
					'top_right'     => array(
						'title' => esc_html__( 'Top Right', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-up',
					),
					'center_left'   => array(
						'title' => esc_html__( 'Center Left', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-left',
					),
					'center_center' => array(
						'title' => esc_html__( 'Center Center', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-plus',
					),
					'center_right'  => array(
						'title' => esc_html__( 'Center Right', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-right',
					),
					'bottom_left'   => array(
						'title' => esc_html__( 'Bottom Left', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-down',
					),
					'bottom_center' => array(
						'title' => esc_html__( 'Bottom Center', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-down',
					),
					'bottom_right'  => array(
						'title' => esc_html__( 'Bottom Right', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-down',
					),
				),
				'default'              => 'center_center',
				'selectors_dictionary' => array(
					'top_left'      => 'align-items: flex-start; justify-content: flex-start; text-align: left',
					'top_center'    => 'align-items: flex-start; justify-content: center; text-align: center',
					'top_right'     => 'align-items: flex-start; justify-content: flex-end; text-align: right',
					'center_left'   => 'align-items: center; justify-content: flex-start; text-align: left',
					'center_center' => 'align-items: center; justify-content: center; text-align: center',
					'center_right'  => 'align-items: center; justify-content: flex-end; text-align: right',
					'bottom_left'   => 'align-items: flex-end; justify-content: flex-start; text-align: left',
					'bottom_center' => 'align-items: flex-end; justify-content: center; text-align: center',
					'bottom_right'  => 'align-items: flex-end; justify-content: flex-end; text-align: right',
				),
				'selectors'            => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link strong' => '{{VALUE}}',
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link .wpzoom-elementor-addons-featured-category-post-count' => '{{VALUE}}',
				),
				'toggle'               => true,
				'classes'              => 'wpzoom-elementor-addons-backend-align-control',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'section_style_typography_tabs_hover',
			array(
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'style_hover_typography',
				'label'          => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'fields_options' => array(
					'typography'      => array(
						'default' => 'yes',
					),
					'font_size'       => array(
						'default' => array(
							'size' => 2,
							'unit' => 'rem',
						),
					),
					'font_weight'     => array(
						'default' => 'bold',
					),
					'font_style'      => array(
						'default' => 'normal',
					),
					'text_decoration' => array(
						'default' => 'none',
					),
					'text_transform'  => array(
						'default' => 'none',
					),
					'line_height'     => array(
						'default' => array(
							'size' => 3,
							'unit' => 'rem',
						),
					),
				),
				'selector'       => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover > span',
			)
		);

		$this->add_control(
			'style_hover_typography_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover > span' => 'color: {{VALUE}};',
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover .wpzoom-elementor-addons-featured-category-post-count::before' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => 'style_hover_typography_stroke',
				'label'    => esc_html__( 'Stroke', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover > span',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'style_hover_typography_shadow',
				'label'    => esc_html__( 'Shadow', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover > span',
			)
		);

		$this->add_control(
			'style_hover_typography_align',
			array(
				'label'                => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'top_left'      => array(
						'title' => esc_html__( 'Top Left', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-up',
					),
					'top_center'    => array(
						'title' => esc_html__( 'Top Center', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-up',
					),
					'top_right'     => array(
						'title' => esc_html__( 'Top Right', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-up',
					),
					'center_left'   => array(
						'title' => esc_html__( 'Center Left', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-left',
					),
					'center_center' => array(
						'title' => esc_html__( 'Center Center', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-plus',
					),
					'center_right'  => array(
						'title' => esc_html__( 'Center Right', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-right',
					),
					'bottom_left'   => array(
						'title' => esc_html__( 'Bottom Left', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-down',
					),
					'bottom_center' => array(
						'title' => esc_html__( 'Bottom Center', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-down',
					),
					'bottom_right'  => array(
						'title' => esc_html__( 'Bottom Right', 'wpzoom-elementor-addons' ),
						'icon'  => 'fa fa-arrow-down',
					),
				),
				'default'              => 'center_center',
				'selectors_dictionary' => array(
					'top_left'      => 'align-items: flex-start; justify-content: flex-start; text-align: left',
					'top_center'    => 'align-items: flex-start; justify-content: center; text-align: center',
					'top_right'     => 'align-items: flex-start; justify-content: flex-end; text-align: right',
					'center_left'   => 'align-items: center; justify-content: flex-start; text-align: left',
					'center_center' => 'align-items: center; justify-content: center; text-align: center',
					'center_right'  => 'align-items: center; justify-content: flex-end; text-align: right',
					'bottom_left'   => 'align-items: flex-end; justify-content: flex-start; text-align: left',
					'bottom_center' => 'align-items: flex-end; justify-content: center; text-align: center',
					'bottom_right'  => 'align-items: flex-end; justify-content: flex-end; text-align: right',
				),
				'selectors'            => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover strong' => '{{VALUE}}',
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover .wpzoom-elementor-addons-featured-category-post-count'  => '{{VALUE}}',
				),
				'toggle'               => true,
				'classes'              => 'wpzoom-elementor-addons-backend-align-control',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_image',
			array(
				'label' => esc_html__( 'Image', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'section_style_image_tabs' );

		$this->start_controls_tab(
			'section_style_image_tabs_normal',
			array(
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'style_normal_image_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'style_normal_image_position',
			array(
				'label'       => esc_html__( 'Position', 'wpzoom-elementor-addons' ),
				'description' => wp_kses_post( __( 'Use "Default" to apply the focal point set in the <a href="edit-tags.php?taxonomy=category" target="_blank">category settings</a>.', 'wpzoom-elementor-addons' ) ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''              => esc_html__( 'Default (from category)', 'wpzoom-elementor-addons' ),
					'top left'      => esc_html__( 'Top Left', 'wpzoom-elementor-addons' ),
					'top center'    => esc_html__( 'Top Center', 'wpzoom-elementor-addons' ),
					'top right'     => esc_html__( 'Top Right', 'wpzoom-elementor-addons' ),
					'center left'   => esc_html__( 'Center Left', 'wpzoom-elementor-addons' ),
					'center center' => esc_html__( 'Center Center', 'wpzoom-elementor-addons' ),
					'center right'  => esc_html__( 'Center Right', 'wpzoom-elementor-addons' ),
					'bottom left'   => esc_html__( 'Bottom Left', 'wpzoom-elementor-addons' ),
					'bottom center' => esc_html__( 'Bottom Center', 'wpzoom-elementor-addons' ),
					'bottom right'  => esc_html__( 'Bottom Right', 'wpzoom-elementor-addons' ),
				),
				'selectors'   => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'background-position: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'style_normal_image_repeat',
			array(
				'label'     => esc_html__( 'Repeat', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'no-repeat',
				'options'   => array(
					'no-repeat' => esc_html__( 'Don&rsquo;t Repeat', 'wpzoom-elementor-addons' ),
					'repeat-x'  => esc_html__( 'Repeat Horizontally', 'wpzoom-elementor-addons' ),
					'repeat-y'  => esc_html__( 'Repeat Vertically', 'wpzoom-elementor-addons' ),
					'repeat'    => esc_html__( 'Repeat Both', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'background-repeat: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'style_normal_image_attachment',
			array(
				'label'     => esc_html__( 'Attachment', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'scroll',
				'options'   => array(
					'scroll' => esc_html__( 'Scroll', 'wpzoom-elementor-addons' ),
					'fixed'  => esc_html__( 'Fixed', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'background-attachment: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'style_normal_image_size',
			array(
				'label'     => esc_html__( 'Size', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array(
					'auto'    => esc_html__( 'Auto', 'wpzoom-elementor-addons' ),
					'cover'   => esc_html__( 'Cover', 'wpzoom-elementor-addons' ),
					'contain' => esc_html__( 'Contain', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'background-size: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'section_style_image_tabs_hover',
			array(
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'style_hover_image_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Background Color', 'wpzoom-elementor-addons' ),
				'default'   => '#000000',
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'style_hover_image_position',
			array(
				'label'       => esc_html__( 'Position', 'wpzoom-elementor-addons' ),
				'description' => wp_kses_post( __( 'Use "Default" to apply the focal point set in the <a href="edit-tags.php?taxonomy=category" target="_blank">category settings</a>.', 'wpzoom-elementor-addons' ) ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''              => esc_html__( 'Default (from category)', 'wpzoom-elementor-addons' ),
					'top left'      => esc_html__( 'Top Left', 'wpzoom-elementor-addons' ),
					'top center'    => esc_html__( 'Top Center', 'wpzoom-elementor-addons' ),
					'top right'     => esc_html__( 'Top Right', 'wpzoom-elementor-addons' ),
					'center left'   => esc_html__( 'Center Left', 'wpzoom-elementor-addons' ),
					'center center' => esc_html__( 'Center Center', 'wpzoom-elementor-addons' ),
					'center right'  => esc_html__( 'Center Right', 'wpzoom-elementor-addons' ),
					'bottom left'   => esc_html__( 'Bottom Left', 'wpzoom-elementor-addons' ),
					'bottom center' => esc_html__( 'Bottom Center', 'wpzoom-elementor-addons' ),
					'bottom right'  => esc_html__( 'Bottom Right', 'wpzoom-elementor-addons' ),
				),
				'selectors'   => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'background-position: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'style_hover_image_repeat',
			array(
				'label'     => esc_html__( 'Repeat', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'no-repeat',
				'options'   => array(
					'no-repeat' => esc_html__( 'Don&rsquo;t Repeat', 'wpzoom-elementor-addons' ),
					'repeat-x'  => esc_html__( 'Repeat Horizontally', 'wpzoom-elementor-addons' ),
					'repeat-y'  => esc_html__( 'Repeat Vertically', 'wpzoom-elementor-addons' ),
					'repeat'    => esc_html__( 'Repeat Both', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'background-repeat: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'style_hover_image_attachment',
			array(
				'label'     => esc_html__( 'Attachment', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'scroll',
				'options'   => array(
					'scroll' => esc_html__( 'Scroll', 'wpzoom-elementor-addons' ),
					'fixed'  => esc_html__( 'Fixed', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'background-attachment: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'style_hover_image_size',
			array(
				'label'     => esc_html__( 'Size', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array(
					'auto'    => esc_html__( 'Auto', 'wpzoom-elementor-addons' ),
					'cover'   => esc_html__( 'Cover', 'wpzoom-elementor-addons' ),
					'contain' => esc_html__( 'Contain', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'background-size: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_overlay',
			array(
				'label' => esc_html__( 'Overlay', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'section_style_overlay_tabs' );

		$this->start_controls_tab(
			'section_style_overlay_tabs_normal',
			array(
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'style_normal_overlay_background',
				'label'          => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'fields_options' => array(
					'background' => array(
						'default' => 'classic',
					),
					'color'      => array(
						'default' => '#000000',
					),
				),
				'selector'       => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link::before',
			)
		);

		$this->add_control(
			'style_normal_overlay_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'default'   => array(
					'size' => 0.5,
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link::before' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Backdrop_Filter::get_type(),
			array(
				'name'     => 'style_normal_overlay_filters',
				'label'    => esc_html__( 'Filters', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link::before',
			)
		);

		$this->add_control(
			'style_normal_overlay_blend_mode',
			array(
				'label'     => esc_html__( 'Blend Mode', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
					'multiply'    => esc_html__( 'Multiply', 'wpzoom-elementor-addons' ),
					'screen'      => esc_html__( 'Screen', 'wpzoom-elementor-addons' ),
					'overlay'     => esc_html__( 'Overlay', 'wpzoom-elementor-addons' ),
					'darken'      => esc_html__( 'Darken', 'wpzoom-elementor-addons' ),
					'lighten'     => esc_html__( 'Lighten', 'wpzoom-elementor-addons' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'wpzoom-elementor-addons' ),
					'saturation'  => esc_html__( 'Saturation', 'wpzoom-elementor-addons' ),
					'color'       => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
					'difference'  => esc_html__( 'Difference', 'wpzoom-elementor-addons' ),
					'exclusion'   => esc_html__( 'Exclusion', 'wpzoom-elementor-addons' ),
					'hue'         => esc_html__( 'Hue', 'wpzoom-elementor-addons' ),
					'luminosity'  => esc_html__( 'Luminosity', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link::before' => 'mix-blend-mode: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'section_style_overlay_tabs_hover',
			array(
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'style_hover_overlay_background',
				'label'          => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'fields_options' => array(
					'background' => array(
						'default' => 'classic',
					),
					'color'      => array(
						'default' => '#000000',
					),
				),
				'selector'       => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover::before',
			)
		);

		$this->add_control(
			'style_hover_overlay_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'default'   => array(
					'size' => 0.5,
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover::before' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Backdrop_Filter::get_type(),
			array(
				'name'     => 'style_hover_overlay_filters',
				'label'    => esc_html__( 'Filters', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover::before',
			)
		);

		$this->add_control(
			'style_hover_overlay_blend_mode',
			array(
				'label'     => esc_html__( 'Blend Mode', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
					'multiply'    => esc_html__( 'Multiply', 'wpzoom-elementor-addons' ),
					'screen'      => esc_html__( 'Screen', 'wpzoom-elementor-addons' ),
					'overlay'     => esc_html__( 'Overlay', 'wpzoom-elementor-addons' ),
					'darken'      => esc_html__( 'Darken', 'wpzoom-elementor-addons' ),
					'lighten'     => esc_html__( 'Lighten', 'wpzoom-elementor-addons' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'wpzoom-elementor-addons' ),
					'saturation'  => esc_html__( 'Saturation', 'wpzoom-elementor-addons' ),
					'color'       => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
					'difference'  => esc_html__( 'Difference', 'wpzoom-elementor-addons' ),
					'exclusion'   => esc_html__( 'Exclusion', 'wpzoom-elementor-addons' ),
					'hue'         => esc_html__( 'Hue', 'wpzoom-elementor-addons' ),
					'luminosity'  => esc_html__( 'Luminosity', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover::before' => 'mix-blend-mode: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_box',
			array(
				'label' => esc_html__( 'Box', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'style_aspect_ratio',
			array(
				'label'       => esc_html__( 'Aspect Ratio', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '16/9',
				'options'     => array(
					'16/9' => esc_html__( '16:9 (Landscape)', 'wpzoom-elementor-addons' ),
					'4/3'  => esc_html__( '4:3 (Standard)', 'wpzoom-elementor-addons' ),
					'1/1'  => esc_html__( '1:1 (Square)', 'wpzoom-elementor-addons' ),
					'3/4'  => esc_html__( '3:4 (Portrait)', 'wpzoom-elementor-addons' ),
					'9/16' => esc_html__( '9:16 (Tall Portrait)', 'wpzoom-elementor-addons' ),
					'auto' => esc_html__( 'Auto (use Min Height)', 'wpzoom-elementor-addons' ),
				),
				'selectors'   => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link > span' => 'aspect-ratio: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'style_min_height',
			array(
				'label'      => esc_html__( 'Min Height', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 100,
						'max' => 1000,
					),
					'vh' => array(
						'min' => 10,
						'max' => 100,
					),
					'em' => array(
						'min' => 5,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link > span' => 'min-height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'style_aspect_ratio' => 'auto',
				),
			)
		);

		$this->start_controls_tabs( 'section_style_box_tabs' );

		$this->start_controls_tab(
			'section_style_box_tabs_normal',
			array(
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_responsive_control(
			'style_normal_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'default'    => array(
					'top'      => 2,
					'right'    => 2,
					'bottom'   => 2,
					'left'     => 2,
					'unit'     => 'rem',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'style_normal_border',
				'label'    => esc_html__( 'Border', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link',
			)
		);

		$this->add_control(
			'style_normal_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'default'    => array(
					'top'      => 0,
					'right'    => 0,
					'bottom'   => 0,
					'left'     => 0,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'style_normal_box_shadow',
				'label'    => esc_html__( 'Shadow', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link',
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'style_normal_filters',
				'label'    => esc_html__( 'Filters', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link',
			)
		);

		$this->add_control(
			'style_normal_blend_mode',
			array(
				'label'     => esc_html__( 'Blend Mode', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
					'multiply'    => esc_html__( 'Multiply', 'wpzoom-elementor-addons' ),
					'screen'      => esc_html__( 'Screen', 'wpzoom-elementor-addons' ),
					'overlay'     => esc_html__( 'Overlay', 'wpzoom-elementor-addons' ),
					'darken'      => esc_html__( 'Darken', 'wpzoom-elementor-addons' ),
					'lighten'     => esc_html__( 'Lighten', 'wpzoom-elementor-addons' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'wpzoom-elementor-addons' ),
					'saturation'  => esc_html__( 'Saturation', 'wpzoom-elementor-addons' ),
					'color'       => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
					'difference'  => esc_html__( 'Difference', 'wpzoom-elementor-addons' ),
					'exclusion'   => esc_html__( 'Exclusion', 'wpzoom-elementor-addons' ),
					'hue'         => esc_html__( 'Hue', 'wpzoom-elementor-addons' ),
					'luminosity'  => esc_html__( 'Luminosity', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'mix-blend-mode: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'style_normal_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'default'   => array(
					'size' => 1,
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'section_style_box_tabs_hover',
			array(
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_responsive_control(
			'style_hover_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'default'    => array(
					'top'      => 2,
					'right'    => 2,
					'bottom'   => 2,
					'left'     => 2,
					'unit'     => 'rem',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'style_hover_border',
				'label'    => esc_html__( 'Border', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover',
			)
		);

		$this->add_control(
			'style_hover_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'default'    => array(
					'top'      => 0,
					'right'    => 0,
					'bottom'   => 0,
					'left'     => 0,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'style_hover_box_shadow',
				'label'    => esc_html__( 'Shadow', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'style_hover_filters',
				'label'    => esc_html__( 'Filters', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover',
			)
		);

		$this->add_control(
			'style_hover_blend_mode',
			array(
				'label'     => esc_html__( 'Blend Mode', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
					'multiply'    => esc_html__( 'Multiply', 'wpzoom-elementor-addons' ),
					'screen'      => esc_html__( 'Screen', 'wpzoom-elementor-addons' ),
					'overlay'     => esc_html__( 'Overlay', 'wpzoom-elementor-addons' ),
					'darken'      => esc_html__( 'Darken', 'wpzoom-elementor-addons' ),
					'lighten'     => esc_html__( 'Lighten', 'wpzoom-elementor-addons' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'wpzoom-elementor-addons' ),
					'saturation'  => esc_html__( 'Saturation', 'wpzoom-elementor-addons' ),
					'color'       => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
					'difference'  => esc_html__( 'Difference', 'wpzoom-elementor-addons' ),
					'exclusion'   => esc_html__( 'Exclusion', 'wpzoom-elementor-addons' ),
					'hue'         => esc_html__( 'Hue', 'wpzoom-elementor-addons' ),
					'luminosity'  => esc_html__( 'Luminosity', 'wpzoom-elementor-addons' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'mix-blend-mode: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'style_hover_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'default'   => array(
					'size' => 1,
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link:hover' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_animation',
			array(
				'label' => esc_html__( 'Animation', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'style_transition_duration',
			array(
				'label'     => esc_html__( 'Transition Duration', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 0.3,
				),
				'range'     => array(
					'px' => array(
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .wpzoom-elementor-addons-featured-category > h3 .wpzoom-elementor-addons-featured-category-link' => 'transition-duration: {{SIZE}}s',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the Widget.
	 *
	 * Renders the widget on the frontend.
	 *
	 * @since  1.2.0
	 * @access public
	 * @param  array $instance The instance variables.
	 * @return void
	 */
	protected function render( $instance = array() ) {
		// Get settings.
		$settings = $this->get_settings_for_display();

		$cat = trim( $settings['category'] );

		// Exit early if no category is selected.
		if ( ! in_array( $cat, array_keys( array_filter( $this->get_all_categories() ) ), true ) ) {
			return;
		}

		$cat_data = get_category_by_slug( $cat );

		// Exit early if the given category is invalid.
		if ( false === $cat_data ) {
			return;
		}

		$term_meta = get_term_meta( $cat_data->term_id, 'wpz_cover_image_id', true );
		$image_id  = false !== $term_meta ? absint( $term_meta ) : 0;
		$image_url = '';

		if ( $image_id > 0 ) {
			$image_size = isset( $settings['category_image_size'] ) ? trim( $settings['category_image_size'] ) : 'large';
			$attachment = wp_get_attachment_image_src( $image_id, $image_size );

			if ( false !== $attachment && is_array( $attachment ) && count( $attachment ) > 2 ) {
				$image_url = trim( $attachment[0] );
			}
		}

		$term_pos       = get_term_meta( $cat_data->term_id, 'wpz_cover_image_pos', true );
		$image_position = false !== $term_pos ? trim( $term_pos ) : '';

		$attrs    = '';
		$classes  = 'wpzoom-elementor-addons-featured-category-link';
		$style    = '';
		$cat_link = get_category_link( $cat_data );

		if ( ! empty( $cat_link ) ) {
			$classes .= ' is-linked';
			$attrs   .= ' href="' . esc_url( $cat_link ) . '"';
		} else {
			$classes .= ' not-linked';
		}

		if ( ! empty( $image_url ) ) {
			$classes .= ' has-image';
			$style   .= sprintf( 'background-image:url(\'%s\');', esc_url( $image_url ) );
		} else {
			$classes .= ' no-image';
		}

		if ( ! empty( $image_position ) ) {
			$style .= sprintf( 'background-position:%s;', esc_attr( $image_position ) );
		}

		if ( ! empty( $classes ) ) {
			$attrs .= ' class="' . $classes . '"';
		}

		if ( ! empty( $style ) ) {
			$style  = sprintf( ' style="%s"', $style );
			$attrs .= $style;
		}

		$show_post_count = isset( $settings['show_post_count'] ) && 'yes' === $settings['show_post_count'];
		$post_count      = '';

		if ( $show_post_count ) {
			$format_string = esc_html( isset( $settings['post_count_format'] ) ? trim( $settings['post_count_format'] ) : __( '% Posts', 'wpzoom-elementor-addons' ) );
			$cat_count     = property_exists( $cat_data, 'count' ) ? intval( $cat_data->count ) : 0;
			$post_count    = '<em class="wpzoom-elementor-addons-featured-category-post-count">' . str_replace( '%', $cat_count, $format_string ) . '</em>';
		}

		// phpcs:disable WordPress.Security.EscapeOutput
		?>
		<div class="wpzoom-elementor-addons-featured-category">
			<h3>
				<?php
				printf(
					'<a%2$s><span><strong><span>%3$s%4$s</span></strong></span></a>',
					esc_url( get_category_link( $cat_data ) ),
					$attrs,
					esc_html( $cat_data->name ),
					$post_count
				);
				?>
			</h3>
		</div><!-- //.wpzoom-elementor-addons-featured-category -->
		<?php
		// phpcs:enable WordPress.Security.EscapeOutput
	}
}

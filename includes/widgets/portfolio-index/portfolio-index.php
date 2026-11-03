<?php
/**
 * Portfolio Index widget.
 *
 * A numbered list of portfolio projects with a project panel beside it:
 * hovering a title previews the project (its hover video plays in the panel),
 * the preview stays while the cursor moves to the panel, and the play button plays the project's video.
 *
 * @package WPZOOM_Elementor_Addons
 */

namespace WPZOOMElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

/**
 * Portfolio Index widget class.
 */
class Portfolio_Index extends Widget_Base {

	/**
	 * Constructor.
	 *
	 * @param array      $data Widget data.
	 * @param array|null $args Optional arguments.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style(
			'wpzoom-elementor-addons-css-frontend-portfolio-index',
			plugins_url( 'frontend.css', __FILE__ ),
			array(),
			WPZOOM_EL_ADDONS_VER
		);
		wp_register_script(
			'wpzoom-elementor-addons-js-frontend-portfolio-index',
			plugins_url( 'frontend.js', __FILE__ ),
			array(),
			WPZOOM_EL_ADDONS_VER,
			true
		);
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wpzoom-elementor-addons-portfolio-index';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Portfolio Index', 'wpzoom-elementor-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-bullet-list';
	}

	/**
	 * Widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'wpzoom-elementor-addons-inspiro' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'portfolio', 'index', 'list', 'projects', 'video', 'film' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'wpzoom-elementor-addons-css-frontend-portfolio-index' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'wpzoom-elementor-addons-js-frontend-portfolio-index' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		if ( ! WPZOOM_Elementor_Widgets::is_supported_theme() ) {
			$this->start_controls_section( 'section_restricted', array( 'label' => esc_html__( 'Widget not available', 'wpzoom-elementor-addons' ) ) );
			$this->add_control(
				'restricted_widget_text',
				array(
					'raw'             => wp_kses_post( __( 'This widget is supported only by the <a href="https://www.wpzoom.com/themes/inspiro/?utm_source=wpadmin&utm_medium=wpzoom-elementor-addons&utm_campaign=theme-required">"Inspiro Premium"</a> and <a href="https://www.wpzoom.com/themes/inspiro-pro/?utm_source=wpadmin&utm_medium=wpzoom-elementor-addons&utm_campaign=theme-required">"Inspiro PRO"</a> themes', 'wpzoom-elementor-addons' ) ),
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'elementor-descriptor',
				)
			);
			$this->end_controls_section();
			return;
		}

		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Content controls.
	 *
	 * @return void
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'section_projects',
			array(
				'label' => esc_html__( 'Projects', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'category',
			array(
				'label'   => esc_html__( 'Category', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_portfolio_categories(),
				'default' => '0',
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => esc_html__( 'Number of Projects', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 100,
				'default' => 12,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__( 'Order', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'date'       => esc_html__( 'Newest first', 'wpzoom-elementor-addons' ),
					'menu_order' => esc_html__( 'Custom order (Portfolio > Order)', 'wpzoom-elementor-addons' ),
					'title'      => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
				),
				'default' => 'date',
			)
		);

		$this->add_control(
			'list_label',
			array(
				'label'       => esc_html__( 'List Heading', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Latest projects', 'wpzoom-elementor-addons' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'show_numbers',
			array(
				'label'        => esc_html__( 'Show Numbers', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_rail',
			array(
				'label'        => esc_html__( 'Show Dotted Rail', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_panel',
			array(
				'label' => esc_html__( 'Project Panel', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'click_action',
			array(
				'label'       => esc_html__( 'On Title Click', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'panel' => esc_html__( 'Open the project in the panel', 'wpzoom-elementor-addons' ),
					'post'  => esc_html__( 'Go to the project page', 'wpzoom-elementor-addons' ),
				),
				'default'     => 'panel',
				'description' => esc_html__( 'Hovering a title always previews the project in the panel. On phones the panel is hidden and titles link to the project page (opened in the modal when Portfolio Post Style is set to Modal).', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'open_first',
			array(
				'label'        => esc_html__( 'Show the First Project on Load', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'preview_video',
			array(
				'label'        => esc_html__( 'Play the Hover Video in the Panel', 'wpzoom-elementor-addons' ),
				'description'  => esc_html__( 'Uses each project\'s "Video Background on Hover". Projects without one show their featured image.', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'media_ratio',
			array(
				'label'   => esc_html__( 'Media Aspect Ratio', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'16/9'   => '16:9',
					'2.39/1' => esc_html__( 'Cinema (2.39:1)', 'wpzoom-elementor-addons' ),
					'4/3'    => '4:3',
					'1/1'    => esc_html__( 'Square', 'wpzoom-elementor-addons' ),
				),
				'default' => '16/9',
			)
		);

		$this->add_control(
			'media_radius',
			array(
				'label'     => esc_html__( 'Media Corner Radius', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'selectors' => array( '{{WRAPPER}} .wpz-pindex__media' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'show_year',
			array(
				'label'        => esc_html__( 'Show Year', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_details',
			array(
				'label'        => esc_html__( 'Show Director & Client', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'        => esc_html__( 'Show Excerpt', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'more_text',
			array(
				'label'   => esc_html__( 'Project Link Text', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Open project', 'wpzoom-elementor-addons' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style controls.
	 *
	 * @return void
	 */
	protected function register_style_controls() {
		$this->start_controls_section(
			'section_style_layout',
			array(
				'label' => esc_html__( 'Layout', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'list_width',
			array(
				'label'      => esc_html__( 'List Width', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array( '%' => array( 'min' => 20, 'max' => 70 ) ),
				'default'    => array( 'unit' => '%', 'size' => 36 ),
				'selectors'  => array( '{{WRAPPER}} .wpz-pindex' => '--wpz-pindex-list: {{SIZE}}%;' ),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => esc_html__( 'Gap', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vw' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 80 ),
				'selectors'  => array( '{{WRAPPER}} .wpz-pindex' => '--wpz-pindex-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'panel_top',
			array(
				'label'       => esc_html__( 'Panel Sticky Offset', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'Distance from the top of the window while the list scrolls.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 300 ) ),
				'default'     => array( 'unit' => 'px', 'size' => 120 ),
				'selectors'   => array( '{{WRAPPER}} .wpz-pindex' => '--wpz-pindex-top: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_list',
			array(
				'label' => esc_html__( 'List', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => esc_html__( 'Heading Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-pindex__label' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'label'    => esc_html__( 'Heading Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-pindex__label',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .wpz-pindex__link' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_active_color',
			array(
				'label'     => esc_html__( 'Active Title Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpz-pindex__item.is-active .wpz-pindex__link, {{WRAPPER}} .wpz-pindex__link:hover, {{WRAPPER}} .wpz-pindex__link:focus-visible' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_active_italic',
			array(
				'label'        => esc_html__( 'Italic Active Title', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Title Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-pindex__title',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'number_typography',
				'label'    => esc_html__( 'Number Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-pindex__num',
			)
		);

		$this->add_responsive_control(
			'item_spacing',
			array(
				'label'      => esc_html__( 'Space Between Titles', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 14 ),
				'selectors'  => array( '{{WRAPPER}} .wpz-pindex' => '--wpz-pindex-item-gap: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'rail_color',
			array(
				'label'     => esc_html__( 'Rail Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-pindex' => '--wpz-pindex-rail: {{VALUE}};' ),
				'condition' => array( 'show_rail' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_panel',
			array(
				'label' => esc_html__( 'Project Panel', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'panel_title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-pindex__panel-title, {{WRAPPER}} .wpz-pindex__year' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'panel_title_typography',
				'label'    => esc_html__( 'Title Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-pindex__panel-title',
			)
		);

		$this->add_control(
			'panel_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-pindex__text, {{WRAPPER}} .wpz-pindex__details' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'panel_text_typography',
				'label'    => esc_html__( 'Text Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-pindex__text, {{WRAPPER}} .wpz-pindex__details',
			)
		);

		$this->add_control(
			'more_color',
			array(
				'label'     => esc_html__( 'Link Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-pindex__more' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'media_radius',
			array(
				'label'      => esc_html__( 'Media Corner Radius', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .wpz-pindex__media' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Portfolio categories for the Category control.
	 *
	 * @return array
	 */
	protected function get_portfolio_categories() {
		$options = array( '0' => esc_html__( 'All', 'wpzoom-elementor-addons' ) );
		$terms   = get_terms(
			array(
				'taxonomy'   => 'portfolio',
				'hide_empty' => false,
			)
		);

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name . ' (' . $term->count . ')';
			}
		}

		return $options;
	}

	/**
	 * The project's hover video (Video Background on Hover), if it has one.
	 *
	 * @param int $post_id Portfolio post ID.
	 * @return string
	 */
	protected function get_preview_video( $post_id ) {
		if ( function_exists( 'init_video_background_on_hover_module' ) ) {
			$url = init_video_background_on_hover_module()->get_data( $post_id );
			if ( $url && preg_match( '/\.(mp4|webm|mov)(\?|$)/i', $url ) ) {
				return $url;
			}
		}

		$url = get_post_meta( $post_id, 'wpzoom_portfolio_video_background_mp4', true );

		return $url ? $url : '';
	}

	/**
	 * Player markup for the project's lightbox video: a self-hosted file or a
	 * YouTube / Vimeo embed. Rendered inside a <template> so nothing loads
	 * until the visitor presses play.
	 *
	 * @param int $post_id Portfolio post ID.
	 * @return string
	 */
	protected function get_player( $post_id ) {
		$type = get_post_meta( $post_id, 'wpzoom_portfolio_popup_video_type', true );
		$file = get_post_meta( $post_id, 'wpzoom_portfolio_video_popup_url_mp4', true );
		if ( ! $file ) {
			$file = get_post_meta( $post_id, 'wpzoom_portfolio_video_popup_url_webm', true );
		}
		$url = get_post_meta( $post_id, 'wpzoom_portfolio_video_popup_url', true );

		if ( $file && ( 'self_hosted' === $type || ! $url ) ) {
			// wp-video-shortcode: the frontend script hands the element to WordPress's
			// MediaElement player, which the theme's Video Player Style skins.
			if ( ! WPZOOM_Elementor_Widgets::use_mediaelement_player() ) {
				return sprintf( '<video src="%s" controls playsinline preload="none"></video>', esc_url( $file ) );
			}
			wp_enqueue_style( 'wp-mediaelement' );
			wp_enqueue_script( 'wp-mediaelement' );
			return sprintf( '<video class="wp-video-shortcode" src="%s" controls playsinline preload="none"></video>', esc_url( $file ) );
		}

		if ( $url ) {
			global $wp_embed;
			$html = ( $wp_embed instanceof \WP_Embed ) ? $wp_embed->shortcode( array( 'width' => 1600 ), $url ) : '';

			if ( $html && false !== stripos( $html, '<iframe' ) ) {
				// Start playing when the visitor presses play.
				return preg_replace_callback(
					'/src="([^"]+)"/',
					function ( $m ) {
						return 'src="' . esc_url( add_query_arg( array( 'autoplay' => 1 ), html_entity_decode( $m[1] ) ) ) . '" allow="autoplay; fullscreen; picture-in-picture"';
					},
					$html,
					1
				);
			}
		}

		return '';
	}

	/**
	 * Render the widget.
	 *
	 * @return void
	 */
	protected function render() {
		if ( ! WPZOOM_Elementor_Widgets::is_supported_theme() ) {
			if ( current_user_can( 'edit_posts' ) ) {
				echo '<p>' . esc_html__( 'The Portfolio Index widget needs the Inspiro Premium or Inspiro PRO theme.', 'wpzoom-elementor-addons' ) . '</p>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();

		$args = array(
			'post_type'           => 'portfolio_item',
			'posts_per_page'      => max( 1, (int) $settings['count'] ),
			'orderby'             => $settings['orderby'],
			'order'               => 'title' === $settings['orderby'] || 'menu_order' === $settings['orderby'] ? 'ASC' : 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);
		if ( ! empty( $settings['category'] ) ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'portfolio',
					'field'    => 'slug',
					'terms'    => $settings['category'],
				),
			);
		}

		$query = new \WP_Query( $args );
		if ( ! $query->have_posts() ) {
			if ( current_user_can( 'edit_posts' ) ) {
				echo '<p>' . esc_html__( 'No portfolio projects found.', 'wpzoom-elementor-addons' ) . '</p>';
			}
			return;
		}

		$open_first = 'yes' === $settings['open_first'];
		$classes    = array( 'wpz-pindex' );
		if ( 'yes' === $settings['show_rail'] ) {
			$classes[] = 'wpz-pindex--rail';
		}
		if ( 'yes' === $settings['title_active_italic'] ) {
			$classes[] = 'wpz-pindex--italic';
		}

		$items  = '';
		$panels = '';
		$i      = 0;

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id  = get_the_ID();
			$active   = ( $open_first && 0 === $i ) ? ' is-active' : '';
			$title    = get_the_title();
			$link     = get_permalink();
			$number   = str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );
			$year     = get_post_meta( $post_id, 'su_portfolio_item_year', true );
			$director = get_post_meta( $post_id, 'su_portfolio_item_director', true );
			$client   = get_post_meta( $post_id, 'su_portfolio_item_client', true );
			$preview  = 'yes' === $settings['preview_video'] ? $this->get_preview_video( $post_id ) : '';
			$player   = $this->get_player( $post_id );
			$poster   = get_the_post_thumbnail_url( $post_id, 'large' );

			$items .= sprintf(
				'<li class="wpz-pindex__item%1$s" data-index="%2$d"><a class="wpz-pindex__link" href="%3$s">%4$s<span class="wpz-pindex__title">%5$s</span></a></li>',
				esc_attr( $active ),
				(int) $i,
				esc_url( $link ),
				'yes' === $settings['show_numbers'] ? '<span class="wpz-pindex__num">' . esc_html( $number ) . '</span>' : '',
				esc_html( $title )
			);

			$media = '';
			if ( $poster ) {
				$media .= sprintf( '<img class="wpz-pindex__poster" src="%s" alt="" loading="lazy" decoding="async">', esc_url( $poster ) );
			}
			if ( $preview ) {
				$media .= sprintf( '<video class="wpz-pindex__preview" data-src="%s" muted loop playsinline preload="none" aria-hidden="true"></video>', esc_url( $preview ) );
			}
			if ( $player ) {
				$media .= sprintf(
					'<button type="button" class="wpz-pindex__play" aria-label="%s"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg></button><template class="wpz-pindex__player">%s</template>',
					/* translators: %s: project title. */
					esc_attr( sprintf( __( 'Play %s', 'wpzoom-elementor-addons' ), $title ) ),
					$player // Escaped in get_player() / by WP_Embed.
				);
			}

			$details = '';
			if ( 'yes' === $settings['show_details'] ) {
				if ( $director ) {
					$details .= '<div><dt>' . esc_html__( 'Director', 'wpzoom-elementor-addons' ) . '</dt><dd>' . esc_html( $director ) . '</dd></div>';
				}
				if ( $client ) {
					$details .= '<div><dt>' . esc_html__( 'Client', 'wpzoom-elementor-addons' ) . '</dt><dd>' . esc_html( $client ) . '</dd></div>';
				}
			}

			$panels .= sprintf(
				'<article class="wpz-pindex__panel%1$s" data-index="%2$d"%3$s>
					<header class="wpz-pindex__head"><h2 class="wpz-pindex__panel-title">%4$s</h2>%5$s</header>
					<div class="wpz-pindex__media" style="aspect-ratio:%6$s">%7$s</div>
					%8$s%9$s%10$s
				</article>',
				esc_attr( $active ),
				(int) $i,
				$active ? '' : ' aria-hidden="true"',
				esc_html( $title ),
				( 'yes' === $settings['show_year'] && $year ) ? '<span class="wpz-pindex__year">' . esc_html( $year ) . '</span>' : '',
				esc_attr( $settings['media_ratio'] ),
				$media,
				$details ? '<dl class="wpz-pindex__details">' . $details . '</dl>' : '',
				( 'yes' === $settings['show_excerpt'] && has_excerpt() ) ? '<div class="wpz-pindex__text">' . wp_kses_post( wpautop( get_the_excerpt() ) ) . '</div>' : '',
				$settings['more_text'] ? '<a class="wpz-pindex__more" href="' . esc_url( $link ) . '">' . esc_html( $settings['more_text'] ) . ' <span aria-hidden="true">&rarr;</span></a>' : ''
			);

			$i++;
		}
		wp_reset_postdata();

		printf(
			'<div class="%1$s" data-click="%2$s">
				<div class="wpz-pindex__list">%3$s<ol class="wpz-pindex__items">%4$s</ol></div>
				<div class="wpz-pindex__stage">%5$s</div>
			</div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( 'post' === $settings['click_action'] ? 'post' : 'panel' ),
			$settings['list_label'] ? '<p class="wpz-pindex__label">' . esc_html( $settings['list_label'] ) . '</p>' : '',
			$items, // Built from escaped parts above.
			$panels // Built from escaped parts above.
		);
	}
}

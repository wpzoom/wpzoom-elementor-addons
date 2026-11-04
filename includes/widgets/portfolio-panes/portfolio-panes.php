<?php
/**
 * Portfolio Panes widget: one project per screen.
 *
 * Each project fills a full-screen pane with its hover video (or featured
 * image) and the title in the middle. Scrolling moves from pane to pane with
 * the widget's own effect — the next pane slides over the pinned one (Cover),
 * the panes dissolve through black (Fade) or slide with depth (Parallax) —
 * so no page-builder motion add-on is needed. A click opens the project
 * (the theme's modal when Portfolio Post Style is Modal) or plays its video
 * in place.
 *
 * @package WPZOOM_Elementor_Addons
 */

namespace WPZOOMElementorWidgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portfolio Panes.
 */
class Portfolio_Panes extends Widget_Base {

	/**
	 * Constructor.
	 *
	 * @param array      $data Widget data.
	 * @param array|null $args Optional arguments.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style(
			'wpzoom-elementor-addons-css-frontend-portfolio-panes',
			plugins_url( 'frontend.css', __FILE__ ),
			array(),
			WPZOOM_EL_ADDONS_VER
		);
		wp_register_script(
			'wpzoom-elementor-addons-js-frontend-portfolio-panes',
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
		return 'wpzoom-elementor-addons-portfolio-panes';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Portfolio Panes', 'wpzoom-elementor-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-slides';
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
		return array( 'portfolio', 'panes', 'fullscreen', 'scroll', 'video', 'film', 'projects' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'wpzoom-elementor-addons-css-frontend-portfolio-panes' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'wpzoom-elementor-addons-js-frontend-portfolio-panes' );
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
				'max'     => 30,
				'default' => 6,
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
		$this->end_controls_section();

		$this->start_controls_section(
			'section_panes',
			array(
				'label' => esc_html__( 'Panes', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'effect',
			array(
				'label'       => esc_html__( 'Scroll Effect', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'cover'    => esc_html__( 'Cover — the next pane slides over the current one', 'wpzoom-elementor-addons' ),
					'parallax' => esc_html__( 'Parallax — slides over with depth', 'wpzoom-elementor-addons' ),
					'fade'     => esc_html__( 'Fade — dissolves through black', 'wpzoom-elementor-addons' ),
					'none'     => esc_html__( 'None — panes stack and scroll normally', 'wpzoom-elementor-addons' ),
				),
				'default'     => 'cover',
				'description' => esc_html__( 'Visitors who asked their system for reduced motion get the stacked panes without the effect.', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'pane_height',
			array(
				'label'   => esc_html__( 'Pane Height', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'full' => esc_html__( 'Full screen', 'wpzoom-elementor-addons' ),
					'tall' => esc_html__( 'Tall (75% of the screen)', 'wpzoom-elementor-addons' ),
					'band' => esc_html__( 'Band (16:7)', 'wpzoom-elementor-addons' ),
				),
				'default' => 'full',
			)
		);
		$this->add_control(
			'snap',
			array(
				'label'        => esc_html__( 'Snap to Panes', 'wpzoom-elementor-addons' ),
				'description'  => esc_html__( 'Scrolling settles on the nearest pane.', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'effect!' => 'none' ),
			)
		);
		$this->add_control(
			'loop_play',
			array(
				'label'       => esc_html__( 'Footage', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'always' => esc_html__( 'Play the hover video on the visible pane', 'wpzoom-elementor-addons' ),
					'hover'  => esc_html__( 'Play the hover video on hover only', 'wpzoom-elementor-addons' ),
					'image'  => esc_html__( 'Featured image only', 'wpzoom-elementor-addons' ),
				),
				'default'     => 'always',
				'description' => esc_html__( 'Uses each project\'s "Video Background on Hover". Projects without one show their featured image. On phones the visible pane plays.', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'click_action',
			array(
				'label'       => esc_html__( 'On Click', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'post'   => esc_html__( 'Open the project', 'wpzoom-elementor-addons' ),
					'inline' => esc_html__( 'Play the project video in the pane', 'wpzoom-elementor-addons' ),
				),
				'default'     => 'post',
				'description' => esc_html__( 'Open the project uses the Modal post style when it is enabled in Theme Options. Play in the pane uses the Video Lightbox video of the project (self-hosted, YouTube or Vimeo).', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'play_label',
			array(
				'label'       => esc_html__( 'Button Label', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'A small label under the credits, e.g. "Play film". The whole title block is clickable either way; leave empty for none.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
			)
		);
		$this->add_control(
			'more_label',
			array(
				'label'       => esc_html__( 'Project Link Label', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'Shown next to the close button while a film plays in the pane, linking to the project page. Leave empty for none.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'View project', 'wpzoom-elementor-addons' ),
				'condition'   => array( 'click_action' => 'inline' ),
			)
		);
		$this->add_control(
			'show_dots',
			array(
				'label'        => esc_html__( 'Show Dots', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'cue_text',
			array(
				'label'       => esc_html__( 'Scroll Cue', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'A word shown at the bottom of the first pane, e.g. "Scroll". Leave empty for none.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_text',
			array(
				'label' => esc_html__( 'Title & Credits', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'title_position',
			array(
				'label'   => esc_html__( 'Position', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'center'        => esc_html__( 'Centre', 'wpzoom-elementor-addons' ),
					'bottom-left'   => esc_html__( 'Bottom left', 'wpzoom-elementor-addons' ),
					'bottom-center' => esc_html__( 'Bottom centre', 'wpzoom-elementor-addons' ),
				),
				'default' => 'center',
			)
		);
		$this->add_control(
			'show_credit',
			array(
				'label'        => esc_html__( 'Show Credit Line', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'credit_position',
			array(
				'label'     => esc_html__( 'Credit Line', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'above' => esc_html__( 'Above the title', 'wpzoom-elementor-addons' ),
					'below' => esc_html__( 'Below the title', 'wpzoom-elementor-addons' ),
				),
				'default'   => 'below',
				'condition' => array( 'show_credit' => 'yes' ),
			)
		);
		foreach ( array(
			'credit_client'   => esc_html__( 'Client', 'wpzoom-elementor-addons' ),
			'credit_director' => esc_html__( 'Director', 'wpzoom-elementor-addons' ),
			'credit_year'     => esc_html__( 'Year', 'wpzoom-elementor-addons' ),
			'credit_category' => esc_html__( 'Category', 'wpzoom-elementor-addons' ),
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'credit_category' === $key ? '' : 'yes',
					'condition'    => array( 'show_credit' => 'yes' ),
				)
			);
		}
		$this->add_control(
			'show_counter',
			array(
				'label'        => esc_html__( 'Show Counter (01 / 06)', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
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
			'section_style_title',
			array(
				'label' => esc_html__( 'Title', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-pane__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .wpz-pane__title',
			)
		);
		$this->add_control(
			'title_max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 200, 'max' => 1600 ),
					'%'  => array( 'min' => 20, 'max' => 100 ),
				),
				'selectors'  => array( '{{WRAPPER}} .wpz-pane__text' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_credit',
			array(
				'label' => esc_html__( 'Credit Line & Button', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'credit_color',
			array(
				'label'     => esc_html__( 'Credit Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-pane__credit, {{WRAPPER}} .wpz-pane__counter' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'credit_typography',
				'label'    => esc_html__( 'Credit Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-pane__credit, {{WRAPPER}} .wpz-pane__counter',
			)
		);
		$this->add_control(
			'button_color',
			array(
				'label'     => esc_html__( 'Button Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-pane__button' => 'color: {{VALUE}}; border-color: currentColor;' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Button Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-pane__button',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_pane',
			array(
				'label' => esc_html__( 'Pane', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'shade_opacity',
			array(
				'label'       => esc_html__( 'Shade Over the Footage', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'A dark gradient that keeps the title readable. 0 turns it off.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.05 ) ),
				'default'     => array( 'size' => 0.7 ),
				'selectors'   => array( '{{WRAPPER}} .wpz-panes' => '--wpz-pane-shade: {{SIZE}};' ),
			)
		);
		$this->add_control(
			'dots_color',
			array(
				'label'     => esc_html__( 'Dots Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-panes__dots' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'cue_color',
			array(
				'label'     => esc_html__( 'Scroll Cue Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-panes__cue' => 'color: {{VALUE}};' ),
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
				'hide_empty' => true,
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
	protected function get_loop_video( $post_id ) {
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
	 * Player markup for the project's lightbox video, inside a <template> so
	 * nothing loads until the visitor presses play. Same sources as the
	 * Portfolio Index widget: a self-hosted file or a YouTube / Vimeo embed.
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
				echo '<p>' . esc_html__( 'The Portfolio Panes widget needs the Inspiro Premium or Inspiro PRO theme.', 'wpzoom-elementor-addons' ) . '</p>';
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

		$effect  = in_array( $settings['effect'], array( 'cover', 'parallax', 'fade', 'none' ), true ) ? $settings['effect'] : 'cover';
		$inline  = 'inline' === $settings['click_action'];
		$total   = $query->post_count;
		$classes = array(
			'wpz-panes',
			'wpz-panes--' . $effect,
			'wpz-panes--h-' . ( in_array( $settings['pane_height'], array( 'full', 'tall', 'band' ), true ) ? $settings['pane_height'] : 'full' ),
			'wpz-panes--title-' . ( in_array( $settings['title_position'], array( 'center', 'bottom-left', 'bottom-center' ), true ) ? $settings['title_position'] : 'center' ),
		);
		if ( 'yes' === $settings['snap'] && 'none' !== $effect ) {
			$classes[] = 'wpz-panes--snap';
		}
		if ( 'none' === $effect ) {
			$classes[] = 'wpz-panes--static';
		}

		$panes = '';
		$dots  = '';
		$i     = 0;

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();
			$title   = get_the_title();
			$link    = get_permalink();
			$poster  = get_the_post_thumbnail_url( $post_id, 'full' );
			$loop    = 'image' === $settings['loop_play'] ? '' : $this->get_loop_video( $post_id );
			$player  = $inline ? $this->get_player( $post_id ) : '';

			$media = '';
			if ( $poster ) {
				$media .= sprintf( '<img class="wpz-pane__poster" src="%s" alt="" %s decoding="async">', esc_url( $poster ), 0 === $i ? 'fetchpriority="high"' : 'loading="lazy"' );
			}
			if ( $loop ) {
				$media .= sprintf( '<video class="wpz-pane__loop" data-src="%s" muted loop playsinline preload="none" aria-hidden="true"></video>', esc_url( $loop ) );
			}

			$credit = '';
			if ( 'yes' === $settings['show_credit'] ) {
				$parts = array();
				if ( 'yes' === $settings['credit_client'] ) {
					$parts[] = get_post_meta( $post_id, 'su_portfolio_item_client', true );
				}
				if ( 'yes' === $settings['credit_director'] ) {
					$parts[] = get_post_meta( $post_id, 'su_portfolio_item_director', true );
				}
				if ( 'yes' === $settings['credit_year'] ) {
					$parts[] = get_post_meta( $post_id, 'su_portfolio_item_year', true );
				}
				if ( 'yes' === $settings['credit_category'] ) {
					$terms = get_the_terms( $post_id, 'portfolio' );
					if ( $terms && ! is_wp_error( $terms ) ) {
						$parts[] = $terms[0]->name;
					}
				}
				$parts = array_filter( array_map( 'trim', $parts ) );
				if ( $parts ) {
					$credit = '<p class="wpz-pane__credit">' . implode( '<span class="wpz-pane__dot" aria-hidden="true"> · </span>', array_map( 'esc_html', $parts ) ) . '</p>';
				}
			}

			$counter = 'yes' === $settings['show_counter']
				? sprintf( '<p class="wpz-pane__counter">%s<span aria-hidden="true"> / </span>%s</p>', esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ), esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ) )
				: '';

			$button = '';
			if ( $settings['play_label'] && ( ! $inline || $player ) ) {
				$button = '<span class="wpz-pane__button"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg>' . esc_html( $settings['play_label'] ) . '</span>';
			}

			$text = $counter
				. ( 'above' === $settings['credit_position'] ? $credit : '' )
				. '<h2 class="wpz-pane__title">' . esc_html( $title ) . '</h2>'
				. ( 'above' !== $settings['credit_position'] ? $credit : '' )
				. $button;

			if ( $inline && $player ) {
				$trigger = sprintf(
					'<button type="button" class="wpz-pane__text wpz-pane__trigger" aria-label="%s">%s</button><template class="wpz-pane__player">%s</template>',
					/* translators: %s: project title. */
					esc_attr( sprintf( __( 'Play %s', 'wpzoom-elementor-addons' ), $title ) ),
					$text, // Built from escaped parts above.
					$player // Escaped in get_player() / by WP_Embed.
				);
				// While the film plays the pane would otherwise be a dead end (only the close button): offer the project page too.
				if ( ! empty( $settings['more_label'] ) ) {
					$trigger .= sprintf( '<a class="wpz-pane__more" href="%s">%s</a>', esc_url( $link ), esc_html( $settings['more_label'] ) );
				}
			} else {
				$trigger = sprintf( '<a class="wpz-pane__text wpz-pane__trigger" href="%s">%s</a>', esc_url( $link ), $text );
			}

			$panes .= sprintf(
				'<section class="wpz-pane%1$s" data-index="%2$d" aria-label="%3$s">
					<div class="wpz-pane__media">%4$s</div>
					<div class="wpz-pane__shade"></div>
					<div class="wpz-pane__dim"></div>
					%5$s
				</section>',
				0 === $i ? ' is-active' : '',
				(int) $i,
				esc_attr( $title ),
				$media,
				$trigger
			);

			$dots .= sprintf(
				'<button type="button" class="wpz-panes__dot%s" data-index="%d" aria-label="%s"></button>',
				0 === $i ? ' is-active' : '',
				(int) $i,
				esc_attr( $title )
			);

			$i++;
		}
		wp_reset_postdata();

		$snaps = '';
		for ( $k = 0; $k < $total; $k++ ) {
			$snaps .= '<div class="wpz-panes__snap" style="--i:' . $k . '" aria-hidden="true"></div>';
		}

		printf(
			'<div class="%1$s" data-effect="%2$s" data-loop="%3$s" data-count="%4$d" style="--wpz-panes-count:%4$d">
				<div class="wpz-panes__stage">%5$s%6$s%7$s</div>
				%8$s
			</div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $effect ),
			esc_attr( $settings['loop_play'] ),
			(int) $total,
			$panes, // Built from escaped parts above.
			'yes' === $settings['show_dots'] && $total > 1 ? '<nav class="wpz-panes__dots" aria-label="' . esc_attr__( 'Projects', 'wpzoom-elementor-addons' ) . '">' . $dots . '</nav>' : '',
			$settings['cue_text'] ? '<div class="wpz-panes__cue"><span>' . esc_html( $settings['cue_text'] ) . '</span><i aria-hidden="true"></i></div>' : '',
			$snaps
		);
	}
}

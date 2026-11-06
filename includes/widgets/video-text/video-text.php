<?php
/**
 * Video Text widget: a video that plays through the letters.
 *
 * The text is cut out of a plate the colour of the page and the video shows
 * only inside the letters (CSS blend modes, no SVG). Every line is sized to
 * span the full width of the box, so "WE MAKE / FILMS" fills the page like a
 * wordmark, and the footage can drift behind the letters on scroll.
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
 * Video Text.
 */
class Video_Text extends Widget_Base {

	/**
	 * Constructor.
	 *
	 * @param array      $data Widget data.
	 * @param array|null $args Optional arguments.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-video-text', plugins_url( 'frontend.css', __FILE__ ), array(), WPZOOM_EL_ADDONS_VER );
		wp_register_script( 'wpzoom-elementor-addons-js-frontend-video-text', plugins_url( 'frontend.js', __FILE__ ), array(), WPZOOM_EL_ADDONS_VER, true );
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wpzoom-elementor-addons-video-text';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Video Text', 'wpzoom-elementor-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-t-letter';
	}

	/**
	 * Widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'wpzoom-elementor-addons' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'video', 'text', 'knockout', 'mask', 'wordmark', 'heading', 'hero', 'showreel' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'wpzoom-elementor-addons-css-frontend-video-text' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'wpzoom-elementor-addons-js-frontend-video-text' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		/* ---------- Content: text ---------- */
		$this->start_controls_section( 'section_text', array( 'label' => esc_html__( 'Text', 'wpzoom-elementor-addons' ) ) );

		$this->add_control(
			'text',
			array(
				'label'       => esc_html__( 'Text', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => "We make\nFilms",
				'description' => esc_html__( 'One line per row. With "Fit lines to width" on, every line is sized to span the whole box, so short words come out bigger than long ones.', 'wpzoom-elementor-addons' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
		$this->add_control(
			'html_tag',
			array(
				'label'   => esc_html__( 'HTML Tag', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'div' => 'div', 'p' => 'p' ),
				'default' => 'h1',
			)
		);
		$this->add_control(
			'fit_lines',
			array(
				'label'        => esc_html__( 'Fit lines to width', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Off: the font size from the Style tab is used instead.', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'align',
			array(
				'label'   => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array( 'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default' => 'left',
			)
		);
		$this->add_control(
			'link',
			array(
				'label'       => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'Optional: the whole block becomes a link, for example to the showreel.', 'wpzoom-elementor-addons' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->end_controls_section();

		/* ---------- Content: video ---------- */
		$this->start_controls_section( 'section_video', array( 'label' => esc_html__( 'Video', 'wpzoom-elementor-addons' ) ) );

		$this->add_control(
			'video_type',
			array(
				'label'   => esc_html__( 'Source', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'hosted'   => esc_html__( 'Media Library', 'wpzoom-elementor-addons' ),
					'external' => esc_html__( 'External URL', 'wpzoom-elementor-addons' ),
				),
				'default' => 'hosted',
			)
		);
		$this->add_control(
			'hosted_url',
			array(
				'label'       => esc_html__( 'Video File', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'condition'   => array( 'video_type' => 'hosted' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
		$this->add_control(
			'external_url',
			array(
				'label'       => esc_html__( 'Video URL', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://…/showreel.mp4', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'A direct MP4 or WebM link. YouTube and Vimeo pages cannot be used here.', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'condition'   => array( 'video_type' => 'external' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
		$this->add_control(
			'poster',
			array(
				'label'       => esc_html__( 'Poster Image', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => esc_html__( 'Shown while the video loads, and instead of it on phones when "Play on mobile" is off.', 'wpzoom-elementor-addons' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
		$this->add_control(
			'play_on_mobile',
			array(
				'label'        => esc_html__( 'Play on mobile', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'parallax',
			array(
				'label'        => esc_html__( 'Parallax', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'separator'    => 'before',
				'description'  => esc_html__( 'The footage drifts behind the letters as the page scrolls. Off automatically for visitors who prefer reduced motion.', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'parallax_strength',
			array(
				'label'     => esc_html__( 'Parallax Strength', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0.1, 'max' => 1, 'step' => 0.05 ) ),
				'default'   => array( 'size' => 0.4 ),
				'condition' => array( 'parallax' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style ---------- */
		$this->start_controls_section( 'section_style', array( 'label' => esc_html__( 'Text', 'wpzoom-elementor-addons' ), 'tab' => Controls_Manager::TAB_STYLE ) );

		$this->add_control(
			'mode',
			array(
				'label'       => esc_html__( 'Page', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'light' => esc_html__( 'Light (white page, black letters)', 'wpzoom-elementor-addons' ),
					'dark'  => esc_html__( 'Dark (black page, white letters)', 'wpzoom-elementor-addons' ),
				),
				'default'     => 'light',
				'description' => esc_html__( 'The effect is a blend with the page: pick the one that matches your background.', 'wpzoom-elementor-addons' ),
			)
		);
		$this->add_control(
			'page_color',
			array(
				'label'       => esc_html__( 'Page Color', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'description' => esc_html__( 'Only when the page is not pure white or black (an off-white or charcoal background): set it here so the box has no visible edge.', 'wpzoom-elementor-addons' ),
				'selectors'   => array( '{{WRAPPER}} .wpz-video-text' => '--wpz-vt-tint: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'typography',
				'selector'       => '{{WRAPPER}} .wpz-video-text .wpz-video-text__text, {{WRAPPER}} .wpz-video-text .wpz-video-text__line', // the lines too: em line-height and letter-spacing must resolve against each line's own size
				'fields_options' => array(
					'font_weight'    => array( 'default' => '900' ),
					'text_transform' => array( 'default' => 'uppercase' ),
					'line_height'    => array( 'default' => array( 'unit' => 'em', 'size' => 0.84 ) ),
					'letter_spacing' => array( 'default' => array( 'unit' => 'em', 'size' => -0.03 ) ),
					'font_size'      => array( 'description' => esc_html__( 'Used only when "Fit lines to width" is off.', 'wpzoom-elementor-addons' ) ),
				),
			)
		);
		$this->add_responsive_control(
			'line_gap',
			array(
				'label'     => esc_html__( 'Gap Between Lines', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'em' => array( 'min' => -0.2, 'max' => 0.5, 'step' => 0.01 ) ),
				'size_units' => array( 'em' ),
				'default'   => array( 'unit' => 'em', 'size' => 0.04 ),
				'selectors' => array( '{{WRAPPER}} .wpz-video-text' => '--wpz-vt-line-gap: {{SIZE}}em;' ),
			)
		);
		$this->add_responsive_control(
			'padding',
			array(
				'label'      => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .wpz-video-text .wpz-video-text__text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$lines = preg_split( '/\r\n|\r|\n/', (string) $settings['text'] );
		$lines = array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) );
		if ( ! $lines ) {
			return;
		}

		$src = '';
		if ( 'external' === $settings['video_type'] ) {
			$src = (string) $settings['external_url'];
		} elseif ( ! empty( $settings['hosted_url']['url'] ) ) {
			$src = $settings['hosted_url']['url'];
		}
		$poster = ! empty( $settings['poster']['url'] ) ? $settings['poster']['url'] : '';

		$tag     = in_array( $settings['html_tag'], array( 'h1', 'h2', 'h3', 'div', 'p' ), true ) ? $settings['html_tag'] : 'h1';
		$align   = in_array( $settings['align'], array( 'left', 'center', 'right' ), true ) ? $settings['align'] : 'left';
		$classes = array( 'wpz-video-text', 'wpz-video-text--' . ( 'dark' === $settings['mode'] ? 'dark' : 'light' ), 'wpz-video-text--align-' . $align );
		if ( 'yes' === $settings['fit_lines'] ) {
			$classes[] = 'wpz-video-text--fit';
		}
		if ( 'yes' === $settings['parallax'] ) {
			$classes[] = 'wpz-video-text--parallax';
		}
		if ( ! empty( $settings['page_color'] ) ) {
			$classes[] = 'wpz-video-text--tinted';
		}
		if ( 'yes' !== $settings['play_on_mobile'] && $poster ) {
			$classes[] = 'wpz-video-text--no-mobile-video';
		}

		$this->add_render_attribute( 'root', 'class', $classes );
		if ( 'yes' === $settings['parallax'] ) {
			$strength = isset( $settings['parallax_strength']['size'] ) ? (float) $settings['parallax_strength']['size'] : 0.4;
			$this->add_render_attribute( 'root', 'data-parallax', (string) max( 0.1, min( 1, $strength ) ) );
		}
		if ( 'yes' !== $settings['play_on_mobile'] && $poster ) {
			$this->add_render_attribute( 'root', 'style', '--wpz-vt-poster: url(' . esc_url( $poster ) . ');' );
		}

		echo '<div ' . $this->get_render_attribute_string( 'root' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $src ) {
			echo '<video class="wpz-video-text__media" src="' . esc_url( $src ) . '"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . ' autoplay muted loop playsinline aria-hidden="true" tabindex="-1"></video>';
		}
		echo '<div class="wpz-video-text__plate"><' . $tag . ' class="wpz-video-text__text">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( $lines as $line ) {
			echo '<span class="wpz-video-text__line">' . esc_html( $line ) . '</span>';
		}
		echo '</' . $tag . '></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'link', $settings['link'] );
			$this->add_render_attribute( 'link', 'class', 'wpz-video-text__link' );
			$this->add_render_attribute( 'link', 'aria-label', implode( ' ', $lines ) );
			echo '<a ' . $this->get_render_attribute_string( 'link' ) . '></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}
}

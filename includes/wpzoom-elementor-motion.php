<?php
/**
 * Motion options in the "Advanced" tab of Elementor elements.
 *
 * Reveal on scroll (every widget, container, section and column): a short entrance played once when the
 * element scrolls into view (fade, slide up, text line by line, unmask), with one easing and one duration
 * for the whole site so pages move as a set.
 *
 * Roll text on hover (widgets): the label of a button or linked heading rolls up and is replaced by a copy
 * of itself.
 *
 * @package WPZOOM_Elementor_Addons
 */

namespace WPZOOMElementorWidgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class WPZOOM_Elementor_Motion {

	private static $instance = null;

	/** Whether the head switch was queued for this request. */
	private $in_head = false;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		foreach ( array( 'common', 'container', 'section', 'column' ) as $stack ) {
			add_action( "elementor/element/{$stack}/section_effects/after_section_end", array( $this, 'register_controls' ), 10, 1 );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ), 20 );
		add_action( 'elementor/frontend/before_render', array( $this, 'before_render' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue' ) );
	}

	/**
	 * The "Reveal on Scroll" section, right after Elementor's Motion Effects.
	 *
	 * @param \Elementor\Element_Base $element The element being registered.
	 */
	public function register_controls( $element ) {
		$is_widget = 'widget' === $element->get_type() || 'common' === $element->get_name();

		$effects = array(
			''      => esc_html__( 'None', 'wpzoom-elementor-addons' ),
			'fade'  => esc_html__( 'Fade', 'wpzoom-elementor-addons' ),
			'up'    => esc_html__( 'Slide up', 'wpzoom-elementor-addons' ),
			'lines' => esc_html__( 'Text, line by line', 'wpzoom-elementor-addons' ),
			'mask'  => esc_html__( 'Unmask (images)', 'wpzoom-elementor-addons' ),
		);
		if ( ! $is_widget ) {
			unset( $effects['lines'] );
		}

		$element->start_controls_section(
			'wpz_reveal_section',
			array(
				'label' => esc_html__( 'WPZOOM Motion', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'wpz_reveal',
			array(
				'label'        => esc_html__( 'Reveal on scroll', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => $effects,
				'prefix_class' => 'wpz-reveal-',
				'description'  => esc_html__( 'Plays once, when the element scrolls into view. Skipped for visitors who ask for reduced motion.', 'wpzoom-elementor-addons' ),
			)
		);

		$element->add_control(
			'wpz_reveal_delay',
			array(
				'label'     => esc_html__( 'Delay (ms)', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 3000,
				'step'      => 50,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}}' => '--wpz-reveal-delay: {{VALUE}}ms;',
				),
				'condition' => array( 'wpz_reveal!' => '' ),
			)
		);

		if ( $is_widget ) {
			$element->add_control(
				'wpz_hover_roll',
				array(
					'label'        => esc_html__( 'Roll text on hover', 'wpzoom-elementor-addons' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'return_value' => 'yes',
					'prefix_class' => 'wpz-roll-',
					'separator'    => 'before',
					'description'  => esc_html__( 'For buttons and linked headings: the label rolls up and a copy rolls in.', 'wpzoom-elementor-addons' ),
				)
			);
		}

		$element->end_controls_section();
	}

	public function register_assets() {
		wp_register_style( 'wpzoom-elementor-motion', WPZOOM_EL_ADDONS_URL . 'assets/css/motion.css', array(), WPZOOM_EL_ADDONS_VER );
		wp_register_script( 'wpzoom-elementor-motion', WPZOOM_EL_ADDONS_URL . 'assets/js/motion.js', array(), WPZOOM_EL_ADDONS_VER, true );
	}

	/**
	 * Load in the head when the page being viewed uses a reveal, so nothing flashes before it is hidden.
	 */
	public function maybe_enqueue() {
		if ( ! is_singular() ) {
			return;
		}
		$data = get_post_meta( get_queried_object_id(), '_elementor_data', true );
		if ( is_string( $data ) && preg_match( '/"wpz_(reveal|hover_roll)":"[a-z]+"/', $data ) ) {
			$this->enqueue( true );
		}
	}

	/**
	 * @param bool $in_head True when called while the head is being built: the elements can then be hidden before the first paint.
	 */
	public function enqueue( $in_head = false ) {
		wp_enqueue_style( 'wpzoom-elementor-motion' );
		wp_enqueue_script( 'wpzoom-elementor-motion' );

		if ( true === $in_head && ! $this->in_head ) {
			$this->in_head = true;
			// wp_enqueue_scripts runs at wp_head priority 1, so this prints right after it. The "hidden until
			// revealed" rules only apply once the class is set: the page stays readable without JavaScript and
			// for reduced-motion visitors.
			add_action( 'wp_head', array( $this, 'print_switch' ), 2 );
		}
	}

	public function print_switch() {
		// If motion.js never loads, the class comes off after four seconds so nothing stays hidden.
		echo "<script>(function(d){if(window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches){return;}d.classList.add('wpz-motion');setTimeout(function(){if(!window.wpzMotion){d.classList.remove('wpz-motion');}},4000);})(document.documentElement);</script>\n";
	}

	/**
	 * Templates rendered outside the main post (header, footer, popups) get the assets late: the script still
	 * runs, the elements just are not pre-hidden.
	 *
	 * @param \Elementor\Element_Base $element The element about to render.
	 */
	public function before_render( $element ) {
		if ( wp_script_is( 'wpzoom-elementor-motion', 'enqueued' ) ) {
			return;
		}
		if ( $element->get_settings( 'wpz_reveal' ) || $element->get_settings( 'wpz_hover_roll' ) ) {
			$this->enqueue();
		}
	}
}

WPZOOM_Elementor_Motion::instance();

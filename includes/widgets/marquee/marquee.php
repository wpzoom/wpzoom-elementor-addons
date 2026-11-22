<?php
/**
 * Marquee widget: a row of words or logos that scrolls sideways in a loop.
 *
 * CSS only. The items are printed twice inside a track that slides by the width of one copy,
 * so the loop has no seam; a short list still fills the row because each copy is at least as
 * wide as the widget.
 *
 * @package WPZOOM_Elementor_Addons
 */

namespace WPZOOMElementorWidgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Marquee.
 */
class Marquee extends Widget_Base {

	/**
	 * Constructor.
	 *
	 * @param array      $data Widget data.
	 * @param array|null $args Optional arguments.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-marquee', plugins_url( 'frontend.css', __FILE__ ), array(), WPZOOM_EL_ADDONS_VER );
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wpzoom-elementor-addons-marquee';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Marquee', 'wpzoom-elementor-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-animation-text';
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
		return array( 'marquee', 'ticker', 'scrolling', 'logos', 'clients', 'text', 'loop' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'wpzoom-elementor-addons-css-frontend-marquee' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_items',
			array(
				'label' => esc_html__( 'Items', 'wpzoom-elementor-addons' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'text',
			array(
				'label'       => esc_html__( 'Text', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label'       => esc_html__( 'Logo or image', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'Shown instead of the text; the text becomes its alternative text.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'default'     => array( 'url' => '' ),
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => '' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array( 'text' => esc_html__( 'Brand identity', 'wpzoom-elementor-addons' ) ),
					array( 'text' => esc_html__( 'Websites', 'wpzoom-elementor-addons' ) ),
					array( 'text' => esc_html__( 'Packaging', 'wpzoom-elementor-addons' ) ),
					array( 'text' => esc_html__( 'Campaigns', 'wpzoom-elementor-addons' ) ),
				),
			)
		);

		$this->add_control(
			'separator',
			array(
				'label'       => esc_html__( 'Separator', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'A character printed between the items, for example / or •. Leave empty for none.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'separator'   => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_motion',
			array(
				'label' => esc_html__( 'Motion', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'duration',
			array(
				'label'       => esc_html__( 'Loop duration (seconds)', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'Time for the whole list to pass once. Higher is slower.', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 3,
				'max'         => 300,
				'step'        => 1,
				'default'     => 40,
				'selectors'   => array(
					'{{WRAPPER}} .wpz-marquee' => '--wpz-marquee-time: {{VALUE}}s;',
				),
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'        => esc_html__( 'Direction', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'left',
				'options'      => array(
					'left'  => esc_html__( 'Right to left', 'wpzoom-elementor-addons' ),
					'right' => esc_html__( 'Left to right', 'wpzoom-elementor-addons' ),
				),
				'prefix_class' => 'wpz-marquee-dir-',
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'        => esc_html__( 'Pause on hover', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'prefix_class' => 'wpz-marquee-pause-',
			)
		);

		$this->add_control(
			'fade_edges',
			array(
				'label'        => esc_html__( 'Fade the edges', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'prefix_class' => 'wpz-marquee-fade-',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Marquee', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => esc_html__( 'Space between items', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'vw' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 56 ),
				'selectors'  => array(
					'{{WRAPPER}} .wpz-marquee' => '--wpz-marquee-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .wpz-marquee__item',
			)
		);

		$this->add_control(
			'color',
			array(
				'label'     => esc_html__( 'Text color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpz-marquee__item, {{WRAPPER}} .wpz-marquee__item a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'hover_color',
			array(
				'label'     => esc_html__( 'Link hover color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpz-marquee__item a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'separator_color',
			array(
				'label'     => esc_html__( 'Separator color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpz-marquee__sep' => 'color: {{VALUE}};',
				),
				'condition' => array( 'separator!' => '' ),
			)
		);

		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => esc_html__( 'Image height', 'wpzoom-elementor-addons' ),
				'description' => esc_html__( 'Small for logos, large (for example 60vh) for a strip of photos.', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'vh', 'vw', 'custom' ),
				'range'      => array( 'px' => array( 'min' => 10, 'max' => 800 ), 'vh' => array( 'min' => 5, 'max' => 100 ), 'vw' => array( 'min' => 2, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 36 ),
				'selectors'  => array(
					'{{WRAPPER}} .wpz-marquee__item img' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * One copy of the list.
	 *
	 * @param array  $items     Repeater rows.
	 * @param string $separator Separator text.
	 * @param bool   $hidden    Whether this copy is the decorative duplicate.
	 */
	private function render_group( $items, $separator, $hidden ) {
		echo '<div class="wpz-marquee__group"' . ( $hidden ? ' aria-hidden="true"' : '' ) . '>';

		foreach ( $items as $item ) {
			$text = isset( $item['text'] ) ? $item['text'] : '';
			$img  = ! empty( $item['image']['url'] ) ? $item['image']['url'] : '';
			if ( '' === $text && '' === $img ) {
				continue;
			}

			// Width and height from the Media Library keep the row from jumping while large photos load.
			$size = '';
			if ( $img && ! empty( $item['image']['id'] ) ) {
				$meta = wp_get_attachment_metadata( (int) $item['image']['id'] );
				if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
					$size = ' width="' . (int) $meta['width'] . '" height="' . (int) $meta['height'] . '"';
				}
			}

			$inner = $img
				? '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $text ) . '"' . $size . ' loading="lazy" decoding="async">'
				: esc_html( $text );

			if ( ! empty( $item['link']['url'] ) ) {
				$attrs = ' href="' . esc_url( $item['link']['url'] ) . '"';
				if ( ! empty( $item['link']['is_external'] ) ) {
					$attrs .= ' target="_blank" rel="noopener' . ( ! empty( $item['link']['nofollow'] ) ? ' nofollow' : '' ) . '"';
				} elseif ( ! empty( $item['link']['nofollow'] ) ) {
					$attrs .= ' rel="nofollow"';
				}
				if ( $hidden ) {
					$attrs .= ' tabindex="-1"';
				}
				$inner = '<a' . $attrs . '>' . $inner . '</a>';
			}

			echo '<span class="wpz-marquee__item">' . $inner . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above.

			if ( '' !== $separator ) {
				echo '<span class="wpz-marquee__sep" aria-hidden="true">' . esc_html( $separator ) . '</span>';
			}
		}

		echo '</div>';
	}

	/**
	 * Render.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = isset( $settings['items'] ) && is_array( $settings['items'] ) ? $settings['items'] : array();
		if ( ! $items ) {
			return;
		}
		$separator = isset( $settings['separator'] ) ? trim( (string) $settings['separator'] ) : '';

		echo '<div class="wpz-marquee"><div class="wpz-marquee__track">';
		$this->render_group( $items, $separator, false );
		$this->render_group( $items, $separator, true );
		echo '</div></div>';
	}
}

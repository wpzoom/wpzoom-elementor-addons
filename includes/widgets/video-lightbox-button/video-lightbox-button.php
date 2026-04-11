<?php
/**
 * Video Lightbox Button widget.
 *
 * @package WPZOOM_Elementor_Addons
 */

namespace WPZOOMElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Icons_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Button that opens a video (YouTube, Vimeo, or direct file) in a modal.
 */
class Video_Lightbox_Button extends Widget_Base {

	/**
	 * Constructor.
	 *
	 * @param array      $data Widget data.
	 * @param array|null $args Optional arguments.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style(
			'wpzoom-elementor-addons-css-frontend-video-lightbox-button',
			plugins_url( 'frontend.css', __FILE__ ),
			array(),
			WPZOOM_EL_ADDONS_VER
		);
		wp_register_script(
			'wpzoom-elementor-addons-js-frontend-video-lightbox-button',
			plugins_url( 'frontend.js', __FILE__ ),
			array( 'jquery' ),
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
		return 'wpzoom-elementor-addons-video-lightbox-button';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Video Lightbox Button', 'wpzoom-elementor-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-play';
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
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'wpzoom-elementor-addons-css-frontend-video-lightbox-button' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'wpzoom-elementor-addons-js-frontend-video-lightbox-button' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
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
			'section_content',
			array(
				'label' => esc_html__( 'Content', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'video_url',
			array(
				'label'       => esc_html__( 'Video URL', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'options'     => false,
				'placeholder' => esc_html__( 'YouTube, Vimeo, or direct video file URL', 'wpzoom-elementor-addons' ),
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => array(
					'url' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => esc_html__( 'Button Text', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Watch video', 'wpzoom-elementor-addons' ),
				'label_block' => true,
				'dynamic'     => array(
					'active' => true,
				),
			)
		);

		$this->add_control(
			'show_icon',
			array(
				'label'        => esc_html__( 'Show Icon', 'wpzoom-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'wpzoom-elementor-addons' ),
				'label_off'    => esc_html__( 'No', 'wpzoom-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'selected_icon',
			array(
				'label'            => esc_html__( 'Icon', 'wpzoom-elementor-addons' ),
				'type'             => Controls_Manager::ICONS,
				'skin'             => 'inline',
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => 'fas fa-play',
					'library' => 'fa-solid',
				),
				'condition'        => array(
					'show_icon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'wpzoom-elementor-addons' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'wpzoom-elementor-addons' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'wpzoom-elementor-addons' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'left',
				'toggle'    => true,
				'selectors' => array(
					'{{WRAPPER}} .wpz-vlb' => 'display: flex; justify-content: {{VALUE}};',
				),
				'selectors_dictionary' => array(
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				),
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
			'section_style_trigger',
			array(
				'label' => esc_html__( 'Button', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Typography', 'wpzoom-elementor-addons' ),
				'selector' => '{{WRAPPER}} .wpz-vlb-trigger__text',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				),
			)
		);

		$this->start_controls_tabs( 'tabs_trigger' );

		$this->start_controls_tab(
			'tab_trigger_normal',
			array(
				'label' => esc_html__( 'Normal', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'trigger_color',
			array(
				'label'     => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpz-vlb-trigger' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'trigger_bg',
			array(
				'label'     => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpz-vlb-trigger' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_trigger_hover',
			array(
				'label' => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'trigger_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpz-vlb-trigger:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'trigger_hover_bg',
			array(
				'label'     => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpz-vlb-trigger:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'trigger_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => 10,
					'right'    => 0,
					'bottom'   => 10,
					'left'     => 0,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpz-vlb-trigger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'trigger_border',
				'selector' => '{{WRAPPER}} .wpz-vlb-trigger',
			)
		);

		$this->add_responsive_control(
			'trigger_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpz-vlb-trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'trigger_shadow',
				'selector' => '{{WRAPPER}} .wpz-vlb-trigger',
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 80,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 18,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpz-vlb-trigger__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpz-vlb-trigger__icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_icon' => 'yes',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'icon_gap',
			array(
				'label'      => esc_html__( 'Icon Spacing', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpz-vlb-trigger' => 'gap: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_icon' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$url = isset( $settings['video_url']['url'] ) ? $settings['video_url']['url'] : '';
		$url = trim( (string) $url );

		$this->add_render_attribute(
			'wrapper',
			array(
				'class' => 'wpz-vlb',
			)
		);

		$href = $url !== '' ? $url : '#';
		$this->add_render_attribute(
			'trigger',
			array(
				'class' => 'wpz-vlb-trigger',
				'href'  => esc_url( $href ),
				'role'  => 'button',
			)
		);

		if ( $url === '' ) {
			$this->add_render_attribute( 'trigger', 'aria-disabled', 'true' );
		} else {
			$this->add_render_attribute(
				'trigger',
				array(
					'aria-haspopup' => 'dialog',
					'aria-label'    => esc_attr( $settings['button_text'] ),
				)
			);
		}

		echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>';
		echo '<a ' . $this->get_render_attribute_string( 'trigger' ) . '>';

		if ( ! empty( $settings['show_icon'] ) && 'yes' === $settings['show_icon'] ) {
			$icon_setting = isset( $settings['selected_icon'] ) ? $settings['selected_icon'] : array();
			if ( ! empty( $icon_setting['value'] ) || ! empty( $settings['icon'] ) ) {
				echo '<span class="wpz-vlb-trigger__icon" aria-hidden="true">';
				Icons_Manager::render_icon(
					$icon_setting,
					array( 'aria-hidden' => 'true' ),
					'i'
				);
				echo '</span>';
			}
		}

		echo '<span class="wpz-vlb-trigger__text">' . esc_html( $settings['button_text'] ) . '</span>';
		echo '</a>';
		echo '</div>';
	}
}

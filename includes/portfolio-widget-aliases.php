<?php
/**
 * Theme-specific portfolio widgets standing in for each other.
 *
 * Inspiro pages use "Portfolio Showcase", Reel pages use "Portfolio Widget". Both store
 * (almost) the same settings, so when a site switches theme the page keeps working: on
 * Reel the showcase widget type is rendered by the Reel widget, on Inspiro the Reel
 * widget type by the showcase. The few settings that differ are mapped once, when the
 * widget is instantiated; everything the user then edits is saved under the native keys.
 *
 * @package WPZOOM_Elementor_Addons
 */

namespace WPZOOMElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings shared by both portfolio widgets, mapped from one to the other.
 *
 * @param array  $settings Raw widget settings.
 * @param string $to       'reel' or 'showcase': the widget that will render them.
 * @return array
 */
function map_portfolio_widget_settings( $settings, $to ) {
	if ( 'reel' === $to ) {
		if ( ! isset( $settings['grid_style'] ) && isset( $settings['layout_type'] ) ) {
			$settings['grid_style'] = 'narrow' === $settings['layout_type'] ? 'title_below' : 'title_overlay';
		}
		if ( ! isset( $settings['lightbox_open_thumb'] ) && isset( $settings['item_click'] ) ) {
			$settings['lightbox_open_thumb'] = 'lightbox' === $settings['item_click'] ? 'yes' : '';
		}
	} else {
		if ( ! isset( $settings['layout_type'] ) && isset( $settings['grid_style'] ) ) {
			$settings['layout_type'] = 'title_below' === $settings['grid_style'] ? 'narrow' : 'full-width';
		}
		if ( ! isset( $settings['item_click'] ) && ! empty( $settings['lightbox_open_thumb'] ) ) {
			$settings['item_click'] = 'lightbox';
		}
	}
	return $settings;
}

if ( class_exists( '\WPZOOMElementorWidgets\Portfolio_Reel' ) ) {
	/**
	 * On Reel: renders the "wpzoom-elementor-addons-portfolio-showcase" widget type.
	 */
	class Portfolio_Reel_For_Showcase extends Portfolio_Reel {
		public function __construct( $data = array(), $args = null ) {
			if ( ! empty( $data['settings'] ) && is_array( $data['settings'] ) ) {
				$data['settings'] = map_portfolio_widget_settings( $data['settings'], 'reel' );
			}
			parent::__construct( $data, $args );
		}
		public function get_name() {
			return 'wpzoom-elementor-addons-portfolio-showcase';
		}
		/**
		 * Elementor stores no value for a control left at its default, so "unset" must
		 * mean the same thing it meant in the showcase widget: Title Overlay.
		 */
		protected function register_controls() {
			parent::register_controls();
			$this->update_control( 'grid_style', array( 'default' => 'title_overlay' ) );
		}
		public function get_title() {
			return esc_html__( 'Portfolio Showcase', 'wpzoom-elementor-addons' );
		}
		public function show_in_panel() {
			return false;
		}
	}
}

if ( class_exists( '\WPZOOMElementorWidgets\Portfolio_Showcase' ) ) {
	/**
	 * On Inspiro / Inspiro PRO: renders the "wpzoom-elementor-addons-portfolio-reel" widget type.
	 */
	class Portfolio_Showcase_For_Reel extends Portfolio_Showcase {
		public function __construct( $data = array(), $args = null ) {
			if ( ! empty( $data['settings'] ) && is_array( $data['settings'] ) ) {
				$data['settings'] = map_portfolio_widget_settings( $data['settings'], 'showcase' );
			}
			parent::__construct( $data, $args );
		}
		public function get_name() {
			return 'wpzoom-elementor-addons-portfolio-reel';
		}
		/** Unset layout in Reel data means its default, Title Below. */
		protected function register_controls() {
			parent::register_controls();
			$this->update_control( 'layout_type', array( 'default' => 'narrow' ) );
		}
		public function get_title() {
			return esc_html__( 'Portfolio Widget', 'wpzoom-elementor-addons' );
		}
		public function show_in_panel() {
			return false;
		}
	}
}

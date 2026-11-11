<?php
/**
 * World Map widget: a clickable vector map of the world.
 *
 * Countries (or whole continents) are coloured and linked to pages or
 * categories, with their name in a tooltip, so a travel site gets a map of its
 * destinations. The list of highlighted places can come from a repeater or
 * straight from a taxonomy, whose terms are matched by name ("Morocco",
 * "Europe", "Southeast Asia"). Optional pins mark cities. Drawn with
 * jsvectormap (MIT), no external service, no API key.
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
 * World Map.
 */
class World_Map extends Widget_Base {

	/**
	 * Version of the bundled jsvectormap files (lib/).
	 */
	const LIB_VERSION = '1.7.0';

	/**
	 * Projection setting => jsvectormap map name.
	 *
	 * @var string[]
	 */
	const MAPS = array(
		'merc'   => 'world_merc',
		'miller' => 'world',
	);

	/**
	 * Muted palette used when a taxonomy colours each term differently.
	 *
	 * @var string[]
	 */
	const PALETTE = array( '#2f6f8f', '#c46a3c', '#7a9a5b', '#8a6fb0', '#c9a43c', '#b5505a', '#4f8f8b', '#6d6d9a' );

	/**
	 * Countries, continents and aliases (countries.php).
	 *
	 * @var array|null
	 */
	private static $data = null;

	/**
	 * Normalised name => code lookup, built once.
	 *
	 * @var array|null
	 */
	private static $lookup = null;

	/**
	 * Constructor.
	 *
	 * @param array      $data Widget data.
	 * @param array|null $args Optional arguments.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		$lib = plugins_url( 'lib', __FILE__ ); // not "vendor": .distignore drops any folder of that name from the WordPress.org build

		if ( ! wp_script_is( 'jsvectormap', 'registered' ) ) {
			wp_register_script( 'jsvectormap', $lib . '/jsvectormap.min.js', array(), self::LIB_VERSION, true );
		}
		if ( ! wp_script_is( 'jsvectormap-world-merc', 'registered' ) ) {
			wp_register_script( 'jsvectormap-world-merc', $lib . '/world-merc.js', array( 'jsvectormap' ), self::LIB_VERSION, true );
		}
		if ( ! wp_script_is( 'jsvectormap-world', 'registered' ) ) {
			wp_register_script( 'jsvectormap-world', $lib . '/world.js', array( 'jsvectormap' ), self::LIB_VERSION, true );
		}
		if ( ! wp_style_is( 'jsvectormap', 'registered' ) ) {
			wp_register_style( 'jsvectormap', $lib . '/jsvectormap.min.css', array(), self::LIB_VERSION );
		}

		wp_register_style( 'wpzoom-elementor-addons-css-frontend-world-map', plugins_url( 'frontend.css', __FILE__ ), array( 'jsvectormap' ), WPZOOM_EL_ADDONS_VER );
		wp_register_script( 'wpzoom-elementor-addons-js-frontend-world-map', plugins_url( 'frontend.js', __FILE__ ), array( 'jsvectormap' ), WPZOOM_EL_ADDONS_VER, true );
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wpzoom-elementor-addons-world-map';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'World Map', 'wpzoom-elementor-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-globe';
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
		return array( 'map', 'world', 'countries', 'travel', 'destinations', 'vector', 'svg', 'globe' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'jsvectormap', 'wpzoom-elementor-addons-css-frontend-world-map' );
	}

	/**
	 * Script dependencies: the library, the map data for the chosen projection
	 * (both when the settings are not known, as in the editor) and the widget.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		$maps       = array( 'jsvectormap-world-merc', 'jsvectormap-world' );
		$projection = '';

		// The editor asks the bare widget type (no id, no settings); reading
		// settings there throws, so that case gets both maps.
		try {
			if ( $this->get_id() ) {
				$projection = (string) $this->get_settings_for_display( 'projection' );
			}
		} catch ( \Throwable $e ) {
			$projection = '';
		}

		if ( isset( self::MAPS[ $projection ] ) ) {
			$maps = array( 'miller' === $projection ? 'jsvectormap-world' : 'jsvectormap-world-merc' );
		}

		return array_merge( array( 'jsvectormap' ), $maps, array( 'wpzoom-elementor-addons-js-frontend-world-map' ) );
	}

	/**
	 * Countries, continents and aliases.
	 *
	 * @return array
	 */
	private static function data() {
		if ( null === self::$data ) {
			self::$data = include __DIR__ . '/countries.php';
		}
		return self::$data;
	}

	/**
	 * Options for the region select: continents first, then countries A–Z.
	 *
	 * @return array
	 */
	private static function region_groups() {
		$data       = self::data();
		$continents = array();
		$countries  = array();

		foreach ( $data['continents'] as $code => $name ) {
			$continents[ 'continent:' . $code ] = $name;
		}
		foreach ( $data['countries'] as $code => $row ) {
			$countries[ $code ] = $row[0];
		}

		return array(
			array(
				'label'   => esc_html__( 'Continents', 'wpzoom-elementor-addons' ),
				'options' => $continents,
			),
			array(
				'label'   => esc_html__( 'Countries', 'wpzoom-elementor-addons' ),
				'options' => $countries,
			),
		);
	}

	/**
	 * Public taxonomies for the source select.
	 *
	 * @return array
	 */
	private static function taxonomy_options() {
		$options = array();
		foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $tax ) {
			if ( 'post_format' === $tax->name ) {
				continue;
			}
			$options[ $tax->name ] = $tax->labels->singular_name . ' (' . $tax->name . ')';
		}
		return $options;
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$data = self::data();

		/* ---------- Content: destinations ---------- */
		$this->start_controls_section( 'section_map', array( 'label' => esc_html__( 'Destinations', 'wpzoom-elementor-addons' ) ) );

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Source', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => array(
					'manual'   => esc_html__( 'Pick countries', 'wpzoom-elementor-addons' ),
					'taxonomy' => esc_html__( 'Categories (matched by name)', 'wpzoom-elementor-addons' ),
				),
			)
		);

		$this->add_control(
			'taxonomy',
			array(
				'label'       => esc_html__( 'Taxonomy', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'category',
				'options'     => self::taxonomy_options(),
				'description' => esc_html__( 'Every term with posts is matched to a country, a continent or a region by its name — "Morocco", "Europe", "Southeast Asia", "Scandinavia" — and the map links there.', 'wpzoom-elementor-addons' ),
				'condition'   => array( 'source' => 'taxonomy' ),
			)
		);

		$this->add_control(
			'taxonomy_colors',
			array(
				'label'     => esc_html__( 'Colours', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'single',
				'options'   => array(
					'single'  => esc_html__( 'One highlight colour', 'wpzoom-elementor-addons' ),
					'palette' => esc_html__( 'A different colour per term', 'wpzoom-elementor-addons' ),
				),
				'condition' => array( 'source' => 'taxonomy' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'region',
			array(
				'label'   => esc_html__( 'Country or continent', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'groups'  => self::region_groups(),
				'default' => 'MA',
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'A category, a page, a post…', 'wpzoom-elementor-addons' ),
				'options'     => array( 'url', 'is_external', 'nofollow' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'color',
			array(
				'label'       => esc_html__( 'Colour', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'description' => esc_html__( 'Leave empty for the highlight colour.', 'wpzoom-elementor-addons' ),
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'       => esc_html__( 'Tooltip', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'The country name', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'regions',
			array(
				'label'       => esc_html__( 'Countries', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '<# print( label || region ) #>',
				'default'     => array(
					array( 'region' => 'MA' ),
					array( 'region' => 'IT' ),
					array( 'region' => 'JP' ),
					array( 'region' => 'PE' ),
					array( 'region' => 'US' ),
					array( 'region' => 'AU' ),
				),
				'condition'   => array( 'source' => 'manual' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Content: pins ---------- */
		$this->start_controls_section( 'section_pins', array( 'label' => esc_html__( 'Pins', 'wpzoom-elementor-addons' ) ) );

		$pin = new Repeater();

		$pin->add_control(
			'name',
			array(
				'label'   => esc_html__( 'Name', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Lisbon', 'wpzoom-elementor-addons' ),
			)
		);

		$pin->add_control(
			'coords',
			array(
				'label'       => esc_html__( 'Latitude, longitude', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '38.72, -9.14',
				'description' => esc_html__( 'Right-click a place in Google Maps to copy its coordinates.', 'wpzoom-elementor-addons' ),
			)
		);

		$pin->add_control(
			'link',
			array(
				'label'       => esc_html__( 'Link', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::URL,
				'options'     => array( 'url', 'is_external', 'nofollow' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'pins',
			array(
				'label'       => esc_html__( 'Pins', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $pin->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(),
			)
		);

		$this->end_controls_section();

		/* ---------- Content: options ---------- */
		$this->start_controls_section( 'section_options', array( 'label' => esc_html__( 'Map', 'wpzoom-elementor-addons' ) ) );

		$this->add_control(
			'projection',
			array(
				'label'   => esc_html__( 'Projection', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'merc',
				'options' => array(
					'merc'   => esc_html__( 'Mercator (taller)', 'wpzoom-elementor-addons' ),
					'miller' => esc_html__( 'Miller (wider)', 'wpzoom-elementor-addons' ),
				),
			)
		);

		$focus = array( '' => esc_html__( 'The whole world', 'wpzoom-elementor-addons' ) );
		foreach ( $data['continents'] as $code => $name ) {
			$focus[ $code ] = $name;
		}

		$this->add_control(
			'focus',
			array(
				'label'   => esc_html__( 'Show', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $focus,
			)
		);

		$this->add_control(
			'tooltip',
			array(
				'label'   => esc_html__( 'Tooltip with the name', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'zoom',
			array(
				'label'       => esc_html__( 'Zoom and pan', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => esc_html__( 'Zoom buttons and dragging. The mouse wheel keeps scrolling the page.', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'new_tab',
			array(
				'label'   => esc_html__( 'Open links in a new tab', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'legend',
			array(
				'label'       => esc_html__( 'List the destinations under the map', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => esc_html__( 'The list is always in the page for screen readers and search engines; this makes it visible.', 'wpzoom-elementor-addons' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: map ---------- */
		$this->start_controls_section(
			'section_style_map',
			array(
				'label' => esc_html__( 'Map', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'land_color',
			array(
				'label'   => esc_html__( 'Land', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#e6e6e6',
			)
		);

		$this->add_control(
			'border_color',
			array(
				'label'   => esc_html__( 'Borders', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#ffffff',
			)
		);

		$this->add_control(
			'border_width',
			array(
				'label'   => esc_html__( 'Border width', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'default' => array(
					'size' => 0.6,
					'unit' => 'px',
				),
			)
		);

		$this->add_control(
			'highlight_color',
			array(
				'label'   => esc_html__( 'Highlighted countries', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#2f6f8f',
			)
		);

		$this->add_control(
			'hover_color',
			array(
				'label'       => esc_html__( 'Hover', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'description' => esc_html__( 'Leave empty to lighten the country\'s own colour.', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'bg_color',
			array(
				'label'   => esc_html__( 'Sea', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => esc_html__( 'Max width', 'wpzoom-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 300,
						'max' => 1600,
					),
					'%'  => array(
						'min' => 20,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 100,
					'unit' => '%',
				),
				'selectors'  => array( '{{WRAPPER}} .wpz-world-map' => '--wpz-map-max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'center',
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
				'condition' => array( 'max_width[size]!' => '' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: tooltip ---------- */
		$this->start_controls_section(
			'section_style_tooltip',
			array(
				'label'     => esc_html__( 'Tooltip', 'wpzoom-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'tooltip' => 'yes' ),
			)
		);

		$this->add_control(
			'tip_bg',
			array(
				'label'     => esc_html__( 'Background', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-world-map' => '--wpz-map-tip-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'tip_color',
			array(
				'label'     => esc_html__( 'Text', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-world-map' => '--wpz-map-tip-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'tip_typography',
				'selector' => '{{WRAPPER}} .wpz-world-map__tip-style',
			)
		);

		$this->add_control(
			'tip_radius',
			array(
				'label'     => esc_html__( 'Corner radius', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'selectors' => array( '{{WRAPPER}} .wpz-world-map' => '--wpz-map-tip-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'tip_padding',
			array(
				'label'     => esc_html__( 'Padding', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .wpz-world-map' => '--wpz-map-tip-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: pins ---------- */
		$this->start_controls_section(
			'section_style_pins',
			array(
				'label' => esc_html__( 'Pins', 'wpzoom-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'pin_color',
			array(
				'label'       => esc_html__( 'Colour', 'wpzoom-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'description' => esc_html__( 'Leave empty for the highlight colour.', 'wpzoom-elementor-addons' ),
			)
		);

		$this->add_control(
			'pin_border',
			array(
				'label'   => esc_html__( 'Ring', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#ffffff',
			)
		);

		$this->add_control(
			'pin_size',
			array(
				'label'   => esc_html__( 'Size', 'wpzoom-elementor-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => 3,
						'max' => 14,
					),
				),
				'default' => array(
					'size' => 6,
					'unit' => 'px',
				),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: list ---------- */
		$this->start_controls_section(
			'section_style_list',
			array(
				'label'     => esc_html__( 'List', 'wpzoom-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'legend' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'list_typography',
				'selector' => '{{WRAPPER}} .wpz-world-map__list',
			)
		);

		$this->add_control(
			'list_color',
			array(
				'label'     => esc_html__( 'Text', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .wpz-world-map__list' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'list_gap',
			array(
				'label'     => esc_html__( 'Space above', 'wpzoom-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array( '{{WRAPPER}} .wpz-world-map__list' => 'margin-top: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Lower-case ASCII words only, so "Côte d'Ivoire", "Bosnia & Herz." and
	 * "bosnia and herz" compare equal.
	 *
	 * @param string $name Term or country name.
	 * @return string
	 */
	private static function normalize( $name ) {
		$name = strtolower( remove_accents( (string) $name ) );
		$name = str_replace( '&', ' and ', $name );
		$name = preg_replace( '/[^a-z0-9]+/', ' ', $name );
		return trim( preg_replace( '/\s+/', ' ', $name ) );
	}

	/**
	 * Normalised name => code | 'continent:XX' | 'XX YY continent:ZZ'.
	 *
	 * @return array
	 */
	private static function lookup() {
		if ( null !== self::$lookup ) {
			return self::$lookup;
		}
		$data   = self::data();
		$lookup = array();
		foreach ( $data['countries'] as $code => $row ) {
			$lookup[ self::normalize( $row[0] ) ] = $code;
		}
		foreach ( $data['continents'] as $code => $name ) {
			$lookup[ self::normalize( $name ) ] = 'continent:' . $code;
		}
		foreach ( $data['aliases'] as $alias => $target ) {
			$lookup[ $alias ] = $target;
		}
		self::$lookup = $lookup;
		return $lookup;
	}

	/**
	 * Country codes for a region value: a country code, 'continent:XX', or a
	 * space-separated list of those.
	 *
	 * @param string $value Region value.
	 * @return string[]
	 */
	private static function expand( $value ) {
		$data  = self::data();
		$codes = array();
		foreach ( preg_split( '/\s+/', trim( (string) $value ) ) as $part ) {
			if ( 0 === strpos( $part, 'continent:' ) ) {
				$continent = substr( $part, 10 );
				foreach ( $data['countries'] as $code => $row ) {
					if ( $row[1] === $continent ) {
						$codes[] = $code;
					}
				}
			} elseif ( isset( $data['countries'][ $part ] ) ) {
				$codes[] = $part;
			}
		}
		return array_values( array_unique( $codes ) );
	}

	/**
	 * Name of a region value, for the list under the map.
	 *
	 * @param string $value Region value.
	 * @return string
	 */
	private static function region_name( $value ) {
		$data = self::data();
		if ( 0 === strpos( $value, 'continent:' ) && isset( $data['continents'][ substr( $value, 10 ) ] ) ) {
			return $data['continents'][ substr( $value, 10 ) ];
		}
		return isset( $data['countries'][ $value ] ) ? $data['countries'][ $value ][0] : '';
	}

	/**
	 * Matches a term name to a region value, or '' when nothing fits.
	 *
	 * @param string $name Term name.
	 * @return string
	 */
	private static function match( $name ) {
		$lookup = self::lookup();
		$key    = self::normalize( $name );
		if ( isset( $lookup[ $key ] ) ) {
			return $lookup[ $key ];
		}
		// "Travel to Morocco", "Morocco guides", "Europe (east)".
		foreach ( array( '/^(travel|trips?|guides?|visit|visiting|exploring|discover) (to |in )?/', '/ (travel|trips?|guides?|stories|posts|articles|adventures?|diaries)$/', '/ \(.*\)$/' ) as $pattern ) {
			$trimmed = trim( preg_replace( $pattern, '', $key ) );
			if ( $trimmed !== $key && isset( $lookup[ $trimmed ] ) ) {
				return $lookup[ $trimmed ];
			}
		}
		return '';
	}

	/**
	 * The link of a repeater row, safe to navigate to. The map script opens it
	 * with window.location, where a "javascript:" URL would run as code, so it
	 * is limited to the protocols WordPress allows (anything else gives '').
	 *
	 * @param array $row Repeater row.
	 * @return string
	 */
	private static function link_url( $row ) {
		if ( empty( $row['link']['url'] ) || ! is_string( $row['link']['url'] ) ) {
			return '';
		}
		return esc_url_raw( trim( $row['link']['url'] ) );
	}

	/**
	 * Highlighted regions from the repeater.
	 *
	 * @param array $settings Widget settings.
	 * @return array {regions: code => [...], list: [...]}
	 */
	private function from_repeater( $settings ) {
		$regions = array();
		$list    = array();
		$rows    = array();

		foreach ( (array) $settings['regions'] as $row ) {
			$value = isset( $row['region'] ) ? (string) $row['region'] : '';
			$codes = self::expand( $value );
			if ( ! $codes ) {
				continue;
			}
			$rows[] = array( $row, $value, $codes );

			$link   = self::link_url( $row );
			$list[] = array(
				'label'  => ! empty( $row['label'] ) ? $row['label'] : self::region_name( $value ),
				'link'   => $link,
				'target' => ( $link && ( ! empty( $row['link']['is_external'] ) || 'yes' === $settings['new_tab'] ) ) ? '_blank' : '',
				'rel'    => ! empty( $row['link']['nofollow'] ) ? 'nofollow' : '',
				'color'  => ! empty( $row['color'] ) ? $row['color'] : '',
			);
		}

		// A country row wins over a continent row that contains it, so continents
		// are painted first and single countries on top.
		$order = array();
		foreach ( $rows as $i => $item ) {
			$order[ $i ] = ( 1 === count( $item[2] ) ? 1 : 0 ) * 1000 + $i;
		}
		array_multisort( $order, SORT_ASC, $rows );

		foreach ( $rows as $item ) {
			list( $row, $value, $codes ) = $item;
			$link   = self::link_url( $row );
			$target = ( $link && ( ! empty( $row['link']['is_external'] ) || 'yes' === $settings['new_tab'] ) ) ? '_blank' : '';
			$color  = ! empty( $row['color'] ) ? $row['color'] : '';
			$label  = ! empty( $row['label'] ) ? $row['label'] : '';

			foreach ( $codes as $code ) {
				$entry = array();
				if ( $color ) {
					$entry['fill'] = $color;
				}
				if ( $link ) {
					$entry['link'] = $link;
				}
				if ( $target ) {
					$entry['target'] = $target;
				}
				if ( $label ) {
					$entry['label'] = $label;
				}
				$regions[ $code ] = (object) $entry;
			}
		}

		return compact( 'regions', 'list' );
	}

	/**
	 * Countries whose bounding box frames a continent. Russia would drag
	 * "Europe" out to the Pacific, Greenland pushes "North America" to the
	 * pole and the Aleutians (US) and Fiji cross the dateline, which makes a
	 * box as wide as the world; those are left out of the frame (they are
	 * still drawn, and the US sits between Canada and Mexico anyway).
	 *
	 * @param string $continent Continent code.
	 * @return string[]
	 */
	public static function focus_codes( $continent ) {
		$skip = array(
			'EU' => array( 'RU' ),
			'AS' => array( 'RU' ),
			'OC' => array( 'FJ' ),
		);
		$codes = self::expand( 'continent:' . $continent );
		if ( isset( $skip[ $continent ] ) ) {
			$codes = array_values( array_diff( $codes, $skip[ $continent ] ) );
		}
		return $codes;
	}

	/**
	 * What the map zooms to for a continent: the box around its countries, or,
	 * for North America, a point and a zoom factor, because Canada's Arctic
	 * islands reach so far north on a Mercator map that the box around them
	 * is nearly the whole world.
	 *
	 * @param string $continent Continent code.
	 * @return array|null
	 */
	public static function focus( $continent ) {
		if ( 'NA' === $continent ) {
			return array(
				'coords' => array( 42, -97 ),
				'scale'  => 2.4,
			);
		}
		$codes = self::focus_codes( $continent );
		return $codes ? array( 'regions' => $codes ) : null;
	}

	/**
	 * Highlighted regions from a taxonomy's terms, matched by name.
	 *
	 * @param array $settings Widget settings.
	 * @return array {regions: code => [...], list: [...]}
	 */
	private function from_taxonomy( $settings ) {
		$regions  = array();
		$list     = array();
		$taxonomy = ! empty( $settings['taxonomy'] ) && taxonomy_exists( $settings['taxonomy'] ) ? $settings['taxonomy'] : 'category';
		$terms    = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'orderby'    => 'name',
			)
		);
		if ( is_wp_error( $terms ) ) {
			return compact( 'regions', 'list' );
		}

		$palette = 'palette' === $settings['taxonomy_colors'];
		$target  = 'yes' === $settings['new_tab'] ? '_blank' : '';
		$i       = 0;

		// Country terms win over continent terms for the same country, so they
		// are applied last.
		$matched = array();
		foreach ( $terms as $term ) {
			// A parent term kept only by its children ("Europe" above "Italy") has
			// no posts of its own: skip it, or the whole continent lights up.
			if ( (int) $term->count < 1 ) {
				continue;
			}
			$value = self::match( $term->name );
			if ( '' === $value ) {
				continue;
			}
			$matched[] = array( $term, $value, false === strpos( $value, 'continent:' ) && 1 === count( self::expand( $value ) ) );
		}
		usort(
			$matched,
			function ( $a, $b ) {
				return (int) $a[2] - (int) $b[2];
			}
		);

		foreach ( $matched as $item ) {
			list( $term, $value ) = $item;
			$link  = get_term_link( $term );
			$link  = is_wp_error( $link ) ? '' : esc_url_raw( $link );
			$color = $palette ? self::PALETTE[ $i % count( self::PALETTE ) ] : '';
			$i++;

			foreach ( self::expand( $value ) as $code ) {
				$entry = array();
				if ( $color ) {
					$entry['fill'] = $color;
				}
				if ( $link ) {
					$entry['link'] = $link;
				}
				if ( $target ) {
					$entry['target'] = $target;
				}
				$regions[ $code ] = (object) $entry;
			}

			$list[] = array(
				'label'  => $term->name,
				'link'   => $link,
				'target' => $target,
				'rel'    => '',
				'color'  => $color,
			);
		}

		usort(
			$list,
			function ( $a, $b ) {
				return strcasecmp( $a['label'], $b['label'] );
			}
		);

		return compact( 'regions', 'list' );
	}

	/**
	 * Pins from the repeater.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function pins( $settings ) {
		$pins = array();
		foreach ( (array) $settings['pins'] as $row ) {
			if ( empty( $row['coords'] ) || ! preg_match( '/^\s*(-?\d+(?:\.\d+)?)\s*[,;\s]\s*(-?\d+(?:\.\d+)?)\s*$/', $row['coords'], $m ) ) {
				continue;
			}
			$lat = (float) $m[1];
			$lng = (float) $m[2];
			if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
				continue;
			}
			$link   = self::link_url( $row );
			$pins[] = array(
				'coords' => array( $lat, $lng ),
				'name'   => isset( $row['name'] ) ? (string) $row['name'] : '',
				'link'   => $link,
				'target' => ( $link && ( ! empty( $row['link']['is_external'] ) || 'yes' === $settings['new_tab'] ) ) ? '_blank' : '',
			);
		}
		return $pins;
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$found   = 'taxonomy' === $settings['source'] ? $this->from_taxonomy( $settings ) : $this->from_repeater( $settings );
		$pins    = $this->pins( $settings );
		$focus   = ! empty( $settings['focus'] ) ? self::focus( $settings['focus'] ) : null;
		$proj    = isset( self::MAPS[ $settings['projection'] ] ) ? $settings['projection'] : 'merc';
		$highlight = ! empty( $settings['highlight_color'] ) ? $settings['highlight_color'] : '#2f6f8f';

		$data = array(
			'map'     => self::MAPS[ $proj ],
			'regions' => $found['regions'] ? $found['regions'] : (object) array(),
			'pins'    => $pins,
			'focus'   => $focus,
			'tooltip' => 'yes' === $settings['tooltip'],
			'zoom'    => 'yes' === $settings['zoom'],
			'style'   => array(
				'land'        => ! empty( $settings['land_color'] ) ? $settings['land_color'] : '#e6e6e6',
				'border'      => ! empty( $settings['border_color'] ) ? $settings['border_color'] : 'none',
				'borderWidth' => isset( $settings['border_width']['size'] ) ? (float) $settings['border_width']['size'] : 0.6,
				'highlight'   => $highlight,
				'hover'       => ! empty( $settings['hover_color'] ) ? $settings['hover_color'] : '',
				'bg'          => ! empty( $settings['bg_color'] ) ? $settings['bg_color'] : 'transparent',
				'pin'         => ! empty( $settings['pin_color'] ) ? $settings['pin_color'] : $highlight,
				'pinBorder'   => ! empty( $settings['pin_border'] ) ? $settings['pin_border'] : '#ffffff',
				'pinSize'     => isset( $settings['pin_size']['size'] ) ? (float) $settings['pin_size']['size'] : 6,
			),
		);

		$align   = in_array( $settings['align'], array( 'left', 'center', 'right' ), true ) ? $settings['align'] : 'center';
		$classes = array( 'wpz-world-map', 'wpz-world-map--align-' . $align );

		$this->add_render_attribute( 'root', 'class', $classes );
		$this->add_render_attribute( 'canvas', 'class', 'wpz-world-map__canvas' );
		$this->add_render_attribute( 'canvas', 'data-map', wp_json_encode( $data ) );
		$this->add_render_attribute( 'canvas', 'aria-hidden', 'true' );
		$this->add_render_attribute( 'list', 'class', array( 'wpz-world-map__list', 'yes' === $settings['legend'] ? '' : 'wpz-world-map__list--sr' ) );
		$this->add_render_attribute( 'list', 'aria-label', esc_html__( 'Destinations', 'wpzoom-elementor-addons' ) );

		echo '<div ' . $this->get_render_attribute_string( 'root' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div ' . $this->get_render_attribute_string( 'canvas' ) . '></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="wpz-world-map__tip-style" hidden></span>';

		$items = $found['list'];
		foreach ( $pins as $pin ) {
			if ( '' !== $pin['name'] ) {
				$items[] = array(
					'label'  => $pin['name'],
					'link'   => $pin['link'],
					'target' => $pin['target'],
					'rel'    => '',
					'color'  => $data['style']['pin'],
				);
			}
		}

		if ( $items ) {
			echo '<ul ' . $this->get_render_attribute_string( 'list' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			foreach ( $items as $item ) {
				$style = $item['color'] ? ' style="--wpz-dot:' . esc_attr( $item['color'] ) . '"' : ' style="--wpz-dot:' . esc_attr( $highlight ) . '"';
				echo '<li>';
				if ( $item['link'] ) {
					$rel = trim( ( '_blank' === $item['target'] ? 'noopener ' : '' ) . $item['rel'] );
					echo '<a href="' . esc_url( $item['link'] ) . '"' . ( $item['target'] ? ' target="' . esc_attr( $item['target'] ) . '"' : '' ) . ( $rel ? ' rel="' . esc_attr( $rel ) . '"' : '' ) . $style . '>' . esc_html( $item['label'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo '<span' . $style . '>' . esc_html( $item['label'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '</li>';
			}
			echo '</ul>';
		}

		echo '</div>';
	}
}

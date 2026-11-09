/**
 * World Map: draws the vector map (jsvectormap), colours the linked countries,
 * shows their names in a tooltip and opens their links on click.
 */
( function () {
	'use strict';

	var instances = [];

	/**
	 * Destroys maps whose widget was re-rendered (Elementor replaces the DOM),
	 * so their tooltips do not pile up in <body>.
	 */
	function cleanup() {
		instances = instances.filter( function ( item ) {
			if ( document.body.contains( item.root ) ) {
				return true;
			}
			// Not map.destroy(): that flushes the library's event registry for
			// every map on the page. Dropping the tooltip is what matters.
			try {
				if ( item.map._tooltip ) {
					item.map._tooltip.dispose();
				}
			} catch ( e ) {}
			if ( item.observer ) {
				item.observer.disconnect();
			}
			return false;
		} );
	}

	/**
	 * The tooltip lives in <body>, outside the widget, so it cannot inherit the
	 * widget's styles: copy them over from the hidden style carrier instead.
	 */
	function styleTip( root, el ) {
		if ( ! el ) {
			return;
		}
		var carrier = root.querySelector( '.wpz-world-map__tip-style' );
		var cs = carrier ? window.getComputedStyle( carrier ) : null;
		var vars = window.getComputedStyle( root );
		var map = {
			'--jvm-tooltip-bg-color': '--wpz-map-tip-bg',
			'--jvm-tooltip-color': '--wpz-map-tip-color',
			'--jvm-tooltip-radius': '--wpz-map-tip-radius',
			'--jvm-tooltip-padding': '--wpz-map-tip-padding',
			'--jvm-tooltip-shadow': '--wpz-map-tip-shadow'
		};
		Object.keys( map ).forEach( function ( target ) {
			var value = vars.getPropertyValue( map[ target ] ).trim();
			if ( value ) {
				el.style.setProperty( target, value );
			}
		} );
		if ( cs ) {
			[ 'fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'lineHeight', 'letterSpacing', 'textTransform' ].forEach( function ( prop ) {
				el.style[ prop ] = cs[ prop ];
			} );
		}
		el.classList.add( 'wpz-world-map__tip' );
	}

	function open( link, target, event ) {
		if ( ! link ) {
			return;
		}
		if ( '_blank' === target || event.metaKey || event.ctrlKey ) {
			window.open( link, '_blank', 'noopener' );
		} else {
			window.location.href = link;
		}
	}

	function init( root ) {
		if ( ! root || ! window.jsVectorMap ) {
			return;
		}
		var canvas = root.querySelector( '.wpz-world-map__canvas' );
		if ( ! canvas || canvas.getAttribute( 'data-ready' ) ) {
			return;
		}
		var cfg;
		try {
			cfg = JSON.parse( canvas.getAttribute( 'data-map' ) );
		} catch ( e ) {
			return;
		}
		// No width yet (a closed tab or accordion, a background page): jsvectormap
		// would start at scale 0 and divide by it on the first resize (scale(NaN)).
		// Wait until the canvas is laid out.
		if ( ! canvas.clientWidth && window.ResizeObserver ) {
			var waiter = new ResizeObserver( function () {
				if ( canvas.clientWidth ) {
					waiter.disconnect();
					init( root );
				}
			} );
			waiter.observe( canvas );
			return;
		}
		cleanup();

		var st = cfg.style || {};
		var regions = cfg.regions || {};
		var pins = cfg.pins || [];
		var ratio = { world_merc: '900 / 583.08', world: '900 / 440.71' };

		if ( ratio[ cfg.map ] ) {
			canvas.style.aspectRatio = ratio[ cfg.map ];
		}
		// A selector string, not the element: inside the Elementor editor the
		// library's option merge turns the element into a plain object.
		if ( ! canvas.id ) {
			canvas.id = 'wpz-world-map-' + Math.random().toString( 36 ).slice( 2, 9 );
		}

		var options = {
			selector: '#' + canvas.id,
			map: cfg.map,
			backgroundColor: st.bg || 'transparent',
			draggable: !! cfg.zoom,
			zoomButtons: !! cfg.zoom,
			zoomOnScroll: false,
			bindTouchEvents: !! cfg.zoom,
			showTooltip: !! cfg.tooltip,
			regionStyle: {
				initial: { fill: st.land, fillOpacity: 1, stroke: st.border, strokeWidth: st.borderWidth },
				hover: {},
				selected: {},
				selectedHover: {}
			},
			markerStyle: {
				initial: { r: st.pinSize, fill: st.pin, fillOpacity: 1, stroke: st.pinBorder, strokeWidth: 2, strokeOpacity: 1 },
				hover: { fill: st.hover || st.pin, r: st.pinSize * 1.3, cursor: 'pointer' },
				selected: {},
				selectedHover: {}
			},
			onLoaded: function ( map ) {
				Object.keys( map.regions ).forEach( function ( code ) {
					var el = map.regions[ code ].element;
					var r = regions[ code ];
					if ( r ) {
						var fill = r.fill || st.highlight;
						el.setStyle( { fill: fill } );
						el.shape.style.hover = {
							fill: st.hover || fill,
							fillOpacity: st.hover ? 1 : 0.72,
							cursor: r.link ? 'pointer' : 'default'
						};
					} else {
						el.shape.style.hover = {};
					}
				} );
			},
			onRegionTooltipShow: function ( event, tip, code ) {
				var r = regions[ code ];
				if ( ! r ) {
					event.preventDefault();
					return;
				}
				var region = map && map.regions && map.regions[ code ];
				tip.text( r.label || ( region && region.config && region.config.name ) || code );
				styleTip( root, tip.getElement() );
			},
			onMarkerTooltipShow: function ( event, tip ) {
				styleTip( root, tip.getElement() );
			},
			onRegionClick: function ( event, code ) {
				var r = regions[ code ];
				if ( r ) {
					open( r.link, r.target, event );
				}
			},
			onMarkerClick: function ( event, index ) {
				var p = pins[ parseInt( index, 10 ) ];
				if ( p ) {
					open( p.link, p.target, event );
				}
			}
		};

		if ( pins.length ) {
			options.markers = pins.map( function ( p ) {
				return { coords: p.coords, name: p.name };
			} );
		}
		var focus = null;
		if ( cfg.focus && ( cfg.focus.regions || cfg.focus.coords ) ) {
			focus = { regions: cfg.focus.regions, coords: cfg.focus.coords, scale: cfg.focus.scale, animate: false };
			options.focusOn = focus;
		}

		var map;
		try {
			map = new window.jsVectorMap( options );
		} catch ( e ) {
			// The map data script may be printed after this one (another widget
			// on the page queued it later); the window 'load' pass picks it up.
			return;
		}
		canvas.setAttribute( 'data-ready', '1' );
		var item = { root: root, map: map, observer: null };

		if ( window.ResizeObserver ) {
			var width = canvas.clientWidth;
			item.observer = new ResizeObserver( function () {
				if ( canvas.clientWidth && canvas.clientWidth !== width ) {
					width = canvas.clientWidth;
					map.updateSize();
					if ( focus ) {
						map.setFocus( focus );
					}
				}
			} );
			item.observer.observe( canvas );
		}
		instances.push( item );
	}

	function initAll( scope ) {
		Array.prototype.forEach.call( ( scope || document ).querySelectorAll( '.wpz-world-map' ), init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			initAll();
		} );
	} else {
		initAll();
	}
	if ( 'complete' !== document.readyState ) {
		window.addEventListener( 'load', function () {
			initAll();
		} );
	}

	// Elementor editor: widgets are re-rendered without a page load. Elementor
	// fires this with jQuery, so it has to be listened to with jQuery.
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/wpzoom-elementor-addons-world-map.default', function ( $scope ) {
					initAll( $scope[ 0 ] );
				} );
			}
		} );
	}
}() );

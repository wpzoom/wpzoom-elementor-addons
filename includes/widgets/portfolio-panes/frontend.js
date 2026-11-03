/**
 * Portfolio Panes: one project per screen.
 *
 * The stage is sticky and the panes are stacked inside it; the scroll distance
 * comes from one invisible spacer per pane. On scroll the widget reads how far
 * it has been scrolled, in panes (0 … n-1), and places every pane for the
 * chosen effect:
 *   cover    – the next pane slides up over the pinned one, which dims
 *   parallax – the same, but the footage lags behind for depth
 *   fade     – the current pane dissolves to black, then the next fades in
 * The hover video of the visible pane plays; the others are paused and only
 * the neighbours are loaded. Reduced motion turns the effect off (static stack).
 */
( function () {
	'use strict';

	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	var canHover = window.matchMedia( '(hover: hover)' );
	var instances = [];

	function clamp( v, lo, hi ) {
		return v < lo ? lo : v > hi ? hi : v;
	}

	function init( root ) {
		if ( ! root || root.dataset.wpzPanesReady ) {
			return;
		}
		root.dataset.wpzPanesReady = '1';

		var stage = root.querySelector( '.wpz-panes__stage' );
		var panes = Array.prototype.slice.call( root.querySelectorAll( '.wpz-pane' ) );
		var dots = Array.prototype.slice.call( root.querySelectorAll( '.wpz-panes__dot' ) );
		var cue = root.querySelector( '.wpz-panes__cue' );
		var effect = root.dataset.effect || 'cover';
		var loopMode = root.dataset.loop || 'always';
		var n = panes.length;
		var active = -1; // So the first setActive( 0 ) loads and plays the first pane's footage.
		var ticking = false;
		var isStatic = effect === 'none' || reduced.matches;

		if ( ! stage || ! n ) {
			return;
		}

		/* ---------- footage ---------- */

		function loadLoop( pane ) {
			var video = pane.querySelector( '.wpz-pane__loop' );
			if ( video && ! video.getAttribute( 'src' ) ) {
				video.addEventListener( 'playing', function () {
					video.classList.add( 'is-ready' );
				} );
				video.src = video.dataset.src; // Setting src starts the load; an explicit load() here would cancel the play() that follows.
			}
			return video;
		}

		function playLoop( pane ) {
			var video = loadLoop( pane );
			if ( ! video || pane.classList.contains( 'is-playing' ) ) {
				return;
			}
			var promise = video.play();
			if ( promise && promise.catch ) {
				promise.catch( function () {
					// Not ready yet (or interrupted): play as soon as it can.
					video.addEventListener( 'canplay', function retry() {
						video.removeEventListener( 'canplay', retry );
						if ( pane.classList.contains( 'is-active' ) && ! pane.classList.contains( 'is-playing' ) ) {
							var p2 = video.play();
							if ( p2 && p2.catch ) {
								p2.catch( function () {} );
							}
						}
					} );
				} );
			}
		}

		function pauseLoop( pane ) {
			var video = pane.querySelector( '.wpz-pane__loop' );
			if ( video && ! video.paused ) {
				video.pause();
			}
		}

		// "Hover only" plays on hover (and always on touch screens, where there is no hover).
		var autoPlays = loopMode === 'always' || ! canHover.matches;

		function setActive( index ) {
			if ( index === active && panes[ index ].classList.contains( 'is-active' ) ) {
				return;
			}
			panes.forEach( function ( pane, k ) {
				var on = k === index;
				pane.classList.toggle( 'is-active', on );
				if ( Math.abs( k - index ) <= 1 ) {
					loadLoop( pane );
				}
				if ( on ) {
					if ( autoPlays ) {
						playLoop( pane );
					}
				} else {
					pauseLoop( pane );
					stopPlayer( pane );
				}
			} );
			dots.forEach( function ( dot, k ) {
				dot.classList.toggle( 'is-active', k === index );
			} );
			active = index;
		}

		/* ---------- play in the pane ---------- */

		function stopPlayer( pane ) {
			var playing = pane.querySelector( '.wpz-pane__playing' );
			if ( playing ) {
				playing.remove();
			}
			var close = pane.querySelector( '.wpz-pane__close' );
			if ( close ) {
				close.remove();
			}
			pane.classList.remove( 'is-playing' );
		}

		function startPlayer( pane ) {
			var template = pane.querySelector( '.wpz-pane__player' );
			if ( ! template || pane.classList.contains( 'is-playing' ) ) {
				return;
			}
			pauseLoop( pane );
			var holder = document.createElement( 'div' );
			holder.className = 'wpz-pane__playing';
			holder.appendChild( template.content.cloneNode( true ) );
			pane.appendChild( holder );

			var close = document.createElement( 'button' );
			close.type = 'button';
			close.className = 'wpz-pane__close';
			close.setAttribute( 'aria-label', 'Close' );
			close.innerHTML = '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M2 2l12 12M14 2L2 14"/></svg>';
			close.addEventListener( 'click', function () {
				stopPlayer( pane );
				if ( autoPlays ) {
					playLoop( pane );
				}
				pane.querySelector( '.wpz-pane__trigger' ).focus();
			} );
			pane.appendChild( close );
			pane.classList.add( 'is-playing' );

			if ( document.body.classList.contains( 'wpz-mediaelement-player' ) && window.wp && window.wp.mediaelement && window.wp.mediaelement.initialize ) {
				window.wp.mediaelement.initialize(); // WordPress player, skinned by the theme's Video Player Style.
			}
			var video = holder.querySelector( 'video' );
			if ( video ) {
				video.focus();
				var promise = video.play();
				if ( promise && promise.catch ) {
					promise.catch( function () {} );
				}
			}
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' || event.key === 'Esc' ) {
				var playing = root.querySelector( '.wpz-pane.is-playing' );
				if ( playing ) {
					stopPlayer( playing );
					if ( autoPlays ) {
						playLoop( playing );
					}
				}
			}
		} );

		panes.forEach( function ( pane ) {
			var trigger = pane.querySelector( '.wpz-pane__trigger' );
			if ( trigger && trigger.tagName === 'BUTTON' ) {
				trigger.addEventListener( 'click', function () {
					startPlayer( pane );
				} );
			}
			if ( ! autoPlays ) {
				pane.addEventListener( 'mouseenter', function () {
					if ( pane.classList.contains( 'is-active' ) ) {
						playLoop( pane );
					}
				} );
				pane.addEventListener( 'mouseleave', function () {
					pauseLoop( pane );
				} );
			}
		} );

		/* ---------- static: no effect ---------- */

		if ( isStatic ) {
			root.classList.add( 'wpz-panes--static' );
			if ( 'IntersectionObserver' in window ) {
				var io = new IntersectionObserver( function ( entries ) {
					entries.forEach( function ( entry ) {
						if ( entry.isIntersecting ) {
							setActive( panes.indexOf( entry.target ) );
						}
					} );
				}, { threshold: 0.55 } );
				panes.forEach( function ( pane ) {
					io.observe( pane );
				} );
			} else {
				setActive( 0 );
			}
			return;
		}

		/* ---------- scroll effects ---------- */

		document.documentElement.classList.add( 'wpz-panes-page' );
		if ( root.classList.contains( 'wpz-panes--snap' ) ) {
			document.documentElement.classList.add( 'wpz-panes-snap' );
		}

		var H = stage.offsetHeight || window.innerHeight;

		function place( k, ty, dim, text, mediaTy, opacity ) {
			var pane = panes[ k ];
			pane.style.transform = ty === 0 ? 'none' : 'translateY(' + ( ty * 100 ).toFixed( 2 ) + '%)';
			pane.style.opacity = opacity === undefined ? '' : String( opacity );
			pane.style.visibility = ( opacity === 0 || ty >= 1 ) ? 'hidden' : '';
			pane.style.setProperty( '--wpz-dim', ( dim * 0.6 ).toFixed( 3 ) );
			pane.style.setProperty( '--wpz-text', text.toFixed( 3 ) );
			pane.style.setProperty( '--wpz-text-shift', ( ( 1 - text ) * 28 ).toFixed( 1 ) );
			var media = pane.querySelector( '.wpz-pane__media' );
			if ( media ) {
				// Percentages of the media box, which is 16% taller than the pane (CSS), so ±6.9% of it stays covered.
				media.style.transform = mediaTy ? 'translateY(' + ( mediaTy * 100 ).toFixed( 2 ) + '%)' : 'none';
			}
		}

		function apply() {
			ticking = false;
			var top = root.getBoundingClientRect().top;
			var p = clamp( -top / H, 0, n - 1 );
			var k0 = Math.floor( p );
			var f = p - k0;
			var k;

			if ( effect === 'fade' ) {
				for ( k = 0; k < n; k++ ) {
					var o = 0;
					if ( k === k0 ) {
						o = f < 0.5 ? 1 - f / 0.5 : 0;
					} else if ( k === k0 + 1 ) {
						o = f > 0.5 ? ( f - 0.5 ) / 0.5 : 0;
					}
					if ( k === n - 1 && k === k0 ) {
						o = 1; // Last pane stays.
					}
					place( k, 0, 0, o, 0, o );
				}
			} else {
				// Covered panes drift up a little (both effects); with Parallax the incoming footage lags too.
				var push = 0.06;
				var lag = effect === 'parallax' ? 0.065 : 0;
				for ( k = 0; k < n; k++ ) {
					if ( k < k0 ) {
						place( k, 0, 1, 0, -push );
					} else if ( k === k0 ) {
						place( k, 0, f, clamp( 1 - f * 1.6, 0, 1 ), -f * push );
					} else if ( k === k0 + 1 ) {
						place( k, 1 - f, 0, clamp( ( f - 0.35 ) / 0.5, 0, 1 ), lag ? ( 1 - f ) * lag : 0 );
					} else {
						place( k, 1, 0, 0, 0 );
					}
				}
			}

			if ( cue ) {
				cue.style.setProperty( '--wpz-cue', String( clamp( 1 - p * 3, 0, 0.8 ) ) );
			}

			setActive( f < 0.5 ? k0 : Math.min( k0 + 1, n - 1 ) );
		}

		function request() {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( apply );
			}
		}

		window.addEventListener( 'scroll', request, { passive: true } );
		window.addEventListener( 'resize', function () {
			H = stage.offsetHeight || window.innerHeight;
			request();
		} );

		dots.forEach( function ( dot, k ) {
			dot.addEventListener( 'click', function () {
				var start = root.getBoundingClientRect().top + window.pageYOffset;
				window.scrollTo( { top: start + k * H, behavior: reduced.matches ? 'auto' : 'smooth' } );
			} );
		} );

		if ( reduced.addEventListener ) {
			reduced.addEventListener( 'change', function () {
				window.location.reload();
			} );
		}

		apply();
		instances.push( root );
	}

	function initAll( scope ) {
		Array.prototype.forEach.call( ( scope || document ).querySelectorAll( '.wpz-panes' ), init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			initAll();
		} );
	} else {
		initAll();
	}

	// Elementor editor: widgets are re-rendered without a page load.
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/wpzoom-elementor-addons-portfolio-panes.default', function ( $scope ) {
					initAll( $scope[ 0 ] );
				} );
			}
		} );
	}
}() );

/**
 * Portfolio Index: hover a title to preview the project in the panel,
 * click to keep it open, press play to watch the project's video.
 * On screens narrower than 1025px the panel is hidden and titles are plain links.
 */
( function () {
	'use strict';

	var desktop = window.matchMedia( '(min-width: 1025px)' );
	var canHover = window.matchMedia( '(hover: hover)' );

	function init( root ) {
		if ( ! root || root.dataset.wpzPindexReady ) {
			return;
		}
		root.dataset.wpzPindexReady = '1';

		var list = root.querySelector( '.wpz-pindex__list' );
		var items = Array.prototype.slice.call( root.querySelectorAll( '.wpz-pindex__item' ) );
		var panels = Array.prototype.slice.call( root.querySelectorAll( '.wpz-pindex__panel' ) );
		var clickOpensPanel = 'post' !== root.dataset.click;
		var selected = -1;
		var shown = -1;
		var stage = root.querySelector( '.wpz-pindex__stage' );
		var openMobile = -1; // index of the panel shown under its title on small screens

		items.forEach( function ( item, i ) {
			if ( item.classList.contains( 'is-active' ) ) {
				selected = i;
				shown = i;
			}
		} );

		function stopPlayer( panel ) {
			var playing = panel.querySelector( '.wpz-pindex__playing' );
			if ( playing ) {
				playing.remove();
			}
			var button = panel.querySelector( '.wpz-pindex__play' );
			if ( button ) {
				button.hidden = false;
			}
		}

		function playPreview( panel ) {
			var video = panel.querySelector( '.wpz-pindex__preview' );
			if ( ! video || panel.querySelector( '.wpz-pindex__playing' ) ) {
				return;
			}
			if ( ! video.getAttribute( 'src' ) ) {
				video.addEventListener( 'playing', function () {
					video.classList.add( 'is-ready' );
				} );
				video.src = video.dataset.src;
			}
			var promise = video.play();
			if ( promise && promise.catch ) {
				promise.catch( function () {} );
			}
		}

		function pausePreview( panel ) {
			var video = panel.querySelector( '.wpz-pindex__preview' );
			if ( video && ! video.paused ) {
				video.pause();
			}
		}

		function show( index ) {
			if ( index === shown || ! panels[ index ] ) {
				return;
			}
			if ( shown > -1 ) {
				items[ shown ].classList.remove( 'is-active' );
				panels[ shown ].classList.remove( 'is-active' );
				panels[ shown ].setAttribute( 'aria-hidden', 'true' );
				pausePreview( panels[ shown ] );
				stopPlayer( panels[ shown ] );
			}
			items[ index ].classList.add( 'is-active' );
			panels[ index ].classList.add( 'is-active' );
			panels[ index ].removeAttribute( 'aria-hidden' );
			playPreview( panels[ index ] );
			shown = index;
		}

		/*
		 * Small screens: the stage is hidden, so a tapped title gets its panel moved
		 * right under it (one open at a time); tapping it again closes it.
		 */
		function mobileClose() {
			if ( openMobile < 0 ) {
				return;
			}
			var panel = panels[ openMobile ];
			pausePreview( panel );
			stopPlayer( panel );
			panel.classList.remove( 'is-active' );
			panel.setAttribute( 'aria-hidden', 'true' );
			items[ openMobile ].classList.remove( 'is-open' );
			stage.appendChild( panel );
			openMobile = -1;
		}

		function mobileOpen( index ) {
			mobileClose();
			var panel = panels[ index ];
			items[ index ].appendChild( panel );
			items[ index ].classList.add( 'is-open' );
			panel.classList.add( 'is-active' );
			panel.removeAttribute( 'aria-hidden' );
			playPreview( panel );
			openMobile = index;
		}

		// Back on a wide screen: panels return to the stage with the desktop state.
		if ( desktop.addEventListener ) {
			desktop.addEventListener( 'change', function ( event ) {
				if ( ! event.matches ) {
					return;
				}
				mobileClose();
				if ( shown > -1 ) {
					items[ shown ].classList.add( 'is-active' );
					panels[ shown ].classList.add( 'is-active' );
					panels[ shown ].removeAttribute( 'aria-hidden' );
				}
			} );
		}

		/*
		 * Hover intent (the "Amazon menu" trick): while the cursor travels from a title
		 * towards the panel it crosses other titles. If it is moving inside the triangle
		 * between its last position and the panel's near corners, the crossed titles are
		 * not previewed right away; they get a short delay that is renewed while the
		 * cursor keeps heading for the panel, and is cancelled when it arrives there.
		 * A cursor that stops on a title previews it as soon as the delay runs out.
		 */
		var AIM_DELAY = 300;
		var AIM_TOLERANCE = 75;
		var positions = []; // last two cursor positions
		var aimTimer = null;
		var aimIndex = -1;

		function trackMouse( event ) {
			positions.push( { x: event.pageX, y: event.pageY } );
			if ( positions.length > 3 ) {
				positions.shift();
			}
		}

		function slope( a, b ) {
			return ( b.y - a.y ) / ( b.x - a.x );
		}

		function aimingAtStage() {
			if ( ! stage || positions.length < 2 ) {
				return false;
			}
			var current = positions[ positions.length - 1 ];
			var previous = positions[ 0 ];
			if ( current.x === previous.x && current.y === previous.y ) {
				return false; // The cursor stopped: the title under it is wanted.
			}
			var rect = stage.getBoundingClientRect();
			var listRect = list.getBoundingClientRect();
			var onRight = rect.left >= listRect.left;
			var edgeX = ( onRight ? rect.left : rect.right ) + window.pageXOffset;
			var upper = { x: edgeX, y: rect.top + window.pageYOffset - AIM_TOLERANCE };
			var lower = { x: edgeX, y: rect.bottom + window.pageYOffset + AIM_TOLERANCE };
			if ( ! onRight ) {
				// Mirror horizontally so the panel is always "to the right" for the slope test.
				current = { x: -current.x, y: current.y };
				previous = { x: -previous.x, y: previous.y };
				upper.x = -upper.x;
				lower.x = -lower.x;
			}
			if ( current.x >= upper.x ) {
				return false; // Already past the panel's near edge.
			}
			var decreasing = slope( current, upper );
			var increasing = slope( current, lower );
			var prevDecreasing = slope( previous, upper );
			var prevIncreasing = slope( previous, lower );
			return decreasing < prevDecreasing && increasing > prevIncreasing;
		}

		function cancelAim() {
			if ( aimTimer ) {
				clearTimeout( aimTimer );
				aimTimer = null;
			}
			aimIndex = -1;
		}

		function possiblyShow( index ) {
			cancelAim();
			if ( aimingAtStage() ) {
				aimIndex = index;
				aimTimer = setTimeout( function () {
					aimTimer = null;
					if ( aimIndex === index ) {
						possiblyShow( index );
					}
				}, AIM_DELAY );
				return;
			}
			show( index );
		}

		root.addEventListener( 'mousemove', trackMouse );

		if ( stage ) {
			stage.addEventListener( 'mouseenter', cancelAim ); // Arrived at the panel: keep what is shown.
		}

		items.forEach( function ( item, i ) {
			var link = item.querySelector( '.wpz-pindex__link' );

			item.addEventListener( 'mouseenter', function () {
				if ( desktop.matches && canHover.matches ) {
					possiblyShow( i );
				}
			} );

			item.addEventListener( 'mouseleave', function () {
				if ( aimIndex === i ) {
					cancelAim(); // Passed over on the way to the panel.
				}
			} );

			link.addEventListener( 'focus', function () {
				if ( desktop.matches ) {
					show( i );
				}
			} );

			// The whole row is clickable, not only the title text.
			item.addEventListener( 'click', function ( event ) {
				if ( event.target.closest( '.wpz-pindex__panel' ) ) {
					return; // Play button and links inside an opened panel.
				}
				if ( ! desktop.matches ) {
					event.preventDefault();
					event.stopPropagation();
					if ( openMobile === i ) {
						mobileClose();
					} else {
						mobileOpen( i );
					}
					return;
				}
				if ( ! clickOpensPanel ) {
					return; // Follow the link (the theme may open it in its modal).
				}
				event.preventDefault();
				event.stopPropagation();
				selected = i;
				cancelAim();
				show( i );
			} );
		} );

		// Leaving the list (or the widget) keeps the last previewed project in the panel.
		root.addEventListener( 'mouseleave', cancelAim );

		panels.forEach( function ( panel ) {
			var button = panel.querySelector( '.wpz-pindex__play' );
			var template = panel.querySelector( '.wpz-pindex__player' );
			if ( ! button || ! template ) {
				return;
			}
			button.addEventListener( 'click', function () {
				pausePreview( panel );
				var holder = document.createElement( 'div' );
				holder.className = 'wpz-pindex__playing';
				holder.appendChild( template.content.cloneNode( true ) );
				panel.querySelector( '.wpz-pindex__media' ).appendChild( holder );
				if ( document.body.classList.contains( 'wpz-mediaelement-player' ) && window.wp && window.wp.mediaelement && window.wp.mediaelement.initialize ) {
					window.wp.mediaelement.initialize(); // WordPress player, skinned by the theme's Video Player Style
				}
				button.hidden = true;

				var video = holder.querySelector( 'video' );
				if ( video ) {
					video.focus();
					var promise = video.play();
					if ( promise && promise.catch ) {
						promise.catch( function () {} );
					}
				}
			} );
		} );

		if ( shown > -1 && desktop.matches ) {
			playPreview( panels[ shown ] );
		}
	}

	function initAll( scope ) {
		Array.prototype.forEach.call( ( scope || document ).querySelectorAll( '.wpz-pindex' ), init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			initAll();
		} );
	} else {
		initAll();
	}

	// Elementor editor: widgets are re-rendered without a page load.
	// Elementor triggers this as a jQuery event on window.
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/wpzoom-elementor-addons-portfolio-index.default', function ( $scope ) {
					initAll( $scope[ 0 ] );
				} );
			}
		} );
	}
}() );

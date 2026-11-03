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

		items.forEach( function ( item, i ) {
			var link = item.querySelector( '.wpz-pindex__link' );

			item.addEventListener( 'mouseenter', function () {
				if ( desktop.matches && canHover.matches ) {
					show( i );
				}
			} );

			link.addEventListener( 'focus', function () {
				if ( desktop.matches ) {
					show( i );
				}
			} );

			// The whole row is clickable, not only the title text.
			item.addEventListener( 'click', function ( event ) {
				if ( ! clickOpensPanel || ! desktop.matches ) {
					return; // Follow the link (the theme may open it in its modal).
				}
				event.preventDefault();
				event.stopPropagation();
				selected = i;
				show( i );
			} );
		} );

		// Leaving the list goes back to the project that was clicked.
		list.addEventListener( 'mouseleave', function () {
			if ( desktop.matches && selected > -1 ) {
				show( selected );
			}
		} );

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
				if ( window.wp && window.wp.mediaelement && window.wp.mediaelement.initialize ) {
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

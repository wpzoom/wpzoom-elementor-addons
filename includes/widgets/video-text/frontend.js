/**
 * Video Text: fits every line to the width of the box and drifts the video
 * behind the letters on scroll (parallax). The blend itself is pure CSS.
 */
( function () {
	'use strict';

	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	function fit( root ) {
		if ( ! root.classList.contains( 'wpz-video-text--fit' ) ) {
			return;
		}
		var width = root.clientWidth;
		if ( ! width ) {
			return;
		}
		Array.prototype.forEach.call( root.querySelectorAll( '.wpz-video-text__line' ), function ( line ) {
			line.style.fontSize = '100px';
			var measured = line.getBoundingClientRect().width;
			if ( measured > 0 ) {
				line.style.fontSize = ( 100 * width / measured ).toFixed( 2 ) + 'px';
			}
		} );
	}

	function parallax( root ) {
		var media = root.querySelector( '.wpz-video-text__media' );
		var strength = parseFloat( root.dataset.parallax || '0' );
		if ( ! media || ! strength || reduced.matches ) {
			return;
		}
		var ticking = false;
		function update() {
			ticking = false;
			var r = root.getBoundingClientRect();
			if ( r.bottom < 0 || r.top > window.innerHeight ) {
				return;
			}
			// 0.77: the scaled clip has 15% spare on each side; keep the drift inside that.
			media.style.transform = 'scale(1.3) translateY(' + Math.round( -r.top * strength * 0.77 ) + 'px)';
		}
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}, { passive: true } );
		update();
	}

	function init( root ) {
		if ( ! root || root.dataset.wpzVideoTextReady ) {
			return;
		}
		root.dataset.wpzVideoTextReady = '1';
		fit( root );
		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( function () { fit( root ); } );
		}
		if ( window.ResizeObserver ) {
			new ResizeObserver( function () { fit( root ); } ).observe( root );
		} else {
			window.addEventListener( 'resize', function () { fit( root ); } );
		}
		parallax( root );
		var video = root.querySelector( 'video' );
		if ( video && video.paused ) {
			var p = video.play();
			if ( p && p.catch ) { p.catch( function () {} ); }
		}
	}

	function initAll( scope ) {
		Array.prototype.forEach.call( ( scope || document ).querySelectorAll( '.wpz-video-text' ), init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { initAll(); } );
	} else {
		initAll();
	}

	// Elementor editor: widgets are re-rendered without a page load.
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/wpzoom-elementor-addons-video-text.default', function ( $scope ) {
					var root = $scope[ 0 ].querySelector( '.wpz-video-text' );
					if ( root ) { delete root.dataset.wpzVideoTextReady; }
					initAll( $scope[ 0 ] );
				} );
			}
		} );
	}
}() );

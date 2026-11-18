/**
 * WPZOOM Motion. Reveal on scroll: adds .wpz-in to elements with a wpz-reveal-* class when they enter
 * the viewport. Roll text on hover: duplicates the label of .wpz-roll-yes buttons and linked headings.
 * "Text, line by line" wraps the words for the length of the animation and then puts the original
 * markup back, so the text reflows normally afterwards.
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var TEXT_BLOCKS = 'h1,h2,h3,h4,h5,h6,p,li,blockquote,figcaption,.elementor-heading-title,.elementor-button-text';

	window.wpzMotion = true; // tells the head script the file loaded

	function cssTime( el, name, fallback ) {
		var v = getComputedStyle( el ).getPropertyValue( name ).trim();
		if ( ! v ) { return fallback; }
		return v.indexOf( 'ms' ) > -1 ? parseFloat( v ) : parseFloat( v ) * 1000;
	}

	// Wrap every word of every text node under `node` in <span class="wpz-w"><span class="wpz-wi">.
	function wrapWords( node, out ) {
		var kids = Array.prototype.slice.call( node.childNodes );
		kids.forEach( function ( kid ) {
			if ( kid.nodeType === 3 ) {
				var parts = kid.nodeValue.split( /(\s+)/ );
				if ( parts.length === 1 && ! parts[ 0 ].trim() ) { return; }
				var frag = document.createDocumentFragment();
				parts.forEach( function ( part ) {
					if ( ! part ) { return; }
					if ( /^\s+$/.test( part ) ) {
						frag.appendChild( document.createTextNode( ' ' ) );
						return;
					}
					var w = document.createElement( 'span' );
					var wi = document.createElement( 'span' );
					w.className = 'wpz-w';
					wi.className = 'wpz-wi';
					wi.textContent = part;
					w.appendChild( wi );
					frag.appendChild( w );
					out.push( w );
				} );
				node.replaceChild( frag, kid );
			} else if ( kid.nodeType === 1 && ! /^(script|style|svg|img|video|iframe|br)$/i.test( kid.tagName ) ) {
				wrapWords( kid, out );
			}
		} );
	}

	function revealLines( el ) {
		var blocks = Array.prototype.slice.call( el.querySelectorAll( TEXT_BLOCKS ) ).filter( function ( b ) {
			// keep the outermost text blocks only (a <p> inside a <li> is handled with the <li>)
			var p = b.parentElement;
			while ( p && p !== el ) {
				if ( p.matches( TEXT_BLOCKS ) ) { return false; }
				p = p.parentElement;
			}
			return true;
		} );
		if ( ! blocks.length ) { blocks = [ el.querySelector( '.elementor-widget-container' ) || el ]; }

		var saved = blocks.map( function ( b ) { return b.innerHTML; } );
		var line = -1;
		blocks.forEach( function ( b ) {
			var words = [];
			wrapWords( b, words );
			var top = null;
			words.forEach( function ( w ) {
				var t = Math.round( w.getBoundingClientRect().top );
				if ( top === null || Math.abs( t - top ) > 4 ) { line++; top = t; }
				w.style.setProperty( '--wpz-l', line );
			} );
		} );

		el.classList.add( 'wpz-in' );

		var total = cssTime( el, '--wpz-reveal-delay', 0 ) + ( line + 1 ) * cssTime( el, '--wpz-reveal-step', 90 ) + cssTime( el, '--wpz-reveal-time', 1100 ) + 120;
		setTimeout( function () {
			blocks.forEach( function ( b, i ) {
				if ( b.querySelector( '.wpz-w' ) ) { b.innerHTML = saved[ i ]; }
			} );
		}, total );
	}

	function reveal( el ) {
		if ( el.classList.contains( 'wpz-in' ) ) { return; }
		if ( el.classList.contains( 'wpz-reveal-lines' ) && ! reduced ) {
			revealLines( el );
		} else {
			el.classList.add( 'wpz-in' );
		}
	}

	// Roll text on hover: put the label and a hidden copy of it in one clipped box.
	function roll( el ) {
		var t = el.querySelector( '.elementor-button-text' ) || el.querySelector( '.elementor-heading-title a' );
		if ( ! t || t.querySelector( '.wpz-roll' ) || t.children.length ) { return; }
		var text = t.textContent;
		var box = document.createElement( 'span' );
		var a = document.createElement( 'span' );
		var b = document.createElement( 'span' );
		box.className = 'wpz-roll';
		a.className = 'wpz-roll__a';
		b.className = 'wpz-roll__b';
		b.setAttribute( 'aria-hidden', 'true' );
		a.textContent = text;
		b.textContent = text;
		box.appendChild( a );
		box.appendChild( b );
		t.textContent = '';
		t.appendChild( box );
	}

	function start() {
		if ( ! reduced ) {
			Array.prototype.forEach.call( document.querySelectorAll( '.wpz-roll-yes' ), roll );
		}

		var els = Array.prototype.slice.call( document.querySelectorAll( '[class*="wpz-reveal-"]' ) );
		if ( ! els.length ) { return; }

		if ( reduced || ! ( 'IntersectionObserver' in window ) ) {
			els.forEach( function ( el ) { el.classList.add( 'wpz-in' ); } );
			return;
		}

		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) { return; }
				io.unobserve( entry.target );
				window.requestAnimationFrame( function () { reveal( entry.target ); } );
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0 } );

		els.forEach( function ( el ) { io.observe( el ); } );
	}

	// Line breaks depend on the web font, so wait for it (but never longer than a second).
	function whenFontsReady( cb ) {
		var done = false;
		function go() { if ( ! done ) { done = true; cb(); } }
		if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( go ); }
		setTimeout( go, 1000 );
	}

	function boot() {
		if ( document.body.classList.contains( 'elementor-editor-active' ) ) {
			// In the editor nothing is hidden; play the effect when an element is added or re-rendered.
			window.addEventListener( 'elementor/frontend/init', function () {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
					var el = $scope[ 0 ];
					if ( el && /wpz-roll-yes/.test( el.className ) ) { roll( el ); }
					if ( el && /wpz-reveal-/.test( el.className ) ) {
						el.classList.remove( 'wpz-in' );
						void el.offsetWidth;
						reveal( el );
					}
				} );
			} );
			return;
		}
		whenFontsReady( start );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();

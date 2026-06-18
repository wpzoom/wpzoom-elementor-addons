(function ($) {
	'use strict';

	var NS_CLICK = 'click.wpzVlb';
	var NS_KEYDOWN = 'keydown.wpzVlb';

	function getYouTubeVideoId(url) {
		var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
		var match = url.match(regExp);
		return match && match[2].length === 11 ? match[2] : null;
	}

	function getVimeoVideoId(url) {
		var regExp = /(?:vimeo\.com\/)([0-9]+)/;
		var match = url.match(regExp);
		return match ? match[1] : null;
	}

	function isDirectVideoFile(url) {
		var videoExtensions = ['.mp4', '.webm', '.ogg', '.mov', '.avi', '.wmv', '.flv', '.mkv', '.m4v'];
		var urlLower = url.toLowerCase();
		var cleanUrl = urlLower.split('?')[0].split('#')[0];
		return videoExtensions.some(function (ext) {
			return cleanUrl.endsWith(ext);
		});
	}

	function getVideoMimeType(url) {
		var urlLower = url.toLowerCase();
		var cleanUrl = urlLower.split('?')[0].split('#')[0];
		if (cleanUrl.endsWith('.mp4') || cleanUrl.endsWith('.m4v')) {
			return 'video/mp4';
		}
		if (cleanUrl.endsWith('.webm')) {
			return 'video/webm';
		}
		if (cleanUrl.endsWith('.ogg')) {
			return 'video/ogg';
		}
		if (cleanUrl.endsWith('.mov')) {
			return 'video/quicktime';
		}
		if (cleanUrl.endsWith('.avi')) {
			return 'video/x-msvideo';
		}
		if (cleanUrl.endsWith('.wmv')) {
			return 'video/x-ms-wmv';
		}
		if (cleanUrl.endsWith('.flv')) {
			return 'video/x-flv';
		}
		if (cleanUrl.endsWith('.mkv')) {
			return 'video/x-matroska';
		}
		return 'video/mp4';
	}

	function getVideoEmbedHtml(url) {
		if (url.includes('youtube.com') || url.includes('youtu.be')) {
			var ytId = getYouTubeVideoId(url);
			if (ytId) {
				return (
					'<iframe class="wpz-vlb-modal__iframe" src="https://www.youtube.com/embed/' +
					ytId +
					'?autoplay=1&rel=0" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe>'
				);
			}
		}
		if (url.includes('vimeo.com')) {
			var vmId = getVimeoVideoId(url);
			if (vmId) {
				return (
					'<iframe class="wpz-vlb-modal__iframe" src="https://player.vimeo.com/video/' +
					vmId +
					'?autoplay=1" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe>'
				);
			}
		}
		if (isDirectVideoFile(url)) {
			var mime = getVideoMimeType(url);
			var $wrap = $('<div/>');
			var $video = $('<video/>', {
				class: 'wpz-vlb-modal__video-el',
				controls: true,
				autoplay: true,
				playsinline: true,
			});
			$video.append($('<source/>', { src: url, type: mime }));
			$wrap.append($video);
			return $wrap.html();
		}
		var $fb = $('<iframe/>', {
			class: 'wpz-vlb-modal__iframe',
			src: url,
			frameborder: 0,
			allowfullscreen: 'allowfullscreen',
		});
		return $('<div/>').append($fb).html();
	}

	function closeModal($modal) {
		$modal.removeClass('wpz-vlb-modal--active');
		$(document).off(NS_KEYDOWN);
		setTimeout(function () {
			$modal.remove();
		}, 300);
	}

	function openModal(videoUrl) {
		$('.wpz-vlb-modal').remove();

		var $modal = $(
			'<div class="wpz-vlb-modal" role="dialog" aria-modal="true" aria-label="Video">' +
				'<div class="wpz-vlb-modal__overlay" tabindex="-1"></div>' +
				'<button type="button" class="wpz-vlb-modal__close" aria-label="Close video">' +
				'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
				'<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
				'</svg>' +
				'</button>' +
				'<div class="wpz-vlb-modal__container">' +
				'<div class="wpz-vlb-modal__content">' +
				getVideoEmbedHtml(videoUrl) +
				'</div>' +
				'</div>' +
				'</div>'
		);

		$('body').append($modal);

		requestAnimationFrame(function () {
			$modal.addClass('wpz-vlb-modal--active');
		});

		$modal.find('.wpz-vlb-modal__close, .wpz-vlb-modal__overlay').on('click', function () {
			closeModal($modal);
		});

		$(document).on(NS_KEYDOWN, function (e) {
			if (e.key === 'Escape' || e.keyCode === 27) {
				closeModal($modal);
			}
		});
	}

	function initDelegatedHandler() {
		$(document)
			.off(NS_CLICK)
			.on(NS_CLICK, '.wpz-vlb-trigger', function (e) {
				e.preventDefault();
				var $t = $(this);
				if ($t.attr('aria-disabled') === 'true') {
					return;
				}
				var href = $t.attr('href');
				if (!href || href === '#' || href.trim() === '') {
					return;
				}
				openModal(href);
			});
	}

	$(window).on('elementor/frontend/init', function () {
		initDelegatedHandler();
	});

	$(function () {
		initDelegatedHandler();
	});
})(jQuery);

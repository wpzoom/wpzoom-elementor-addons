jQuery(window).on('elementor/frontend/init', function () {
	'use strict';

	class VideoSliderHandler extends elementorModules.frontend.handlers.SwiperBase {
		
		constructor() {
			super(...arguments);
			this.videoEventHandlers = new WeakMap();
			this.dimensionCache = new Map();
			this.lastResizeTime = 0;
			this.boundCleanupMethods = new Set(); // Track cleanup methods
		}
		
		getDefaultSettings() {
			return {
				selectors: {
					wrapper: '.wpz-video-slider-wrapper',
					slide: '.swiper-slide',
					lightboxTrigger: '.wpz-slide-lightbox-trigger',
					videoBackground: '.wpz-video-bg',
					slideInnerContents: '.swiper-slide-inner'
				},
				slidesPerView: {
					desktop: 1,
					tablet: 1,
					mobile: 1
				},
				classes: {
					animated: 'animated'
				}
			};
		}
		
		getDefaultElements() {
			const selectors = this.getSettings('selectors');
			const elements = {
				$swiperContainer: this.$element.find(selectors.wrapper)
			};
			elements.$slides = elements.$swiperContainer.find(selectors.slide);
			return elements;
		}

		getInitialSlide() {
			const editSettings = this.getEditSettings();
			return editSettings.activeItemIndex ? editSettings.activeItemIndex - 1 : 0;
		}

		getSwiperOptions() {
			const elementSettings = this.getElementSettings();
			
			const swiperOptions = {
				slidesPerView: parseInt(elementSettings.slides_to_show) || 1,
				spaceBetween: 0,
				speed: elementSettings.animation_speed || 300,
				loop: elementSettings.loop === 'yes',
				autoplay: elementSettings.autoplay === 'yes' ? {
					delay: elementSettings.autoplay_speed || 3000,
					disableOnInteraction: false
				} : false,
				grabCursor: true,
				initialSlide: this.getInitialSlide(),
				observeParents: true,
				observer: true,
				handleElementorBreakpoints: true,
				noSwiping: true,
				noSwipingClass: 'swiper-no-swiping',
				noSwipingSelector: '.wpz-slide-lightbox-trigger, .wpz-background-video-buttons-wrapper, .wpz-slide-button, .wpz-slide-lightbox-wrapper, .wpz-slide-button-wrapper',
				on: {
					init: (swiper) => {
						this.requestAnimationFrame(() => {
							this.initVideoBackgrounds();
							this.fixSafariRendering();
							this.updateVideoControlsVisibility();
						});
					},
					slideChange: (swiper) => {
						this.requestAnimationFrame(() => {
							this.handleVideoBackgrounds();
							this.fixSafariRendering();
						});
					},
					resize: (swiper) => {
						this.throttledResizeHandler();
					}
				}
			};

			// Navigation
			const showArrows = elementSettings.navigation === 'arrow' || elementSettings.navigation === 'both';
			const showDots = elementSettings.navigation === 'dots' || elementSettings.navigation === 'both';

			if (showArrows) {
				swiperOptions.navigation = {
					nextEl: '.swiper-button-next',
					prevEl: '.swiper-button-prev'
				};
			}

			if (showDots) {
				swiperOptions.pagination = {
					el: '.swiper-pagination',
					clickable: true,
					type: 'bullets'
				};
			}

			// Responsive breakpoints
			if (elementSettings.slides_to_show_tablet || elementSettings.slides_to_show_mobile) {
				swiperOptions.breakpoints = {};
				
				if (elementSettings.slides_to_show_mobile) {
					swiperOptions.breakpoints[320] = {
						slidesPerView: parseInt(elementSettings.slides_to_show_mobile) || 1
					};
				}
				
				if (elementSettings.slides_to_show_tablet) {
					swiperOptions.breakpoints[768] = {
						slidesPerView: parseInt(elementSettings.slides_to_show_tablet) || 1
					};
				}
			}

			return swiperOptions;
		}

		async onInit() {
			super.onInit();

			// Handle single slide case - still need video initialization
			if (this.elements.$slides.length <= 1) {
				this.initVideoLightbox();
				this.setupResizeHandler();
				this.preventFitVidsConflicts();
				this.initVideoControls();

				// Initialize videos for single slide
				this.requestAnimationFrame(() => this.initVideoBackgroundsForSingleSlide());
				return;
			}

			await this.initSwiper();
			this.initVideoLightbox();
			this.preventSliderDragOnInteractiveElements();
			this.setupResizeHandler();
			this.preventFitVidsConflicts();
			this.initVideoControls();
			
			// Initialize videos after Swiper is ready
			this.requestAnimationFrame(() => this.initVideoBackgrounds());
		}

		async initSwiper() {
			const Swiper = elementorFrontend.utils.swiper;
			this.swiper = await new Swiper(this.elements.$swiperContainer, this.getSwiperOptions());
			
			// Expose swiper instance
			this.elements.$swiperContainer.data('swiper', this.swiper);
		}

		onEditSettingsChange(propertyName) {
			if (this.elements.$slides.length <= 1) {
				return;
			}
			
			if ('activeItemIndex' === propertyName) {
				const activeIndex = this.getEditSettings('activeItemIndex') - 1;
				if (this.swiper) {
					if (this.swiper.params.loop) {
						this.swiper.slideToLoop(activeIndex);
					} else {
						this.swiper.slideTo(activeIndex);
					}
					// Stop autoplay when manually navigating in editor
					if (this.swiper.autoplay) {
						this.swiper.autoplay.stop();
					}
				}
			}
		}

		onElementChange(propertyName) {
			if (this.elements.$slides.length <= 1) {
				return;
			}

			// Handle settings changes that require swiper re-initialization
			const reinitSettings = ['slides_to_show', 'slides_to_show_tablet', 'slides_to_show_mobile', 'navigation', 'loop'];
			
			if (reinitSettings.includes(propertyName)) {
				this.swiper.destroy(true, true);
				this.initSwiper();
				return;
			}

			// Handle settings that can be updated without re-initialization
			if (propertyName === 'autoplay') {
				if (this.getElementSettings('autoplay') === 'yes') {
					this.swiper.autoplay.start();
				} else {
					this.swiper.autoplay.stop();
				}
			}

			if (propertyName === 'autoplay_speed') {
				this.swiper.params.autoplay.delay = this.getElementSettings('autoplay_speed') || 3000;
				this.swiper.autoplay.start();
			}

			if (propertyName === 'animation_speed') {
				this.swiper.params.speed = this.getElementSettings('animation_speed') || 300;
			}
		}

		initVideoBackgrounds() {
			try {
				const jQuery = window.jQuery;

				// Cache video containers for better performance
				const videoContainers = this.elements.$swiperContainer.find('.wpz-video-bg');

				videoContainers.each((index, element) => {
					const videoContainer = jQuery(element);
					const videoType = videoContainer.data('video-type');

					if (!videoType) {
						return;
					}

					if (videoType === 'hosted') {
						this.handleHostedVideo(videoContainer);
					} else {
						this.handleIframeVideo(videoContainer);
					}
				});

				// Handle current slide specifically for better initialization
				if (this.swiper?.slides?.[this.swiper.activeIndex]) {
					this.requestAnimationFrame(() => this.handleCurrentSlideVideos());
				}

			} catch (error) {
				console.error('Error in initVideoBackgrounds:', error);
			}
		}

		initVideoBackgroundsForSingleSlide() {
			try {
				const jQuery = window.jQuery;

				// For single slide, directly find and initialize video backgrounds
				const videoContainers = this.elements.$swiperContainer.find('.wpz-video-bg');

				videoContainers.each((index, element) => {
					const videoContainer = jQuery(element);
					const videoType = videoContainer.data('video-type');

					if (!videoType) {
						return;
					}

					if (videoType === 'hosted') {
						this.handleHostedVideo(videoContainer);
					} else {
						this.handleIframeVideo(videoContainer);
					}
				});

			} catch (error) {
				console.error('Error in initVideoBackgroundsForSingleSlide:', error);
			}
		}

		handleVideoBackgrounds() {
			if (!this.swiper) return;
			
			try {
				this.handleCurrentSlideVideos();
				this.updateVideoControlsVisibility();
			} catch (error) {
				console.error('Error in handleVideoBackgrounds:', error);
			}
		}

		handleCurrentSlideVideos() {
			if (!this.swiper?.slides) return;
			
			const jQuery = window.jQuery;
			const currentSlide = jQuery(this.swiper.slides[this.swiper.activeIndex]);
			const videoContainer = currentSlide.find('.wpz-video-bg');
			
			if (videoContainer.length) {
				const videoType = videoContainer.data('video-type');
				
				if (videoType === 'hosted') {
					this.handleHostedVideo(videoContainer);
				} else {
					this.handleIframeVideo(videoContainer);
				}
			}
		}

		handleHostedVideo(container) {
			const video = container.find('video')[0];
			if (!video) {
				this.handleVideoError(container);
				return;
			}
			
			const isMobile = window.innerWidth <= 768;
			const playOnMobile = container.data('play-on-mobile');
			
			if (isMobile && !playOnMobile) {
				video.style.display = 'none';
				this.handleVideoError(container);
				return;
			}
			
			// Check if event handlers are already attached
			if (!this.videoEventHandlers.has(video)) {
				// Add error handling
				const errorHandler = () => {
					this.handleVideoError(container);
				};
				
				// Add load handler with dimension detection
				const loadHandler = () => {
					this.handleVideoSuccess(container);
					// Resize with actual video dimensions
					this.resizeVideo(container, video, video.videoWidth / video.videoHeight);
				};
				
				video.addEventListener('error', errorHandler);
				video.addEventListener('loadedmetadata', loadHandler);
				
				// Store handlers for cleanup
				this.videoEventHandlers.set(video, {
					error: errorHandler,
					loadedmetadata: loadHandler
				});
			}
			
			// Make sure video is visible (batched style changes)
			Object.assign(video.style, {
				display: 'block',
				visibility: 'visible',
				opacity: '1'
			});
			
			// If video metadata is already loaded, resize immediately
			if (video.readyState >= 1 && video.videoWidth && video.videoHeight) {
				this.resizeVideo(container, video, video.videoWidth / video.videoHeight);
			} else {
				// Fallback resize with default ratio while loading
				this.resizeVideo(container, video);
			}
			
			// Play video if paused
			if (video.paused) {
				video.play().catch((error) => {
					this.handleVideoError(container);
				});
			}
		}

		handleIframeVideo(container) {
			const iframe = container.find('iframe')[0];
			if (!iframe) {
				this.handleVideoError(container);
				return;
			}
			
			// Handle FitVids conflicts - remove any FitVids wrapper
			this.removeFitVidsWrapper(iframe);
			
			// Check if event handlers are already attached
			if (!this.videoEventHandlers.has(iframe)) {
				// Add error handling for iframe
				const errorHandler = () => {
					this.handleVideoError(container);
				};
				
				// Add load handler
				const loadHandler = () => {
					this.handleVideoSuccess(container);
					// Remove FitVids wrapper after load as well
					this.removeFitVidsWrapper(iframe);
				};
				
				iframe.addEventListener('error', errorHandler);
				iframe.addEventListener('load', loadHandler);
				
				// Store handlers for cleanup
				this.videoEventHandlers.set(iframe, {
					error: errorHandler,
					load: loadHandler
				});
			}
			
			// Make sure iframe is visible (batched style changes)
			Object.assign(iframe.style, {
				display: 'block',
				visibility: 'visible',
				opacity: '1'
			});
			
			// Ensure iframe covers the entire background (use default 16:9 for iframe videos)
			this.resizeVideo(container, iframe, 16 / 9);
			
			// Handle iframe load event for better sizing
			if (!iframe.dataset.loaded) {
				iframe.addEventListener('load', () => {
					iframe.dataset.loaded = 'true';
					this.removeFitVidsWrapper(iframe);
					this.requestAnimationFrame(() => {
						this.resizeVideo(container, iframe, 16 / 9);
					});
				});
			}
		}

		resizeVideo(container, video, videoRatio = null) {
			if (!container || !video) return;
			
			try {
				// Use cached dimensions if available and recent
				const cacheKey = video;
				const now = Date.now();
				let containerDimensions = this.dimensionCache.get(cacheKey);
				
				if (!containerDimensions || (now - containerDimensions.timestamp) > 1000) {
					const containerElement = container[0] || container;
					const containerRect = containerElement.getBoundingClientRect();
					containerDimensions = {
						width: containerRect.width,
						height: containerRect.height,
						timestamp: now
					};
					this.dimensionCache.set(cacheKey, containerDimensions);
				}
				
				const { width: containerWidth, height: containerHeight } = containerDimensions;
				
				if (containerWidth === 0 || containerHeight === 0) {
					// Limit retry attempts to prevent infinite recursion
					if (!video.dataset.retryCount) {
						video.dataset.retryCount = '0';
					}
					const retryCount = parseInt(video.dataset.retryCount);
					if (retryCount < 3) {
						video.dataset.retryCount = (retryCount + 1).toString();
						setTimeout(() => {
							this.resizeVideo(container, video, videoRatio);
						}, 100);
					}
					return;
				}

				// Reset retry count on successful resize
				video.dataset.retryCount = '0';

				// Use provided video ratio or detect from video element or default to 16:9
				let actualVideoRatio = videoRatio;
				
				if (!actualVideoRatio && video.tagName === 'VIDEO' && video.videoWidth && video.videoHeight) {
					actualVideoRatio = video.videoWidth / video.videoHeight;
				}
				
				if (!actualVideoRatio) {
					actualVideoRatio = 16 / 9; // Default fallback
				}
				
				const containerRatio = containerWidth / containerHeight;
				
				let videoWidth, videoHeight;
				
				// Calculate dimensions to ensure full coverage
				if (containerRatio > actualVideoRatio) {
					// Container is wider than video ratio - fit to container width
					videoWidth = containerWidth;
					videoHeight = containerWidth / actualVideoRatio;
				} else {
					// Container is taller than video ratio - fit to container height
					videoHeight = containerHeight;
					videoWidth = containerHeight * actualVideoRatio;
				}
				
				// Use the more robust transform approach that was working before (batched style changes)
				Object.assign(video.style, {
					position: 'absolute',
					top: '50%',
					left: '50%',
					transform: 'translate(-50%, -50%)',
					width: videoWidth + 'px',
					height: videoHeight + 'px',
					minWidth: containerWidth + 'px',
					minHeight: containerHeight + 'px',
					maxWidth: 'none',
					maxHeight: 'none',
					objectFit: 'cover',
					zIndex: '1',
					pointerEvents: 'none'
				});
				
			} catch (error) {
				console.error('Error resizing video:', error);
			}
		}

		initVideoLightbox() {
			const jQuery = window.jQuery;
			
			// Remove any existing global lightbox handlers
			jQuery(document).off('click.wpzLightbox');
			
			// Use event delegation for robust handling
			jQuery(document).on('click.wpzLightbox', '.wpz-slide-lightbox-trigger', (e) => {
				e.preventDefault();
				e.stopPropagation();
				
				const trigger = jQuery(e.target).closest('.wpz-slide-lightbox-trigger');
				const videoUrl = trigger.attr('href');
				
				if (!videoUrl) return;
				
				this.openVideoLightbox(videoUrl);
			});
		}

		openVideoLightbox(videoUrl) {
			const jQuery = window.jQuery;
			
			// Remove existing lightbox
			jQuery('.wpz-lightbox').remove();
			
			// Create lightbox with correct structure
			const lightbox = jQuery(`
				<div class="wpz-lightbox">
					<div class="wpz-lightbox-overlay"></div>
					<button class="wpz-lightbox-close" aria-label="Close video">
						<svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					<div class="wpz-lightbox-container">
						<div class="wpz-lightbox-content">
							${this.getVideoEmbedHtml(videoUrl)}
						</div>
					</div>
				</div>
			`);
			
			// Add to page
			jQuery('body').append(lightbox);
			
			// Show lightbox with correct class name
			this.requestAnimationFrame(() => {
				lightbox.addClass('active');
			});
			
			// Close handlers
			lightbox.find('.wpz-lightbox-close, .wpz-lightbox-overlay').on('click', () => {
				this.closeLightbox(lightbox);
			});
			
			// ESC key handler
			jQuery(document).on('keydown.wpzLightbox', (e) => {
				if (e.keyCode === 27) {
					this.closeLightbox(lightbox);
				}
			});
		}

		closeLightbox(lightbox) {
			const jQuery = window.jQuery;
			
			lightbox.removeClass('active');
			jQuery(document).off('keydown.wpzLightbox');
			
			setTimeout(() => {
				lightbox.remove();
			}, 300);
		}

		getVideoEmbedHtml(url) {
			// YouTube
			if (url.includes('youtube.com') || url.includes('youtu.be')) {
				const videoId = this.getYouTubeVideoId(url);
				if (videoId) {
					return `<iframe class="wpz-lightbox-video" src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0" frameborder="0" allowfullscreen allow="autoplay"></iframe>`;
				}
			}
			
			// Vimeo
			if (url.includes('vimeo.com')) {
				const videoId = this.getVimeoVideoId(url);
				if (videoId) {
					const hash = this.getVimeoPrivacyHash(url);
					return `<iframe class="wpz-lightbox-video" src="https://player.vimeo.com/video/${videoId}?autoplay=1${hash ? '&h=' + hash : ''}" frameborder="0" allowfullscreen allow="autoplay"></iframe>`;
				}
			}
			
			// Check if URL is a direct video file
			if (this.isDirectVideoFile(url)) {
				return `<video class="wpz-lightbox-video" controls autoplay loop>
					<source src="${url}" type="${this.getVideoMimeType(url)}">
					<p>Your browser doesn't support HTML5 video. <a href="${url}" target="_blank">Download the video</a> instead.</p>
				</video>`;
			}

			// Fallback for other embeddable content
			return `<iframe class="wpz-lightbox-video" src="${url}" frameborder="0" allowfullscreen></iframe>`;
		}

		getYouTubeVideoId(url) {
			const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
			const match = url.match(regExp);
			return (match && match[2].length === 11) ? match[2] : null;
		}

		getVimeoVideoId(url) {
			// Handles vimeo.com/ID, vimeo.com/ID/HASH, player.vimeo.com/video/ID and channel/group URLs.
			const match = url.match(/vimeo\.com\/(?:.*?\/)?(\d+)/);
			return match ? match[1] : null;
		}

		// Privacy hash of unlisted/private videos: vimeo.com/ID/HASH or player.vimeo.com/video/ID?h=HASH.
		getVimeoPrivacyHash(url) {
			const match = url.match(/vimeo\.com\/(?:.*?\/)?\d+\/([a-z0-9]+)/i) || url.match(/[?&]h=([a-z0-9]+)/i);
			return match ? match[1] : '';
		}

		isDirectVideoFile(url) {
			const videoExtensions = ['.mp4', '.webm', '.ogg', '.mov', '.avi', '.wmv', '.flv', '.mkv', '.m4v'];
			const urlLower = url.toLowerCase();

			// Remove query parameters and fragments for checking
			const cleanUrl = urlLower.split('?')[0].split('#')[0];

			return videoExtensions.some(ext => cleanUrl.endsWith(ext));
		}

		getVideoMimeType(url) {
			const urlLower = url.toLowerCase();
			const cleanUrl = urlLower.split('?')[0].split('#')[0];

			if (cleanUrl.endsWith('.mp4') || cleanUrl.endsWith('.m4v')) {
				return 'video/mp4';
			} else if (cleanUrl.endsWith('.webm')) {
				return 'video/webm';
			} else if (cleanUrl.endsWith('.ogg')) {
				return 'video/ogg';
			} else if (cleanUrl.endsWith('.mov')) {
				return 'video/quicktime';
			} else if (cleanUrl.endsWith('.avi')) {
				return 'video/x-msvideo';
			} else if (cleanUrl.endsWith('.wmv')) {
				return 'video/x-ms-wmv';
			} else if (cleanUrl.endsWith('.flv')) {
				return 'video/x-flv';
			} else if (cleanUrl.endsWith('.mkv')) {
				return 'video/x-matroska';
			}

			// Default fallback
			return 'video/mp4';
		}

		handleVideoError(container) {
			const jQuery = window.jQuery;
			const slideItem = container.closest('.wpz-slide-item');
			
			// Add error class to show fallback image
			slideItem.addClass('video-failed');
		}

		handleVideoSuccess(container) {
			const jQuery = window.jQuery;
			const slideItem = container.closest('.wpz-slide-item');
			
			// Remove error class to hide fallback image
			slideItem.removeClass('video-failed');
		}

		preventSliderDragOnInteractiveElements() {
			const jQuery = window.jQuery;
			
			// Use single event delegation instead of multiple listeners per element
			const interactiveSelector = '.wpz-slide-lightbox-trigger, .wpz-background-video-buttons-wrapper, .wpz-slide-button, .wpz-slide-lightbox-wrapper, .wpz-slide-button-wrapper';
			
			// Prevent all interaction events with single delegation
			this.elements.$swiperContainer.on('touchstart.preventDrag mousedown.preventDrag', interactiveSelector, (e) => {
				e.stopPropagation();
				e.stopImmediatePropagation();
				
				// Mark the slide as non-swipeable
				const slide = jQuery(e.target).closest('.swiper-slide');
				slide.addClass('swiper-no-swiping');
				
				// Remove class after a delay
				setTimeout(() => {
					slide.removeClass('swiper-no-swiping');
				}, 500);
			});
			
			this.elements.$swiperContainer.on('touchmove.preventDrag touchend.preventDrag mousemove.preventDrag mouseup.preventDrag', interactiveSelector, (e) => {
				e.stopPropagation();
				e.stopImmediatePropagation();
			});
			
			// Configure Swiper no-swiping
			if (this.swiper?.params) {
				Object.assign(this.swiper.params, {
					noSwiping: true,
					noSwipingClass: 'swiper-no-swiping',
					noSwipingSelector: interactiveSelector
				});
				this.swiper.update();
			}
		}

		preventFitVidsConflicts() {
			const jQuery = window.jQuery;
			
			// Add fitvidsignore class to the entire widget container
			this.elements.$swiperContainer.addClass('fitvidsignore');
			
			// Add fitvidsignore to all video containers and iframes
			this.elements.$swiperContainer.find('.wpz-video-bg').addClass('fitvidsignore');
			this.elements.$swiperContainer.find('.wpz-video-bg iframe').addClass('fitvidsignore');
			
			// If FitVids is already present and has processed our videos, clean them up
			const existingWrappers = this.elements.$swiperContainer.find('.fluid-width-video-wrapper');
			existingWrappers.each((index, wrapper) => {
				const $wrapper = jQuery(wrapper);
				const $iframe = $wrapper.find('iframe');
				
				if ($iframe.length && $wrapper.parent().hasClass('wpz-video-bg')) {
					// Move iframe out of wrapper
					$iframe.detach();
					$wrapper.remove();
					$wrapper.parent().append($iframe);
					$iframe.addClass('fitvidsignore');
				}
			});
		}

		setupResizeHandler() {
			// Only create handler if it doesn't exist and widget is still active
			if (!this.throttledResizeHandler && this.elements.$swiperContainer) {
				this.throttledResizeHandler = this.throttle(() => {
					// Check if widget is still active before processing
					if (this.elements && this.elements.$swiperContainer && this.elements.$swiperContainer.length) {
						this.resizeAllVideos();
					}
				}, 250);
				
				window.addEventListener('resize', this.throttledResizeHandler);
			}
		}

		resizeAllVideos() {
			// Safety check - ensure widget is still active
			if (!this.elements || !this.elements.$swiperContainer || !this.elements.$swiperContainer.length) {
				return;
			}
			
			// Clear dimension cache on resize
			if (this.dimensionCache && typeof this.dimensionCache.clear === 'function') {
				this.dimensionCache.clear();
			} else if (this.dimensionCache) {
				this.dimensionCache = new Map();
			}
			
			const jQuery = window.jQuery;
			const videoContainers = this.elements.$swiperContainer.find('.wpz-video-bg');
			
			// Batch process all videos
			videoContainers.each((index, element) => {
				const videoContainer = jQuery(element);
				const video = videoContainer.find('video')[0] || videoContainer.find('iframe')[0];
				
				if (!video) return;
				
				const videoType = videoContainer.data('video-type');
				
				if (videoType === 'hosted' && video.tagName === 'VIDEO' && video.videoWidth && video.videoHeight) {
					// Use actual video dimensions for hosted videos
					const videoRatio = video.videoWidth / video.videoHeight;
					this.resizeVideo(videoContainer, video, videoRatio);
				} else {
					// Use default 16:9 for iframe videos
					this.resizeVideo(videoContainer, video, 16 / 9);
				}
			});
		}

		// Utility functions for performance optimization
		requestAnimationFrame(callback) {
			if (window.requestAnimationFrame) {
				window.requestAnimationFrame(callback);
			} else {
				setTimeout(callback, 16);
			}
		}

		removeFitVidsWrapper(iframe) {
			const jQuery = window.jQuery;
			const $iframe = jQuery(iframe);
			
			// Check if iframe is wrapped by FitVids
			const $fitVidsWrapper = $iframe.closest('.fluid-width-video-wrapper');
			
			if ($fitVidsWrapper.length && $fitVidsWrapper.parent().hasClass('wpz-video-bg')) {
				// Move iframe out of FitVids wrapper and back to our video container
				const $videoContainer = $fitVidsWrapper.parent();
				$iframe.detach();
				$fitVidsWrapper.remove();
				$videoContainer.append($iframe);
				
				// Ensure iframe has the fitvidsignore class
				if (!$iframe.hasClass('fitvidsignore')) {
					$iframe.addClass('fitvidsignore');
				}
			}
		}

		throttle(func, limit) {
			let inThrottle;
			return function() {
				const args = arguments;
				const context = this;
				if (!inThrottle) {
					func.apply(context, args);
					inThrottle = true;
					setTimeout(() => inThrottle = false, limit);
				}
			};
		}

		initVideoControls() {
			const elementSettings = this.getElementSettings();

			// Only initialize if video controls are enabled
			if (elementSettings.show_video_controls !== 'yes') {
				return;
			}

			const $ = jQuery;
			const $sliderElement = this.$element;

			// Handle video control buttons
			$sliderElement.find('.wpz-button-video-background-play').on('click', function(e) {
				e.preventDefault();
				const $currentSlide = $(e.currentTarget).closest('.swiper-slide');
				const $videoContainer = $currentSlide.find('.wpz-video-bg');
				const videoType = $videoContainer.data('video-type');

				if (videoType === 'hosted') {
					// Handle self-hosted videos
					const $video = $videoContainer.find('video');
					if ($video.length) {
						$video[0].play().catch(error => console.log('Video play failed:', error));
					}
				} else if (videoType === 'youtube') {
					// Handle YouTube videos via postMessage API
					const $iframe = $videoContainer.find('iframe');
					if ($iframe.length) {
						const origin = $iframe.attr('src').includes('youtube-nocookie.com') ? 'https://www.youtube-nocookie.com' : 'https://www.youtube.com';
						$iframe[0].contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', origin);
						$currentSlide.attr('data-video-state', 'playing');
					}
				} else if (videoType === 'vimeo') {
					// Handle Vimeo videos via postMessage API
					const $iframe = $videoContainer.find('iframe');
					if ($iframe.length) {
						$iframe[0].contentWindow.postMessage('{"method":"play"}', 'https://player.vimeo.com');
						$currentSlide.attr('data-video-state', 'playing');
					}
				}

				$currentSlide.find('.wpz-button-video-background-pause').removeClass('display-none');
				$(this).addClass('display-none');
			});

			$sliderElement.find('.wpz-button-video-background-pause').on('click', function(e) {
				e.preventDefault();
				const $currentSlide = $(e.currentTarget).closest('.swiper-slide');
				const $videoContainer = $currentSlide.find('.wpz-video-bg');
				const videoType = $videoContainer.data('video-type');

				if (videoType === 'hosted') {
					// Handle self-hosted videos
					const $video = $videoContainer.find('video');
					if ($video.length) {
						$video[0].pause();
					}
				} else if (videoType === 'youtube') {
					// Handle YouTube videos via postMessage API
					const $iframe = $videoContainer.find('iframe');
					if ($iframe.length) {
						const origin = $iframe.attr('src').includes('youtube-nocookie.com') ? 'https://www.youtube-nocookie.com' : 'https://www.youtube.com';
						$iframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', origin);
						$currentSlide.attr('data-video-state', 'paused');
					}
				} else if (videoType === 'vimeo') {
					// Handle Vimeo videos via postMessage API
					const $iframe = $videoContainer.find('iframe');
					if ($iframe.length) {
						$iframe[0].contentWindow.postMessage('{"method":"pause"}', 'https://player.vimeo.com');
						$currentSlide.attr('data-video-state', 'paused');
					}
				}

				$currentSlide.find('.wpz-button-video-background-play').removeClass('display-none');
				$(this).addClass('display-none');
			});

			$sliderElement.find('.wpz-button-sound-background-mute').on('click', function(e) {
				e.preventDefault();
				const $currentSlide = $(e.currentTarget).closest('.swiper-slide');
				const $videoContainer = $currentSlide.find('.wpz-video-bg');
				const videoType = $videoContainer.data('video-type');

				if (videoType === 'hosted') {
					// Handle self-hosted videos
					const $video = $videoContainer.find('video');
					if ($video.length) {
						$video[0].muted = true;
					}
				} else if (videoType === 'youtube') {
					// Handle YouTube videos via postMessage API
					const $iframe = $videoContainer.find('iframe');
					if ($iframe.length) {
						const origin = $iframe.attr('src').includes('youtube-nocookie.com') ? 'https://www.youtube-nocookie.com' : 'https://www.youtube.com';
						$iframe[0].contentWindow.postMessage('{"event":"command","func":"mute","args":""}', origin);
						$currentSlide.attr('data-video-muted', 'true');
					}
				} else if (videoType === 'vimeo') {
					// Handle Vimeo videos via postMessage API
					const $iframe = $videoContainer.find('iframe');
					if ($iframe.length) {
						$iframe[0].contentWindow.postMessage('{"method":"setVolume","value":0}', 'https://player.vimeo.com');
						$currentSlide.attr('data-video-muted', 'true');
					}
				}

				$currentSlide.find('.wpz-button-sound-background-unmute').removeClass('display-none');
				$(this).addClass('display-none');
			});

			$sliderElement.find('.wpz-button-sound-background-unmute').on('click', function(e) {
				e.preventDefault();
				const $currentSlide = $(e.currentTarget).closest('.swiper-slide');
				const $videoContainer = $currentSlide.find('.wpz-video-bg');
				const videoType = $videoContainer.data('video-type');

				if (videoType === 'hosted') {
					// Handle self-hosted videos
					const $video = $videoContainer.find('video');
					if ($video.length) {
						$video[0].muted = false;
					}
				} else if (videoType === 'youtube') {
					// Handle YouTube videos via postMessage API
					const $iframe = $videoContainer.find('iframe');
					if ($iframe.length) {
						const origin = $iframe.attr('src').includes('youtube-nocookie.com') ? 'https://www.youtube-nocookie.com' : 'https://www.youtube.com';
						$iframe[0].contentWindow.postMessage('{"event":"command","func":"unMute","args":""}', origin);
						$currentSlide.attr('data-video-muted', 'false');
					}
				} else if (videoType === 'vimeo') {
					// Handle Vimeo videos via postMessage API
					const $iframe = $videoContainer.find('iframe');
					if ($iframe.length) {
						$iframe[0].contentWindow.postMessage('{"method":"setVolume","value":1}', 'https://player.vimeo.com');
						$currentSlide.attr('data-video-muted', 'false');
					}
				}

				$currentSlide.find('.wpz-button-sound-background-mute').removeClass('display-none');
				$(this).addClass('display-none');
			});

			// Initialize button visibility for current slide
			this.updateVideoControlsVisibility();
		}

		updateVideoControlsVisibility() {
			if (!this.swiper) return;

			const $ = jQuery;
			const currentSlide = $(this.swiper.slides[this.swiper.activeIndex]);
			const $videoContainer = currentSlide.find('.wpz-video-bg');

			if ($videoContainer.length) {
				const $buttonsWrapper = currentSlide.find('.wpz-background-video-buttons-wrapper');

				if ($buttonsWrapper.length) {
					const videoType = $videoContainer.data('video-type');

					if (videoType === 'hosted') {
						// For self-hosted videos, check actual video state
						const $video = $videoContainer.find('video');
						if ($video.length) {
							const isPlaying = !$video[0].paused;
							const isMuted = $video[0].muted;

							currentSlide.find('.wpz-button-video-background-play').toggleClass('display-none', isPlaying);
							currentSlide.find('.wpz-button-video-background-pause').toggleClass('display-none', !isPlaying);
							currentSlide.find('.wpz-button-sound-background-mute').toggleClass('display-none', isMuted);
							currentSlide.find('.wpz-button-sound-background-unmute').toggleClass('display-none', !isMuted);
						}
					} else if (videoType === 'youtube' || videoType === 'vimeo') {
						// For external videos, use tracked state or defaults
						const videoState = currentSlide.attr('data-video-state') || 'playing';
						const videoMuted = currentSlide.attr('data-video-muted') || 'true';

						const isPlaying = videoState === 'playing';
						const isMuted = videoMuted === 'true';

						currentSlide.find('.wpz-button-video-background-play').toggleClass('display-none', isPlaying);
						currentSlide.find('.wpz-button-video-background-pause').toggleClass('display-none', !isPlaying);
						currentSlide.find('.wpz-button-sound-background-mute').toggleClass('display-none', isMuted);
						currentSlide.find('.wpz-button-sound-background-unmute').toggleClass('display-none', !isMuted);
					}
				}
			}
		}

		onDestroy() {
			// Clean up resize handler first
			if (this.throttledResizeHandler) {
				window.removeEventListener('resize', this.throttledResizeHandler);
				this.throttledResizeHandler = null;
			}
			
			// Clean up swiper instance
			if (this.swiper) {
				try {
					this.swiper.destroy(true, true);
				} catch (error) {
					// Silent cleanup
				}
				this.swiper = null;
			}
			
			// Clean up video event handlers - WeakMap doesn't need manual cleanup
			// Just remove references so garbage collection can work
			if (this.videoEventHandlers) {
				this.videoEventHandlers = new WeakMap();
			}
			
			// Clear dimension cache manually
			if (this.dimensionCache && typeof this.dimensionCache.clear === 'function') {
				this.dimensionCache.clear();
			} else if (this.dimensionCache) {
				this.dimensionCache = new Map();
			}
			
			// Clean up event delegation
			if (this.elements && this.elements.$swiperContainer) {
				this.elements.$swiperContainer.off('.preventDrag');
			}
			
			// Clean up lightbox handlers
			const jQuery = window.jQuery;
			if (jQuery) {
				jQuery(document).off('click.wpzLightbox');
				jQuery(document).off('keydown.wpzLightbox');
			}
			
			// Clear all references
			this.elements = null;
			
			super.onDestroy();
		}

		fixSafariRendering() {
			// Safari-specific fixes for z-index and rendering issues
			const jQuery = window.jQuery;
			const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
			
			if (!isSafari) {
				return;
			}
			
			// Force Safari to recalculate z-index stacking contexts
			const slides = this.elements.$swiperContainer.find('.swiper-slide');
			
			slides.each((index, slide) => {
				const $slide = jQuery(slide);
				const $content = $slide.find('.wpz-slide-inner');
				const $overlay = $slide.find('.wpz-slide-item::before');
				
				// Force repaint by temporarily changing and restoring transform
				if ($content.length) {
					const currentTransform = $content.css('transform');
					$content.css('transform', 'translateZ(0.1px)');
					
					// Use requestAnimationFrame to ensure the change is applied
					this.requestAnimationFrame(() => {
						$content.css('transform', currentTransform || 'translateZ(0)');
					});
				}
				
				// Ensure content visibility
				$slide.find('.wpz-slide-content, .wpz-slide-actions, .wpz-slide-button, .wpz-slide-lightbox-trigger').each((i, element) => {
					const $element = jQuery(element);
					if ($element.css('visibility') === 'hidden' || $element.css('opacity') === '0') {
						$element.css({
							'visibility': 'visible',
							'opacity': '1',
							'z-index': parseInt($element.css('z-index')) || 100
						});
					}
				});
			});
		}
	}

	// Register the handler
	elementorFrontend.hooks.addAction('frontend/element_ready/wpzoom-elementor-addons-video-slider.default', function($scope) {
		new VideoSliderHandler({ $element: $scope });
	});
});
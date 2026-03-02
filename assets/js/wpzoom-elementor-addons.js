const windowWPZ = window.wpzoom = window.wpzoom || {};
var WPZCached = null; // legacy cache (templates)
var WPZCachedTemplates = null;
var WPZCachedSections = null;
var WPZCachedWireframes = null;

(function( $ ) {

	const elementor_add_section_tmpl = $( "#tmpl-elementor-add-section" );

	if (0 < elementor_add_section_tmpl.length && typeof elementor !== undefined) {
		let text = elementor_add_section_tmpl.text();

		//Add the WPZOOM Button
		(text = text.replace(
			'<div class="elementor-add-section-drag-title',
			'<div class="elementor-add-section-area-button elementor-add-wpzoom-templates-button" title="WPZOOM Library"> <i class="eicon-folder"></i> </div> <div class="elementor-add-section-drag-title'
		)),

		elementor_add_section_tmpl.text(text),
		elementor.on( "preview:loaded", function() {
			$( elementor.$previewContents[0].body).on(
				"click",
				".elementor-add-wpzoom-templates-button",
				openLibrary
			);
		});

		/* Load section categories from server and populate dropdown */
		function wpzoom_load_section_categories() {
			$.post(ajaxurl, { action: 'get_sections_filter_options' }, function (data) {
				try {
					var categories = JSON.parse(data);
					var $categorySelect = $('#wpzoom-elementor-template-library-filter-category');

					// Clear existing options except the first placeholder
					$categorySelect.find('option:not(:first)').remove();

					// Add categories to dropdown
					if (categories && categories.length > 0) {
						categories.forEach(function (category) {
							var categoryLabel = category.replace(/-/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
							$categorySelect.append('<option value="' + category + '">' + categoryLabel + '</option>');
						});
					}

					// Refresh select2 to show new options
					if ($categorySelect.hasClass('select2-hidden-accessible')) {
						$categorySelect.select2('destroy').select2({
							placeholder: 'Category',
							allowClear: true,
							width: 180
						});
						wpzoom_update_filter_visibility();
					}
				} catch (e) {
					console.error('Error loading section categories:', e);
				}
			});
		}

		/* Load wireframe categories from server and populate dropdown */
		function wpzoom_load_wireframe_categories() {
			$.post(ajaxurl, { action: 'get_wireframes_filter_options' }, function (data) {
				try {
					var categories = JSON.parse(data);
					var $categorySelect = $('#wpzoom-elementor-template-library-filter-wireframe-category');

					$categorySelect.find('option:not(:first)').remove();

					if (categories && categories.length > 0) {
						categories.forEach(function (category) {
							var categoryLabel = category.replace(/-/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
							$categorySelect.append('<option value="' + category + '">' + categoryLabel + '</option>');
						});
					}

					if ($categorySelect.hasClass('select2-hidden-accessible')) {
						$categorySelect.select2('destroy').select2({
							placeholder: 'Category',
							allowClear: true,
							width: 180
						});
						wpzoom_update_filter_visibility();
					}
				} catch (e) {
					console.error('Error loading wireframe categories:', e);
				}
			});
		}

		/* Centralized function to update filter dropdown visibility based on current tab */
		function wpzoom_update_filter_visibility() {
			var currentTab = windowWPZ.currentTab || 'templates';

			if (currentTab === 'sections') {
				// Sections tab: show category, hide theme and wireframe category
				$('#wpzoom-elementor-template-library-filter-theme').hide();
				$('#wpzoom-elementor-template-library-filter-theme').next('.select2-container').hide();
				$('#wpzoom-elementor-template-library-filter-wireframe-category').hide();
				$('#wpzoom-elementor-template-library-filter-wireframe-category').next('.select2-container').hide();
				$('#wpzoom-elementor-template-library-filter-category').show();
				$('#wpzoom-elementor-template-library-filter-category').next('.select2-container').show();
			} else if (currentTab === 'wireframes') {
				// Wireframes tab: show wireframe category, hide theme and sections category
				$('#wpzoom-elementor-template-library-filter-theme').hide();
				$('#wpzoom-elementor-template-library-filter-theme').next('.select2-container').hide();
				$('#wpzoom-elementor-template-library-filter-category').hide();
				$('#wpzoom-elementor-template-library-filter-category').next('.select2-container').hide();
				$('#wpzoom-elementor-template-library-filter-wireframe-category').show();
				$('#wpzoom-elementor-template-library-filter-wireframe-category').next('.select2-container').show();
			} else {
				// Templates tab: show theme, hide category and wireframe category
				$('#wpzoom-elementor-template-library-filter-category').hide();
				$('#wpzoom-elementor-template-library-filter-category').next('.select2-container').hide();
				$('#wpzoom-elementor-template-library-filter-wireframe-category').hide();
				$('#wpzoom-elementor-template-library-filter-wireframe-category').next('.select2-container').hide();
				$('#wpzoom-elementor-template-library-filter-theme').show();
				$('#wpzoom-elementor-template-library-filter-theme').next('.select2-container').show();
			}
		}

		//Show the loading panel
		function showLoadingView() {
			$('.dialog-lightbox-loading').show();
			$('.dialog-lightbox-content').hide();
		}
		
		//Hide the loading panel
		function hideLoadingView() {
			$('.dialog-lightbox-content').show();
			$('.dialog-lightbox-loading').hide();
		}

		//Create and Open the Lightbox with Templates
		function openLibrary() {
			const insertIndex = 0 < jQuery(this).parents(".elementor-section-wrap").length ? jQuery(this).parents(".elementor-add-section").index() : -1;
			windowWPZ.insertIndex = insertIndex;

			elementorCommon &&
				(windowWPZ.wpzModal ||
				((windowWPZ.wpzModal = elementorCommon.dialogsManager.createWidget(
					"lightbox",
					{
						id: "wpzoom-elementor-template-library-modal",
						className: "elementor-templates-modal",
						message: "",
						hide: {
							auto: !1,
							onClick: !1,
							onOutsideClick: !1,
							onOutsideContextMenu: !1,
							onBackgroundClick: !0
						},
						position: {
							my: "center",
							at: "center"
						},
						onShow: function () {

							const header = windowWPZ.wpzModal.getElements("header");
							if (!$('.elementor-templates-modal__header').length) {
								header.append(wp.template('wpzoom-elementor-templates-modal__header'));
							}
							const content = windowWPZ.wpzModal.getElements("content");
							if (!$('#elementor-template-library-filter-toolbar-remote').length) {
								content.append(wp.template('wpzoom-elementor-template-library-tools'));
							}
							// Reset active tab UI to Templates on each open
							$('#wpzoom-elementor-template-library-tabs-wrapper .elementor-template-library-menu-item').removeClass('elementor-active').attr('aria-selected', 'false');
							$('#wpzoom-elementor-template-library-tabs-wrapper .elementor-template-library-menu-item[data-tab="templates"]').addClass('elementor-active').attr('aria-selected', 'true');
							// Reset tab state
							windowWPZ.currentTab = 'templates';
							// Ensure filters show Pages theme by default
							$('#wpzoom-elementor-template-library-filter-theme').show();
							$('#wpzoom-elementor-template-library-filter-category').hide();
							$('#wpzoom-elementor-template-library-filter-wireframe-category').hide();
							// Also clear any previous filter values (trigger change so Select2 UI updates)
							$('#wpzoom-elementor-template-library-filter-theme').val(null).trigger('change');
							$('#wpzoom-elementor-template-library-filter-category').val(null).trigger('change');
							$('#wpzoom-elementor-template-library-filter-wireframe-category').val(null).trigger('change');
							$('#wpzoom-elementor-template-library-filter-text').val('');
							$('.wpzoom-search-clear').hide();

							// Immediately hide category select2 containers if they already exist
							// This handles the case when modal is opened for the 2nd+ time
							var $categoryContainer = $('#wpzoom-elementor-template-library-filter-category').next('.select2-container');
							if ($categoryContainer.length) {
								$categoryContainer.hide();
							}
							var $wireframeCategoryContainer = $('#wpzoom-elementor-template-library-filter-wireframe-category').next('.select2-container');
							if ($wireframeCategoryContainer.length) {
								$wireframeCategoryContainer.hide();
							}
							if (!$('#wpzoom-elementor-templates-header').length) {
								content.append('<div id="wpzoom-elementor-templates-header" class="wrap"></div>');
							}
							if (!$('#wpzoom_main_library_templates_panel').length) {
								content.append('<div id="wpzoom_main_library_templates_panel" class="wpzoom__main-view"></div>');
							}
							if ('dark' !== elementor.settings.editorPreferences.model.get('ui_theme')) {
								$("#wpzoom_main_library_templates_panel").removeClass('wpzoom-dark-mode');
							}
							else {
								$("#wpzoom_main_library_templates_panel").addClass('wpzoom-dark-mode');
							}
							const loading = windowWPZ.wpzModal.getElements("loading");
							if (!$('#elementor-template-library-loading').length) {
								loading.append(wp.template('wpzoom-elementor-template-library-loading'));
							}

							var event = new Event("modal-close");
							$("#wpzoom-elementor-templates").on(
								"click",
								".close-modal",
							function () {
								document.dispatchEvent(event);
								return windowWPZ.wpzModal.hide(), !1;
							}
						);
							$(".elementor-templates-modal__header__close").click(function () {
								return windowWPZ.wpzModal.hide();
							});
							// Bind tab switching
							$('#wpzoom-elementor-template-library-tabs-wrapper').off('click keypress', '.elementor-template-library-menu-item')
								.on('click keypress', '.elementor-template-library-menu-item', function (e) {
									if (e.type === 'keypress' && e.key !== 'Enter' && e.key !== ' ') { return; }
									var $btn = $(this);
									if ($btn.hasClass('elementor-active')) { return; }

								// Update UI
									$('#wpzoom-elementor-template-library-tabs-wrapper .elementor-template-library-menu-item').removeClass('elementor-active').attr('aria-selected', 'false');
								$btn.addClass('elementor-active').attr('aria-selected', 'true');

								// Update current tab
								var tabData = $btn.data('tab');
								windowWPZ.currentTab = tabData === 'sections' ? 'sections' : (tabData === 'wireframes' ? 'wireframes' : 'templates');

								// Clear filters not belonging to the active tab
								if (windowWPZ.currentTab === 'sections') {
									$('#wpzoom-elementor-template-library-filter-theme').val(null).trigger('change');
									$('#wpzoom-elementor-template-library-filter-wireframe-category').val(null).trigger('change');
								} else if (windowWPZ.currentTab === 'wireframes') {
									$('#wpzoom-elementor-template-library-filter-theme').val(null).trigger('change');
									$('#wpzoom-elementor-template-library-filter-category').val(null).trigger('change');
								} else {
									$('#wpzoom-elementor-template-library-filter-category').val(null).trigger('change');
									$('#wpzoom-elementor-template-library-filter-wireframe-category').val(null).trigger('change');
								}

								// Reset search on tab switch
								$('#wpzoom-elementor-template-library-filter-text').val('');
								$('.wpzoom-search-clear').hide();
								// Update filter visibility using centralized function
								wpzoom_update_filter_visibility();
								// Update no-media toggle state for the new tab
								wpzoom_update_media_toggle_for_tab(windowWPZ.currentTab);

								// Load content for new tab
								wpzoom_get_library_view(windowWPZ.currentTab);
							});

							// Initialize select2 only once per select
							if (!$('#wpzoom-elementor-template-library-filter-theme').hasClass('select2-hidden-accessible')) {
								$('#wpzoom-elementor-template-library-filter-theme').select2({
									placeholder: 'Theme',
									allowClear: true,
									width: 150,
								});
							}
							if (!$('#wpzoom-elementor-template-library-filter-category').hasClass('select2-hidden-accessible')) {
								$('#wpzoom-elementor-template-library-filter-category').select2({
									placeholder: 'Category',
									allowClear: true,
									width: 180,
								});
								// Load section categories from server
								wpzoom_load_section_categories();

								// Immediately hide category filter since we always start on Templates tab
								// This prevents race condition with setTimeout visibility update
								$('#wpzoom-elementor-template-library-filter-category').next('.select2-container').hide();
							}
							if (!$('#wpzoom-elementor-template-library-filter-wireframe-category').hasClass('select2-hidden-accessible')) {
								$('#wpzoom-elementor-template-library-filter-wireframe-category').select2({
									placeholder: 'Category',
									allowClear: true,
									width: 180,
								});
								// Load wireframe categories from server
								wpzoom_load_wireframe_categories();
								// Immediately hide wireframe category filter since we always start on Templates tab
								$('#wpzoom-elementor-template-library-filter-wireframe-category').next('.select2-container').hide();
							}

							// Bind filter change events (only once)
							if (!windowWPZ.filtersInitialized) {
								$('#wpzoom-elementor-template-library-filter-theme, #wpzoom-elementor-template-library-filter-category, #wpzoom-elementor-template-library-filter-wireframe-category').on('change', function () {
									wpzoom_apply_all_filters();
								});
								$(document).on('input', '#wpzoom-elementor-template-library-filter-text', function () {
									var val = $(this).val();
									$('.wpzoom-search-clear').toggle(val.length > 0);
									wpzoom_apply_all_filters();
								});
								$(document).on('click', '.wpzoom-search-clear', function () {
									$('#wpzoom-elementor-template-library-filter-text').val('');
									$(this).hide();
									wpzoom_apply_all_filters();
								});
								windowWPZ.filtersInitialized = true;
							}

							// Set tab counts immediately from PHP-provided data
					if (typeof wpzoom_admin_data !== 'undefined') {
						if (wpzoom_admin_data.pages_count) { $('[data-tab="templates"] .wpzoom-tab-count').text(wpzoom_admin_data.pages_count); }
						if (wpzoom_admin_data.sections_count) { $('[data-tab="sections"] .wpzoom-tab-count').text(wpzoom_admin_data.sections_count); }
						if (wpzoom_admin_data.wireframes_count) { $('[data-tab="wireframes"] .wpzoom-tab-count').text(wpzoom_admin_data.wireframes_count); }
					}

					// Initialize grid preference from localStorage
					if (!windowWPZ.gridCols) {
						var savedCols = 5;
						try {
							var stored = localStorage.getItem('wpzoom_library_grid_cols');
							if (stored) {
								var parsedCols = parseInt(stored);
								if ([3, 4, 5].indexOf(parsedCols) !== -1) { savedCols = parsedCols; }
							}
						} catch(e) {}
						windowWPZ.gridCols = savedCols;
					}

					// Bind grid size buttons (only once)
					if (!windowWPZ.gridInitialized) {
						$(document).on('click', '.wpzoom-grid-btn', function () {
							var cols = parseInt($(this).data('cols')) || 5;
							windowWPZ.gridCols = cols;
							wpzoom_apply_grid_cols(cols);
						});
						windowWPZ.gridInitialized = true;
					}

					// Apply saved grid pref to buttons immediately
					$('.wpzoom-grid-btn').removeClass('wpzoom-grid-btn-active');
					$('.wpzoom-grid-btn[data-cols="' + windowWPZ.gridCols + '"]').addClass('wpzoom-grid-btn-active');

					// Initialize no-media toggle preference
					if (typeof windowWPZ.noMedia === 'undefined') {
						var savedNoMedia = false;
						try { savedNoMedia = localStorage.getItem('wpzoom_no_media') === '1'; } catch(e) {}
						windowWPZ.noMedia = savedNoMedia;
					}
					// Bind media toggle (only once)
					if (!windowWPZ.mediaToggleInitialized) {
						$(document).on('click', '#wpzoom-no-media-toggle', function () {
							windowWPZ.noMedia = !windowWPZ.noMedia;
							try { localStorage.setItem('wpzoom_no_media', windowWPZ.noMedia ? '1' : '0'); } catch(e) {}
							wpzoom_update_media_toggle_ui();
						});
						windowWPZ.mediaToggleInitialized = true;
					}
					wpzoom_update_media_toggle_for_tab(windowWPZ.currentTab || 'templates');
					// Set initial filter visibility after select2 is fully initialized
							// Small delay ensures select2's DOM manipulation is complete
							setTimeout(function () {
								wpzoom_update_filter_visibility();
								// Load initial content after visibility is set
								wpzoom_get_library_view('templates');
							}, 150);
						},
						onHide: function () {
							// Reset filter values on close so they are cleared when modal is reopened
							var $theme = $('#wpzoom-elementor-template-library-filter-theme');
							var $category = $('#wpzoom-elementor-template-library-filter-category');
							var $wireframeCat = $('#wpzoom-elementor-template-library-filter-wireframe-category');
							if ($theme.length) { $theme.val(null).trigger('change'); }
							if ($category.length) { $category.val(null).trigger('change'); }
							if ($wireframeCat.length) { $wireframeCat.val(null).trigger('change'); }
							$('#wpzoom-elementor-template-library-filter-text').val('');
							$('.wpzoom-search-clear').hide();

							if ('dark' !== elementor.settings.editorPreferences.model.get('ui_theme')) {
								$("#wpzoom_main_library_templates_panel").removeClass('wpzoom-dark-mode');
							}
							else {
								$("#wpzoom_main_library_templates_panel").addClass('wpzoom-dark-mode');
							}
						}
					}
				)),
						windowWPZ.wpzModal.getElements("message").append(windowWPZ.wpzModal.addElement("content"), windowWPZ.wpzModal.addElement('loading'))),
					windowWPZ.wpzModal.show());
		}

		windowWPZ.wpzModal = null;

	}

	/* Add actions to the WPZOOM view templates panel */
	var WPZupdateActions = function (insertIndex) {

		/* INSERT template buttons */
		$('.wpzoom-btn-template-insert, .elementor-template-library-template-action').unbind('click');
		$('.wpzoom-btn-template-insert, .elementor-template-library-template-action').click(function () {
			// Check if this is a locked template trying to be inserted
			if ($(this).hasClass('wpzoom-locked-template')) {
				// Show upgrade notice for locked template insertion
				elementor.templates.showErrorDialog('This template is only available with WPZOOM Elementor Addons Pro license. Please visit wpzoom.com to get your license key.');
				return false;
			}

			var WPZ_selectedElement = this;
			showLoadingView();
			var filename = $(WPZ_selectedElement).attr("data-template-name") + ".json";

			// Determine import type based on active tab
			var importType = windowWPZ.currentTab === 'sections' ? 'section' : (windowWPZ.currentTab === 'wireframes' ? 'wireframe' : 'template');

			$.post(
				ajaxurl,
				{
					action: 'get_content_from_elementor_export_file',
					filename: filename,
					type: importType,
					no_media: ('wireframe' === importType || windowWPZ.noMedia) ? '1' : '0'
				},
				function (data) {
					try {
						// If data is already an object (from wp_send_json_error), use it directly
						if (typeof data === 'object' && data !== null) {
							if (data.success === false) {
								// Handle license error specifically
								if (data.data && data.data.is_license_error) {
									var errorMessage = data.data.message || 'This template requires WPZOOM Elementor Addons Pro license.';
									var licensePageUrl = (typeof wpzoom_admin_data !== 'undefined' && wpzoom_admin_data.license_page_url) ? wpzoom_admin_data.license_page_url : '/wp-admin/options-general.php?page=wpzoom-addons-license';
									var getLicenseUrl = (typeof wpzoom_admin_data !== 'undefined' && wpzoom_admin_data.get_license_url) ? wpzoom_admin_data.get_license_url : 'https://www.wpzoom.com/plugins/wpzoom-elementor-addons/';
									errorMessage += '<br><br><a href="' + licensePageUrl + '" target="_blank" style="color: #007cba; text-decoration: none;">Enter License Key</a> | <a href="' + getLicenseUrl + '" target="_blank" style="color: #007cba; text-decoration: none;">Get License Key</a>';
									elementor.templates.showErrorDialog(errorMessage);
								} else {
									elementor.templates.showErrorDialog(data.data.message || 'The template could not be imported. Please try again.');
								}
								hideLoadingView();
								return;
							}
						}

						// Parse data if it's a string
						if (typeof data === 'string') {
							data = JSON.parse(data);
						}

						if (insertIndex == -1) {
							elementor.getPreviewView().addChildModel(data, { silent: 0 });
						} else {
							elementor.getPreviewView().addChildModel(data, { at: insertIndex, silent: 0 });
						}
						elementor.channels.data.trigger('template:after:insert', {});
						if (undefined != $e && 'undefined' != typeof $e.internal) {
							$e.internal('document/save/set-is-modified', { status: true })
						}
						else {
							elementor.saver.setFlagEditorChange(true);
						}
						showLoadingView();
						windowWPZ.wpzModal.hide();
					} catch (e) {
						console.error('Error parsing template data:', e);
						elementor.templates.showErrorDialog('The template could not be imported. Invalid template data.');
						hideLoadingView();
					}
				})
				.fail(function error(errorData) {
					var errorMessage = 'The template could not be imported. Please try again or get in touch with the WPZOOM team.';

					// Check if it's a license-related error
					if (errorData.responseJSON && errorData.responseJSON.data && errorData.responseJSON.data.is_license_error) {
						errorMessage = errorData.responseJSON.data.message || 'This template requires WPZOOM Elementor Addons Pro license.';
						var licensePageUrl = (typeof wpzoom_admin_data !== 'undefined' && wpzoom_admin_data.license_page_url) ? wpzoom_admin_data.license_page_url : '/wp-admin/options-general.php?page=wpzoom-addons-license';
						var getLicenseUrl = (typeof wpzoom_admin_data !== 'undefined' && wpzoom_admin_data.get_license_url) ? wpzoom_admin_data.get_license_url : 'https://www.wpzoom.com/plugins/wpzoom-elementor-addons/';
						errorMessage += '<br><br><a href="' + licensePageUrl + '" target="_blank" style="color: #007cba; text-decoration: none;">Enter License Key</a> | <a href="' + getLicenseUrl + '" target="_blank" style="color: #007cba; text-decoration: none;">Get License Key</a>';
					}

					elementor.templates.showErrorDialog(errorMessage);
					hideLoadingView();
				});
		});

		/* Open the preview template */
		$('.wpzoom-template-thumb').click(function () {
			var jsonData = $(this).attr('data-template');
			var data = jQuery.parseJSON(jsonData);
			var rawId = data.id || '';
			var slug = String(rawId).toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9_-]/g, '');
			var isLocked = $(this).hasClass('wpzoom-template-thumb-locked');

			//console.log( data );
			$('.elementor-templates-modal__header__logo').hide();
			$('#wpzoom-elementor-template-library-toolbar').hide();
			$('#wpzoom-elementor-template-library-header-tabs').hide();
			$('#wpzoom-elementor-template-library-header-preview').show();
			$('#wpzoom-elementor-template-library-header-preview').find('.elementor-template-library-template-action').attr('data-template-name', slug);

			// If template is locked, add a class to the insert button to handle it differently
			if (isLocked) {
				$('#wpzoom-elementor-template-library-header-preview').find('.elementor-template-library-template-action').addClass('wpzoom-locked-template');
				// Update button text and style for locked templates
				$('#wpzoom-elementor-template-library-header-preview').find('.elementor-button-title').text('Unlock with Pro');
				$('#wpzoom-elementor-template-library-header-preview').find('.elementor-template-library-template-action').css({
					'background': '#3496ff',
					'color': '#fff'
				});
			} else {
				$('#wpzoom-elementor-template-library-header-preview').find('.elementor-template-library-template-action').removeClass('wpzoom-locked-template');
				// Reset button text and style for free templates
				var insertLabel = windowWPZ.currentTab === 'sections' ? 'Insert Section' : (windowWPZ.currentTab === 'wireframes' ? 'Insert Wireframe' : 'Insert Page');
				$('#wpzoom-elementor-template-library-header-preview').find('.elementor-button-title').text(insertLabel);
				$('#wpzoom-elementor-template-library-header-preview').find('.elementor-template-library-template-action').css({
					'background': '',
					'border-color': '',
					'color': ''
				});
			}

			$('.wpzoom-header-back-button').show();
			showLoadingView();
			// Use different AJAX action based on current tab
			var previewAction = windowWPZ.currentTab === 'sections' ? 'get_wpzoom_section_preview' : (windowWPZ.currentTab === 'wireframes' ? 'get_wpzoom_wireframe_preview' : 'get_wpzoom_preview');
			$.post(ajaxurl, { action: previewAction, data: data }, function (data) {
				//console.log( slug );
				hideLoadingView();
				$('.wpzoom__main-view').html(data);
				WPZupdateActions(insertIndex);
			});
		});

		/* Close preview window */
		$('.wpzoom-header-back-button').click(function () {
			$(this).hide();
			$('#wpzoom-elementor-template-library-header-preview').hide();
			$('#wpzoom-elementor-template-library-toolbar').show();
			$('#wpzoom-elementor-template-library-header-tabs').show();
			$('.elementor-templates-modal__header__logo').show();
			// Return to the currently active tab
			wpzoom_get_library_view(windowWPZ.currentTab);
		});

	}

	/* Update no-media toggle switch appearance */
	function wpzoom_update_media_toggle_ui() {
		var $track = $('#wpzoom-no-media-toggle');
		if (windowWPZ.noMedia) {
			$track.addClass('wpzoom-media-btn-active').attr('aria-checked', 'true');
		} else {
			$track.removeClass('wpzoom-media-btn-active').attr('aria-checked', 'false');
		}
	}

	/* Set toggle appearance + disabled state based on the active tab.
	 * Wireframes always import without media, so the toggle is locked ON. */
	function wpzoom_update_media_toggle_for_tab(tab) {
		var $wrap = $('#wpzoom-no-media-toggle-wrap');
		if (tab === 'wireframes') {
			$('#wpzoom-no-media-toggle').addClass('wpzoom-media-btn-active').attr('aria-checked', 'true');
			$wrap.addClass('wpzoom-toggle-disabled');
		} else {
			$wrap.removeClass('wpzoom-toggle-disabled');
			wpzoom_update_media_toggle_ui();
		}
	}

	/* Apply grid column class and persist preference */
	function wpzoom_apply_grid_cols(cols) {
		cols = parseInt(cols) || 5;
		$('.wpzoom-main-tiled-view')
			.removeClass('wpzoom-grid-cols-3 wpzoom-grid-cols-4 wpzoom-grid-cols-5')
			.addClass('wpzoom-grid-cols-' + cols);
		$('.wpzoom-grid-btn').removeClass('wpzoom-grid-btn-active');
		$('.wpzoom-grid-btn[data-cols="' + cols + '"]').addClass('wpzoom-grid-btn-active');
		try { localStorage.setItem('wpzoom_library_grid_cols', cols); } catch(e) {}
	}

	/* Apply all filters: dropdowns + search text */
	function wpzoom_apply_all_filters() {
		var searchText = ($('#wpzoom-elementor-template-library-filter-text').val() || '').toLowerCase().trim();
		var filters = {};

		// Collect filters from visible select2 dropdowns
		$('#elementor-template-library-filter select').each(function (index, select) {
			var $select = $(select);
			var $container = $select.next('.select2-container');
			if ($container.length && $container.is(':visible')) {
				var value = String($select.val());
				if (value && value !== '' && value !== 'null') {
					filters[$select.attr('name')] = value;
				}
			}
		});

		// Filter items
		$('.wpzoom-item').each(function () {
			var $item = $(this);
			var show = true;

			// Check dropdown filters
			if (Object.keys(filters).length > 0) {
				$.each(filters, function (name, val) {
					var itemData = $item.data(name);
					if (typeof itemData === 'undefined' || itemData === null || itemData === '') {
						show = false;
						return false;
					}
					itemData = String(itemData);
					val = String(val);
					if (itemData.indexOf(val) === -1) {
						show = false;
						return false;
					}
				});
			}

			// Check search text
			if (show && searchText !== '') {
				var titleText = ($item.find('.wpzoom-template-title').text() || '').toLowerCase();
				if (titleText.indexOf(searchText) === -1) {
					show = false;
				}
			}

			if (show) {
				$item.show();
			} else {
				$item.hide();
			}
		});

		// Show/hide category headers based on visible items
		$('h2.wpzoom-templates-library-template-category').each(function () {
			var $header = $(this);
			var $items = $header.nextUntil('h2.wpzoom-templates-library-template-category', '.wpzoom-item');
			if ($items.filter(':visible').length > 0) {
				$header.show();
			} else {
				$header.hide();
			}
		});
	}

	/* Get all the templates */
	function wpzoom_get_library_view(activeTab) {
		var tab = activeTab === 'sections' ? 'sections' : (activeTab === 'wireframes' ? 'wireframes' : 'templates');
		var filters = {};
		if (!insertIndex) { var insertIndex = null; }

		$('.elementor-templates-modal__header__logo').show();
		$('#wpzoom-elementor-template-library-toolbar').show();
		$('#wpzoom-elementor-template-library-header-tabs').show();
		$('.wpzoom-header-back-button').hide();
		$('#wpzoom-elementor-template-library-header-preview').hide();

		// Update tab visual state to match the active tab
		$('#wpzoom-elementor-template-library-tabs-wrapper .elementor-template-library-menu-item').removeClass('elementor-active').attr('aria-selected', 'false');
		$('#wpzoom-elementor-template-library-tabs-wrapper .elementor-template-library-menu-item[data-tab="' + tab + '"]').addClass('elementor-active').attr('aria-selected', 'true');

		showLoadingView();
		var action = tab === 'sections' ? 'get_wpzoom_sections_library_view' : (tab === 'wireframes' ? 'get_wpzoom_wireframes_library_view' : 'get_wpzoom_pages_library_view');
		var cached = tab === 'sections' ? WPZCachedSections : (tab === 'wireframes' ? WPZCachedWireframes : (WPZCachedTemplates || WPZCached || null));

		if (cached == null) { // If cache not created then load it
			/* Load library view via Ajax */
			$.post(ajaxurl, { action: action }, function (data) {
				hideLoadingView();
				$('.wpzoom__main-view').html(data);
				if (tab === 'sections') {
					WPZCachedSections = data;
				} else if (tab === 'wireframes') {
					WPZCachedWireframes = data;
				} else {
					WPZCachedTemplates = data;
					WPZCached = data; // keep legacy cache in sync
				}
				WPZupdateActions(insertIndex);
			wpzoom_apply_grid_cols(windowWPZ.gridCols || 5);
			});
		} else {
			hideLoadingView();
			$('.wpzoom__main-view').html(cached);
			WPZupdateActions(insertIndex);
			wpzoom_apply_grid_cols(windowWPZ.gridCols || 5);
		}

		//check if filter is not selected
		var filterValue = $('#wpzoom-elementor-template-library-filter-theme').val();
		if (filterValue) {
			filters['theme'] = filterValue;
			$('.wpzoom-item, h2.wpzoom-templates-library-template-category').each(function (i, item) {
				var show = true;
				$.each(filters, function (name, val) {
					if (val === null) { return; }
					if (name === 'theme' && $(item).data('theme').indexOf(val) === -1) {
						show = false;
					} else if ($(item).data(name).indexOf(val) === -1) {
						show = false;
					}
				});
				if (show) {
					$(item).show();
				} else {
					$(item).hide();
				}
			});
		}

		/* Add bottom hover to capture the correct index for insertion */
		var getTemplateBottomButton = $('#elementor-preview-iframe').contents().find('#elementor-add-new-section .elementor-add-template-button');
		if (getTemplateBottomButton.length && !getTemplateBottomButton.hasClass('WPZ_template_btn')) {
			getTemplateBottomButton.hover(function () {
				$(this).addClass('WPZ_template_btn');
				insertIndex = -1;
			});
		}

		/* Add inline hover to capture the correct index for insertion */
		var getTemplateInlineButtons = $('#elementor-preview-iframe').contents().find('.elementor-add-section-inline .elementor-add-template-button');
		if (getTemplateInlineButtons.length) {
			getTemplateInlineButtons.each(function () {
				if (!$(this).hasClass('WPZ_template_btn')) {
					$(this).addClass('WPZ_template_btn');
				} else {
					$(this).unbind('hover');
					$(this).hover(function () {
						var templateContainer = $(this).parent().parent().parent(),
							allSections = $(this).parent().parent().parent().parent().children(),
							tempInsertIndex = [];
						for (let index = 0; index < allSections.length; index++) {
							if (allSections[index].localName != 'div' || allSections[index] == templateContainer[0]) {
								tempInsertIndex.push(allSections[index]);
							}
						} // Make new array with only the selected add template
						for (let index = 0; index < tempInsertIndex.length; index++) {
							if (tempInsertIndex[index] == templateContainer[0]) { insertIndex = index; }
						} // get index of that selected add template area
					});
				}
			}); /* loop through inline template buttons */

		}  /* Inline template exists */
	}


})(jQuery);
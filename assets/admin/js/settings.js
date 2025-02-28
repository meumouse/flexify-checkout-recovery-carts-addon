( function($) {
	"use strict";

	/**
	 * Flexify Checkout Recovery Carts settings params
	 * 
	 * @since 1.0.0
	 * @return Object
	 */
	const params = fcrc_settings_params

	/**
	 * Flexify Checkout Recovery Carts settings object variable
	 * 
	 * @since 1.0.0
	 * @package MeuMouse.com
	 */
	const Settings = {
		init: function() {
			this.activateTabs();
			this.saveOptions();
		//	this.resetSettings();
		},

		/**
		 * Activate tabs and save on Cookies
		 * 
		 * @since 1.0.0
		 */
		activateTabs: function() {
			$(document).ready( function() {
				let url_hash = window.location.hash;
				let active_tab_index = localStorage.getItem('fc_recovery_carts_get_admin_tab_index');
		
				if (url_hash) {
					let target_tab = $('.fc-recovery-carts-wrapper a.nav-tab[href="' + url_hash + '"]');
		
					if (target_tab.length) {
						target_tab.click();
					}
				} else if (active_tab_index !== null) {
					$('.fc-recovery-carts-wrapper a.nav-tab').eq(active_tab_index).click();
				} else {
					$('.fc-recovery-carts-wrapper a.nav-tab[href="#general"]').click();
				}
			});
	  
			// Activate tab on click
			$(document).on('click', '.fc-recovery-carts-wrapper a.nav-tab', function() {
				 let tab_index = $(this).index();
				 localStorage.setItem('fc_recovery_carts_get_admin_tab_index', tab_index);
				 let attr_href = $(this).attr('href');
	  
				 $('.fc-recovery-carts-wrapper a.nav-tab').removeClass('nav-tab-active');
				 $('.fc-recovery-carts-form .nav-content').removeClass('active');
				 $(this).addClass('nav-tab-active');
				 $('.fc-recovery-carts-form').find(attr_href).addClass('active');
	  
				 return false;
			});
	  	},

		/**
		 * Display toast component
		 * 
		 * @since 1.0.0
		 * @param {string} type | Toast type (success, danger...)
		 * @param {string} header_title | Header title for toast
		 * @param {string} body_title | Body title for toast
		 * @package MeuMouse.com
		 */
		displayToast: function(type, header_title, body_title) {
			var toast_class = '';
			var header_class = '';
			var icon = '';

			if (type === 'success') {
				toast_class = 'toast-success';
				header_class = 'bg-success text-white';
				icon = '<svg class="icon icon-white me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M9.999 13.587 7.7 11.292l-1.412 1.416 3.713 3.705 6.706-6.706-1.414-1.414z"></path></svg>'
			} else if (type === 'error') {
				toast_class = 'toast-danger';
				header_class = 'bg-danger text-white';
				icon = '<svg class="icon icon-white me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M11 11h2v6h-2zm0-4h2v2h-2z"></path></svg>';
			} else if (type === 'warning') {
				toast_class = 'toast-warning';
				header_class = 'bg-warning text-white';
				icon = '<svg class="icon icon-white me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M11 11h2v6h-2zm0-4h2v2h-2z"></path></svg>';
			} else {
				// if unknown type, use default values
				toast_class = 'toast-secondary';
				header_class = 'bg-secondary text-white';
				icon = '';
			}

			// generate uniq id for toast
			var toast_id = 'toast-' + Math.random().toString(36).substr(2, 9);

			// build toast HTML
			var toast_html = `<div id="${toast_id}" class="toast ${toast_class} show">
				<div class="toast-header ${header_class}">
						${icon}
						<span class="me-auto">${header_title}</span>
						<button class="btn-close btn-close-white ms-2 hide-toast" type="button" data-bs-dismiss="toast" aria-label="${params.i18n.toast_aria_label}"></button>
				</div>
				<div class="toast-body">${body_title}</div>
			</div>`;

			// add toast on builder DOM
			$('.fc-recovery-carts-wrapper').before(toast_html);

			// fadeout after 3 seconds
			setTimeout(() => {
				jQuery('#' + toast_id).fadeOut('fast');
			}, 3000);

			// remove toast after 3,5 seconds
			setTimeout(() => {
				jQuery('#' + toast_id).remove();
			}, 3500);

			$(document).on('click', '.hide-toast', function() {
				var toast_id = $('.toast.show').attr('id');

				$('#' + toast_id).fadeOut('fast');
		
				// Remove toast from DOM
				setTimeout( function() {
					$('#' + toast_id).remove();
				}, 500);
			});
		},

		/**
		 * Keep button width and height state
		 * 
		 * @since 1.0.0
		 * @param {object} btn | Button object
		 * @returns {object}
		 */
		keepButtonState: function(btn) {
			var btn_width = btn.width();
			var btn_height = btn.height();
			var btn_html = btn.html();
	  
			// keep original width and height
			btn.width(btn_width);
			btn.height(btn_height);
	  
			return {
				width: btn_width,
				height: btn_height,
				html: btn_html,
			};
	  	},

		/**
		 * Save options in AJAX
		 * 
		 * @since 1.0.0
		 */
		saveOptions: function() {
			let settings_form = $('form[name="fc-recovery-carts-options-form"]');
			let original_values = settings_form.serialize();

			// save options on click button
			$('#fcrc_save_options').on('click', function(e) {
				e.preventDefault();
				
				let btn = $(this);
				let btn_state = Settings.keepButtonState(btn);

				// send request
				$.ajax({
					url: params.ajax_url,
					type: 'POST',
					data: {
						action: 'fc_recovery_carts_save_options',
						form_data: settings_form.serialize(),
					},
					beforeSend: function() {
						btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
					},
					success: function(response) {
						if (params.debug_mode) {
							console.log(response);
						}

						try {
							if (response.status === 'success') {
								original_values = settings_form.serialize();

								Settings.displayToast('success', response.toast_header_title, response.toast_body_title);
							}
						} catch (error) {
							console.log(error);
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error('AJAX Error:', textStatus, errorThrown);
					},
					complete: function() {
						btn.html(btn_state.html);
					},
				});
			});

			// Activate save button on change options
			settings_form.on('change input', 'input, select, textarea', function() {
				if (settings_form.serialize() !== original_values) {
					$('#fcrc_save_options').prop('disabled', false);
				} else {
					$('#fcrc_save_options').prop('disabled', true);
				}
			});
		},


		/**
		 * Reset plugin settings
		 * 
		 * @since 1.0.0
		 */
	/*	resetSettings: function() {
			// display reset modal
			display_popup( $('#fc_recovery_carts_reset_settings_trigger'), $('#fc_recovery_carts_reset_settings_container'), $('#fc_recovery_carts_reset_settings_close') );
				
			// Reset plugin settings
			$(document).on('click', '#confirm_reset_settings', function(e) {
				e.preventDefault();
				
				let btn = $(this);
				let btn_state = Settings.keepButtonState(btn);

				// send request
				$.ajax({
					url: params.ajax_url,
					type: 'POST',
					data: {
						action: 'fc_recovery_carts_reset_plugin_action',
					},
					beforeSend: function() {
						btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
					},
					success: function(response) {
						try {
							if (response.status === 'success') {
								$('#fc_recovery_carts_reset_settings_container').removeClass('show');
								Settings.displayToast('success', response.toast_header_title, response.toast_body_title);
							} else {
								Settings.displayToast('error', response.toast_header_title, response.toast_body_title);
							}
						} catch (error) {
							console.log(error);
						}
					},
					error: function(xhr, status, error) {
						console.error('Error on AJAX request:', xhr.responseText);
					},
					complete: function() {
						btn.prop('disabled', false).html(btn_state.html);
					},
				});
			});
		},*/
	};

	// Initialize the Settings object on ready event
	jQuery(document).ready( function($) {
		Settings.init();
	});
})(jQuery);
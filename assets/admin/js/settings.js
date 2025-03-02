( function($) {
	"use strict";

	/**
	 * Flexify Checkout Recovery Carts settings params
	 * 
	 * @since 1.0.0
	 * @return Object
	 */
	const params = fcrc_settings_params;

	/**
	 * Flexify Checkout Recovery Carts settings object variable
	 * 
	 * @since 1.0.0
	 * @package MeuMouse.com
	 */
	const Settings = {
		/**
		 * Initialize object functions
		 * 
		 * @since 1.0.0
		 */
		init: function() {
			this.activateTabs();
			this.saveOptions();
			this.addNewFollowUp();
			this.editFollowUp();
			this.deleteFollowUp();
			this.collectLeadSettings();
			this.selectColor();
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
		 * Display modal component
		 * 
		 * @since 1.0.0
		 * @param {string} trigger | Trigger for display popup
		 * @param {string} container | Container for display content
		 * @param {string} close | Close button popup
		 */
		displayModal: function(trigger, container, close) {
			// Handle both ID string and jQuery object
			const trigger_id = typeof trigger === 'string' ? trigger : '#' + trigger.attr('id');
			const container_id = typeof container === 'string' ? container : '#' + container.attr('id');
			const close_id = typeof close === 'string' ? close : '#' + close.attr('id');
		
			// Open modal
			$(document).on('click', trigger_id, function(e) {
				e.preventDefault();
				$(container_id).addClass('show');
			});
		
			// Close modal on outside click
			$(document).on('click', container_id, function(e) {
				if (e.target === this) {
					$(this).removeClass('show');
				}
			});
		
			// Close modal on close button click
			$(document).on('click', close_id, function(e) {
				e.preventDefault();
				$(container_id).removeClass('show');
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
		 * Add new follow up item
		 * 
		 * @since 1.0.0
		 */
		addNewFollowUp: function() {
			let container = $('#fcrc_add_new_follow_up_container');

			// display reset modal
			Settings.displayModal( $('#fcrc_add_new_follow_up_trigger'), container, $('#fcrc_add_new_follow_up_close') );
				
			// Add new follow up on click button
			$(document).on('click', '#fcrc_add_new_follow_up_save', function(e) {
				e.preventDefault();
				
				let btn = $(this);
				let btn_state = Settings.keepButtonState(btn);
				let follow_up_title = $('#fcrc_add_new_follow_up_title');
				let follow_up_message = $('#fcrc_add_new_follow_up_message');
				let follow_up_delay_time = $('#fcrc_add_new_follow_up_delay_time');
				let follow_up_delay_type = $('#fcrc_add_new_follow_up_delay_type');
				let whatsapp_channel = $('#fcrc_add_new_follow_up_channels_whatsapp');

				// send request
				$.ajax({
					url: params.ajax_url,
					type: 'POST',
					data: {
						action: 'fcrc_add_new_follow_up',
						title: follow_up_title.val(),
						message: follow_up_message.val(),
						delay_time: follow_up_delay_time.val(),
						delay_type: follow_up_delay_type.val(),
						whatsapp: whatsapp_channel.prop('checked') ? 'yes' : 'no',
						email: '',
					},
					beforeSend: function() {
						btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
					},
					success: function(response) {
						try {
							if (response.status === 'success') {
								$('#follow_up').find('ul.fcrc-follow-up-list').replaceWith(response.follow_up_list);
								$('#fcrc_add_new_follow_up_container').removeClass('show');
								
								Settings.displayToast('success', response.toast_header_title, response.toast_body_title);

								// reset options
								follow_up_title.val('');
								follow_up_message.val('');
								follow_up_delay_time.val('');
								follow_up_delay_type.val('');
								whatsapp_channel.prop('checked', false);
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
		},

		/**
		 * Edit follow up item
		 * 
		 * @since 1.0.0
		 */
		editFollowUp: function() {
			// display edit follow up modal
			$(document).on('click', '.edit-follow-up-item', function(e) {
				e.preventDefault();
				
				var trigger = $(this);
				var container = trigger.siblings('.edit-follow-up-container');
				var close = trigger.siblings('.edit-follow-up-close');
				
				// Show modal immediately
				$('#' + container.attr('id')).addClass('show');
				
				// Set up close handlers
				$(document).on('click', '#' + container.attr('id'), function(e) {
					if (e.target === this) {
						$(this).removeClass('show');
					}
				});
				
				$(document).on('click', '#' + close.attr('id'), function(e) {
					e.preventDefault();
					$('#' + container.attr('id')).removeClass('show');
				});
			});

			// update list item on change title
			$(document).on('change keyup input', '.get-follow-up-title', function() {
				let value = $(this).val();
				var get_item = $('.edit-follow-up-container.show').data('follow-up-item');

				console.log(get_item);
				$('.list-group-item[data-follow-up-item="' + get_item + '"]').find('.fcrc-follow-up-item-title').text(value);
			});
		},

		/**
		 * Delete follow up item
		 * 
		 * @since 1.0.0
		 */
		deleteFollowUp: function() {
			// delete follow up item on click delete button
			$(document).on('click', '.delete-follow-up-item', function(e) {
				e.preventDefault();

				var btn = $(this);
				var btn_state = Settings.keepButtonState(btn);
				var get_item = btn.data('follow-up-item');

				if ( ! confirm(params.i18n.confirm_delete_follow_up) ) {
					return;
				}

				// send request
				$.ajax({
					url: params.ajax_url,
					type: 'POST',
					data: {
						action: 'fcrc_delete_follow_up',
						event_key: get_item,
					},
					beforeSend: function() {
						btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
					},
					success: function(response) {
						try {
							if (response.status === 'success') {
								$('#follow_up').find('ul.fcrc-follow-up-list').replaceWith(response.follow_up_list);
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
		},

		/**
		 * Change visibility for elements
		 * 
		 * @since 1.0.0
		 * @param {string} selector | Activation element selector
		 * @param {string} container | Container selector
		 */
		visibilityController: function(selector, container) {
			// Initial state
			let checked = $(selector).prop('checked');
			
			$(container).toggleClass('d-none', ! checked);
		
			// Update state on click
			$(selector).on('click', function() {
				checked = $(this).prop('checked'); // Get current state inside click handler

				$(container).toggleClass('d-none', ! checked);
			});
		},

		/**
		 * Change collect lead modal settings
		 * 
		 * @since 1.0.0
		 */
		collectLeadSettings: function() {
			// display trigger modal
			Settings.visibilityController( '#enable_modal_add_to_cart', '#collect_lead_modal_settings_trigger' );

			// open settings modal
			Settings.displayModal( '#collect_lead_modal_settings_trigger', '#collect_lead_modal_settings_container', '#collect_lead_modal_settings_close' );
		},

		/**
		 * Select color
		 * 
		 * @since 1.0.0
		 */
		selectColor: function() {
			$('.get-color-selected').on('input', function() {
				var color_value = $(this).val();
		
				$(this).closest('.color-container').find('.form-control-color').val(color_value);
			});
		
			$('.form-control-color').on('input', function() {
				var color_value = $(this).val();
		
				$(this).closest('.color-container').find('.get-color-selected').val(color_value);
			});
	
			$('.reset-color').on('click', function(e) {
				e.preventDefault();
				
				var color_value = $(this).data('color');
	
				$(this).closest('.color-container').find('.form-control-color').val(color_value);
				$(this).closest('.color-container').find('.get-color-selected').val(color_value).change();
			});
		},
	};

	// Initialize the Settings object on ready event
	jQuery(document).ready( function($) {
		Settings.init();
	});
})(jQuery);
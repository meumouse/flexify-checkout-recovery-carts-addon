( function($) {
	"use strict";

	/**
	 * Flexify Checkout Recovery Carts events params
	 * 
	 * @since 1.0.0
	 * @return Object
	 */
	const params = fcrc_events_params;

	/**
	 * Flexify Checkout Recovery Carts events object variable
	 * 
	 * @since 1.0.0
	 * @package MeuMouse.com
	 */
	const Events = {
        
        /**
		 * Initialize object functions
		 * 
		 * @since 1.0.0
		 */
		init: function() {
            this.openModal();
			this.collectLead();

            // check if international phone input is enabled
            if ( params.enable_international_phone === 'yes' ) {
                this.initPhoneInput();
            }
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
         * Open pre checkout modal
         * 
         * @since 1.0.0
         */
        openModal: function() {
            var triggers = params.triggers_list;

            // open modal using event delegation
            $(triggers).hover( function(e) {
                // check if lead was already collected or rejected
                if ( $('body').hasClass('fcrc-lead-collected') || $('body').hasClass('fcrc-lead-rejected') ) {
                    return;
                }

                $('.fcrc-popup-container.lead-capture-modal').addClass('show');
            });

            // close modal
            $(document).on('click', '.fcrc-popup-close', function(e) {
                e.preventDefault();

                $('.fcrc-popup-container.lead-capture-modal').removeClass('show');
                $('body').addClass('fcrc-lead-rejected')
            });
        },

        /**
         * Collect lead event
         * 
         * @since 1.0.0
         */
        collectLead: function() {
            // send request on click trigger button
            $(document).on('click', '.fcrc-trigger-send-lead', function(e) {
                e.preventDefault();

                var btn = $(this);
                var btn_state = Events.keepButtonState(btn);

                $.ajax({
                    url: params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'fcrc_lead_collected',
                        first_name: $('.fcrc-get-first-name').val(),
                        last_name: $('.fcrc-get-last-name').val(),
                        phone: $('.fcrc-get-phone').val(),
                        email: $('.fcrc-get-email').val(),
                    },
                    beforeSend: function() {
                        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                    },
                    success: function (response) {
                        if ( params.dev_mode ) {
                            console.log(response);
                        }
                        
                        try {
                            if ( response.status === 'success' ) {
                                $('body').addClass('fcrc-lead-collected');
                                $('.fcrc-popup-container.lead-capture-modal').removeClass('show');
                            } else {
                                $('body').removeClass('fcrc-lead-collected');
                            }
                        } catch (error) {
                            console.log(error);
                        }
                    },
                    error: function () {
                        console.log("Erro ao registrar o carrinho.");
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(btn_state.html);
                    },
                });
            });
        },

        /**
         * Get cookie value by name
         * 
         * @since 1.0.0
         * @param {string} name | Cookie name
         * @returns Cookie value
         */
        getCookie: function(name) {
            let matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.\$?*|{}\(\)\[\]\/+^])/g, '\\$1') + "=([^;]*)"
            ));

            return matches ? decodeURIComponent(matches[1]) : undefined;
        },

        /**
         * Initialize international phone input
         * 
         * @since 1.0.0
         */
        initPhoneInput: function() {
            const input = document.querySelector('.fcrc-input.fcrc-get-phone');

            // initialize intl tel input
            const iti = window.intlTelInput( input, {
                loadUtilsOnInit: params.path_to_utils,
                autoPlaceholder: "aggressive",
                containerClass: "fcrc-international-phone-selector",
                initialCountry: "auto",
                geoIpLookup: function(success, failure) {
                    const country_code = Events.getCookie('fcrc_phone_country_code');

                    if (country_code) {
                        success(country_code);
                    } else {
                        fetch("https://ipapi.co/json")
                        .then( function(response) { 
                            return response.json();
                        })
                        .then( function(data) {
                            // set response API in cookies for 7 days
                            document.cookie = "fcrc_phone_country_code=" + data.country_code + "; max-age=" + (7 * 24 * 60 * 60) + "; path=/";
                            success(data.country_code);
                        })
                        .catch( function() {
                            failure("br");
                        });
                    }
                },
                i18n: {
                    searchPlaceholder: params.i18n.intl_search_input_placeholder,
                },
            });
        },
    }

    // Initialize the Settings object on ready event
	jQuery(document).ready( function($) {
		Events.init();
	});
})(jQuery);
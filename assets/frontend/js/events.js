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
     * Get country data
     * 
     * @since 1.0.0
     * @returns {object}
     */
    var country = {};

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
         * @version 1.0.1
		 */
		init: function() {
			this.collectLead();

            // check if collect data from IP is enabled
            if ( params.collect_data_from_ip === 'yes' ) {
                this.getUserLocation();
            }

            // check if current page has product
            if ( params.is_product ) {
                this.openModal();
            }

            // check if international phone input is enabled
            if ( params.enable_international_phone === 'yes' && params.is_product ) {
                this.internationalPhone();
            }

            if ( ! window.fcrcPingStarted ) {
			    this.startPingTracking();

                window.fcrcPingStarted = true;
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
         * @version 1.1.0
         */
        openModal: function() {
            var triggers = params.triggers_list;

            // open modal using event delegation
            $(triggers).hover( function(e) {
                // check if lead was already collected or rejected
                if ( $('body').hasClass('fcrc-lead-collected') || $('body').hasClass('fcrc-lead-rejected') || Events.getCookie('fcrc_lead_collected') === 'yes' ) {
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
         * @version 1.1.0
         */
        collectLead: function() {
            // send request on click trigger button
            $(document).on('click', '.fcrc-trigger-send-lead', function(e) {
                e.preventDefault();

                var btn = $(this);
                var btn_state = Events.keepButtonState(btn);
                var get_first_name = $('.fcrc-get-first-name').val() || Events.getCookie('fcrc_first_name');
                var get_last_name = $('.fcrc-get-last-name').val() || Events.getCookie('fcrc_last_name');
                var get_phone = $('.fcrc-get-phone').val() || Events.getCookie('fcrc_phone');
                var get_email = $('.fcrc-get-email').val() || Events.getCookie('fcrc_email');

                // send ajax request
                $.ajax({
                    url: params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'fcrc_lead_collected',
                        first_name: get_first_name,
                        last_name: get_last_name,
                        phone: get_phone,
                        email: get_email,
                        country_data: JSON.stringify(country),
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

                                // save post id on cookie for 7 days
                                Events.setCookie('fcrc_cart_id', response.cart_id, 7);

                                // save lead data on cookie for 30 days
                                Events.setCookie('fcrc_first_name', get_first_name, 365);
                                Events.setCookie('fcrc_last_name', get_last_name, 365);
                                Events.setCookie('fcrc_phone', get_phone, 365);
                                Events.setCookie('fcrc_email', get_email, 365);
                                Events.setCookie('fcrc_lead_collected', 'yes', 365);
                            } else {
                                $('body').removeClass('fcrc-lead-collected');
                            }
                        } catch (error) {
                            console.log(error);
                        }
                    },
                    error: function () {
                        console.log('Error on send lead data');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(btn_state.html);
                    },
                });
            });
        },

        /**
         * Set cookie value
         * 
         * @since 1.0.0
         * @param {string} name | Cookie name
         * @param {string} value | Cookie value
         * @param {int} days | Expiration time in days
         */
        setCookie: function(name, value, days) {
            let expires = "";

            if (days) {
                let date = new Date();

                date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
                expires = "; expires=" + date.toUTCString();
            }

            document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
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
        internationalPhone: function() {
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

            /**
             * Get current country data
             * 
             * @since 1.0.0
             * @returns 
             */
            function getCurrentCountry() {
                const countryData = iti.getSelectedCountryData();
                
                return countryData;
            }

            // wait for the initialization to ensure the country is loaded correctly
            setTimeout(() => {
                country = getCurrentCountry();
            }, 500);
        },

        /**
         * Sends a ping to keep the cart session active
         * 
         * @since 1.0.0
         */
        sendPing: function() {
            let cart_id = Events.getCookie('fcrc_cart_id');

            if ( ! cart_id ) {
                return;
            }

            // send request
            $.ajax({
                url: params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fcrc_cart_ping',
                    cart_id: cart_id,
                    ping: true,
                },
                success: function(response) {
                    if (params.dev_mode) {
                        console.log("Ping sent:", response);
                    }
                },
                error: function() {
                    console.log("Error sending cart ping.");
                }
            });
        },

        /**
         * Starts ping tracking to detect abandonment
         *
         * @since 1.0.0
         * @version 1.1.0
         */
        startPingTracking: function() {
            let cart_id = Events.getCookie('fcrc_cart_id');

            if ( ! cart_id ) {
                return;
            }

            // Send an initial ping immediately if page is visible
            if ( document.visibilityState === 'visible' ) {
                Events.sendPing();
            }

            var interval = params.get_heartbeat_interval * 1000;

            // Send a ping every 30 seconds
            setInterval(Events.sendPing, interval);
        },

        /**
         * Get user location via IP and send data to backend
         * 
         * @since 1.0.1
         * @version 1.1.0
         */
        getUserLocation: function() {
            var location_data = Events.getCookie('fcrc_location');

            if ( location_data ) {
                Events.sendLocationData(location_data);

                return;
            }

            fetch('https://ipapi.co/json')
            .then(response => response.json())
            .then(data => {
                country = {
                    country_code: data.country_code,
                    country_name: data.country_name,
                    region: data.region, // state
                    city: data.city, // city
                    ip: data.ip,
                };

                // Save location data in cookie for 7 days
                Events.setCookie('fcrc_location', JSON.stringify(country), 7);

                // Send the data via AJAX
                Events.sendLocationData(country);
            })
            .catch(error => {
                console.error("Error fetching location:", error);
            });
        },

        /**
         * Send user location data to backend via AJAX
         * 
         * @since 1.0.1
         * @param {object} countryData
         */
        sendLocationData: function(countryData) {
            let cart_id = Events.getCookie('fcrc_cart_id');

            if ( ! cart_id ) {
                return;
            }

            // send request
            $.ajax({
                url: params.ajax_url,
                type: 'POST',
                data: {
                    action: 'fcrc_update_location',
                    cart_id: cart_id,
                    country_data: JSON.stringify(countryData)
                },
                success: function(response) {
                    if (params.dev_mode) {
                        console.log("Location data sent:", response);
                    }
                },
                error: function() {
                    console.error("Error sending location data.");
                }
            });
        },
    }

    // Initialize the Settings object on ready event
	jQuery(document).ready( function($) {
		Events.init();
	});
})(jQuery);
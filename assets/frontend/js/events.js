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
			this.addedToCart();
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
        //    var triggers = params.triggers_list;
            var triggers = 'button[name="add-to-cart"]';

            // open modal using event delegation
            $(document).on('click', triggers, function(e) {
                e.preventDefault();

                // check if lead was already collected
                if ( $('body').hasClass('fcrc-lead-collected') ) {
                    return;
                }

                $('.fcrc-popup-container.lead-capture-modal').addClass('show');
            });

            // close modal
            $(document).on('click', '.fcrc-popup-close', function(e) {
                e.preventDefault();

                $('.fcrc-popup-container.lead-capture-modal').removeClass('show');
            });
        },

        /**
         * Listen added_to_cart event
         * 
         * @since 1.0.0
         */
        addedToCart: function() {
            // send request on click trigger button
            $(document).on('click', '.fcrc-trigger-send-lead', function(e) {
                e.preventDefault();

                var btn = $(this);
                var btn_state = Events.keepButtonState(btn);

                $.ajax({
                    url: params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'fc_add_recovery_cart',
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
                    }
                });
            });
        },
    }

    // Initialize the Settings object on ready event
	jQuery(document).ready( function($) {
		Events.init();
	});
})(jQuery);
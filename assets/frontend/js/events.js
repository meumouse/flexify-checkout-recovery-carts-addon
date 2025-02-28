( function($) {
	"use strict";

	/**
	 * Flexify Checkout Recovery Carts events params
	 * 
	 * @since 1.0.0
	 * @return Object
	 */
	const params = fc_recovery_cart_params;

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
			this.onAddToCart();
		},

        /**
         * Listen added_to_cart event
         * 
         * @since 1.0.0
         */
        onAddToCart: function() {
            $(document.body).on('added_to_cart', function() {
                $.ajax({
                    url: params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'fc_add_recovery_cart',
                        security: params.nonce.fc_recovery_carts_nonce,
                        contact: localStorage.getItem('fc_recovery_contact') || '' // Optional contact field
                    },
                    success: function (response) {
                        console.log(response.message);
                    },
                    error: function () {
                        console.log("Erro ao registrar o carrinho.");
                    },
                });
            });
        },
    }

    // Initialize the Settings object on ready event
	jQuery(document).ready( function($) {
		Events.init();
	});
})(jQuery);
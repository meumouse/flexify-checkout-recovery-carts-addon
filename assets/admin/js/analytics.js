( function($) {
	"use strict";

	/**
	 * Get checkout events params
	 * 
	 * @since 1.3.0
	 * @return Object
	 */
	const params = fcrc_analytics_params || {};

    /**
     * Get period filter
     * 
     * @since 1.3.0
     * @return string
     */
    var period;

	/**
	 * Checkout events object variable
	 * 
	 * @since 1.3.0
	 * @package MeuMouse.com
	 */
	var Analytics = {
        
        /**
         * Set cookie value
         * 
         * @since 1.3.0
         * @param {string} name | Cookie name
         * @param {string} value | Cookie value
         * @param {int} days | Expiration time in days
         * @return void
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
         * @since 1.3.0
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
		 * Get URL parameter by name
		 * 
		 * @since 1.3.0
		 * @param {string} name | Parameter name
		 * @returns Parameter value
		 */
		getParamByName: function(name) {
			let url = window.location.href;
			name = name.replace(/[\[\]]/g, "\\{text}");
			let regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"), results = regex.exec(url);

			if (!results) return null;
			if (!results[2]) return '';
			
			return decodeURIComponent( results[2].replace(/\+/g, " ") );
		},

		/**
		 * Add query params on URL
		 * 
		 * @since 1.3.0
		 * @param {string} param | Parameter name
		 * @param {string} value | Parameter value
         * @return void
		 */
		addQueryParam: function(param, value) {
			// get current URL
			var url = new URL(window.location.href);

			// add or update URL params
			url.searchParams.set( param, value );

			// update URL without reload page
			window.history.pushState( {}, '', url );
		},

        /**
         * Select period filter for analytics
         * 
         * @since 1.3.0
         * @return void
         */
        selectPeriodFilter: function() {
            let get_period = Analytics.getParamByName('period');

            // set active period filter
            if ( get_period ) {
                $(`.period-filter-item[data-period="${get_period}"]`).addClass('active');
            } else {
                $('.period-filter-item').first().addClass('active');
            }

            // set period filter
            $(document).on('click', '.period-filter-item', function(e) {
                e.preventDefault();

                let btn = $(this);
                let get_period = $(this).data('period');

                $('.period-filter-item').removeClass('active');
                $('.fcrc-analytics-widget').addClass('placeholder-content');
                btn.addClass('active');

                // add query param to URL
                Analytics.addQueryParam( 'period', get_period );
                Analytics.fetchData(get_period);
            });
        },

        /**
         * Fetch analytics data from backend
         * 
         * @since 1.3.0
         * @param {int} days | Number of days to fetch data
         * @return void
         */
        fetchData: function( days ) {
            $.ajax({
                url: params.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'fc_recovery_carts_get_analytics_data',
                    period: days,
                },
                success: function (response) {
                    if (response.success) {
                        $('.fcrc-analytics-widget').removeClass('placeholder-content');
                        $('.fcrc-analytics-container-item.get-total-recovered').html(response.data.total_recovered_widget);

                        // render chart for total recovered
                        Analytics.renderChart(
                            response.data.recovered_chart.labels,
                            response.data.recovered_chart.series
                        );
                    }
                },
                error: function () {
                    console.error('Erro ao carregar dados de análise');
                },
            });
        },

        /**
         * Render charts
         * 
         * @since 1.3.0
         * @param {array} labels | Chart labels
         * @param {array} seriesData | Chart series data
         * @return void
         */
        renderChart: function( labels, seriesData ) {
            const options = {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                series: [{
                    name: 'Valor recuperado (R$)',
                    data: seriesData
                }],
                xaxis: {
                    categories: labels,
                    labels: {
                        style: { fontSize: '13px' }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            return 'R$ ' + val.toFixed(2).replace('.', ',');
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return 'R$ ' + val.toFixed(2).replace('.', ',');
                        }
                    }
                }
            };

            this.chart = new ApexCharts(document.querySelector("#fcrc-recovered-chart"), options);
            this.chart.render();
        },

        /**
         * Listen for events
         * 
         * @since 1.3.0
         * @return void
         */
        bindEvents: function() {
            
        },

		/**
		 * Initialize object functions
		 * 
		 * @since 1.3.0
         * @return void
		 */
		init: function() {
            this.selectPeriodFilter();
            this.bindEvents();

            let get_period = Analytics.getParamByName('period') || 7;
            this.fetchData(get_period);
		},
    }

    // Initialize the object on ready event
	jQuery(document).ready( function($) {
		Analytics.init();
	});
})(jQuery);
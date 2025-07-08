<?php

use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Get count of carts by status and period
 *
 * @since 1.3.0
 * @param string $status | Post status
 * @param int $days | Days of query
 * @return int
 */
function fcrc_get_carts_count_by_status( $status, $days = 7 ) {
	global $wpdb;

	// Validate input
	$status = sanitize_key( $status );
	$days = intval( $days );

	// Calculate date interval
	$date = gmdate( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

	$query = $wpdb->prepare(
		"SELECT COUNT(ID)
		FROM {$wpdb->posts}
		WHERE post_type = 'fc-recovery-carts'
		AND post_status = %s
		AND post_date >= %s",
		$status,
		$date
	);

	return intval( $wpdb->get_var( $query ) );
}


/**
 * Get recovered cart totals grouped by day
 *
 * @since 1.3.0
 * @param int $days | Number of days back
 * @return array
 */
function fcrc_get_daily_recovered_totals( $days = 7 ) {
	global $wpdb;

	$days = intval( $days );
	$start_date = gmdate( 'Y-m-d 00:00:00', strtotime( "-$days days" ) );

	$query = $wpdb->prepare(
		"SELECT DATE(p.post_date) as date, SUM( CAST( pm.meta_value AS DECIMAL( 10, 2 ) ) ) as total
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
		WHERE p.post_type = 'fc-recovery-carts'
		AND p.post_status = 'recovered'
		AND p.post_date >= %s
		AND pm.meta_key = '_fcrc_cart_total'
		GROUP BY DATE(p.post_date)
		ORDER BY DATE(p.post_date) ASC",
		$start_date
	);

    $totals_by_date = array();
	$results = $wpdb->get_results( $query );

	foreach ( $results as $row ) {
		$totals_by_date[ $row->date ] = floatval( $row->total );
	}

    // set with 0 the days that don't have values
	$labels = array();
	$series = array();

	for ( $i = $days - 1; $i >= 0; $i-- ) {
		$timestamp = current_time('timestamp') - ( $i * DAY_IN_SECONDS );
		$key = date( 'Y-m-d', $timestamp );
		$value = isset( $by_date[ $key ] ) ? floatval( $by_date[ $key ] ) : 0;

		// formatted labels from WordPress
		$labels[] = date_i18n( get_option('date_format'), $timestamp );
		$series[] = $value;
	}

	return array(
		'labels' => $labels,
		'series' => $series,
	);
}


/**
 * Get notifications sent in the last X days, formatted for ApexCharts
 *
 * @since 1.3.0
 * @param int $days | Number of days to look back
 * @return array
 */
function fcrc_get_notifications_chart_data( $days = 7 ) {
	// calculate initial timestamp and empty array
    $start_ts = current_time('timestamp') - ( $days * DAY_IN_SECONDS );
    $counts = array();

	// get all the carts that have notifications
    $query = new WP_Query( array(
        'post_type' => 'fc-recovery-carts',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_query' => array(
            array(
                'key' => '_fcrc_notifications_sent',
                'compare' => 'EXISTS',
			),
		),
	));

	// iterate for each cart post
    foreach ( $query->posts as $post ) {
        $notes = get_post_meta( $post->ID, '_fcrc_notifications_sent', true );

        if ( ! is_array( $notes ) ) {
            continue;
        }

        foreach ( $notes as $note ) {
            $timestamp = strtotime( $note['sent_at'] );

            if ( $timestamp < $start_ts ) {
                continue;
            }
			
            $day = date( 'Y-m-d', $timestamp );
            $channel = sanitize_key( $note['channel'] );

            if ( ! isset( $counts[ $day ][ $channel ] ) ) {
                $counts[ $day ][ $channel ] = 0;
            }
			
            $counts[ $day ][ $channel ]++;
        }
    }

    wp_reset_postdata();

	// prepare raw keys and formatted labels, ensure empty buckets
    $raw_days = array();
    $categories = array();

    for ( $i = 0; $i < $days; $i++ ) {
        $ts = current_time('timestamp') - ( $i * DAY_IN_SECONDS );
        $day_key = date( 'Y-m-d', $ts );

        // keep raw date for data lookup
        $raw_days[] = $day_key;

        // formatted label according to WP settings
        $categories[] = date_i18n( get_option('date_format'), $ts );

        if ( ! isset( $counts[ $day_key ] ) ) {
            $counts[ $day_key ] = array();
        }
    }

    // reverse to chronological order
    $raw_days = array_reverse( $raw_days );
    $categories = array_reverse( $categories );

    // identifies all channels that appear
    $all_channels = array();

    foreach ( $counts as $day_data ) {
        foreach ( array_keys( $day_data ) as $channel ) {
            $all_channels[ $channel ] = true;
        }
    }

    $all_channels = array_keys( $all_channels );

    // build series for each channel
    $series = array();

    foreach ( $all_channels as $channel ) {
        $data = array();

        foreach ( $raw_days as $day_key ) {
            $data[] = $counts[ $day_key ][ $channel ] ?? 0;
        }

        // set chart label
        $label = Helpers::get_formatted_channel_label( $channel );

        $series[] = array(
            'name' => $label,
            'data' => $data,
        );
    }

    return array(
        'categories' => $categories,
        'series' => $series,
    );
}

<?php
/*
 * Plugin Name: HTTPS guide link for WordPress Health Check
 * Plugin URI: https://www.proisp.no/
 * Description: Provide a link to our guide on how to force all traffic to the webpage to use https. Displayed if https test fails. Inspired by and template by Marius L. J. (@Clorith) at WordPress.org. The original can be <a href="https://make.wordpress.org/core/2019/09/25/whats-new-in-site-health-for-wordpress-5-3/#highlighter_144139" target="_blank">found here</a>.
 * Version: 1.0
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Author: Knut Sparhell
 * Author URI: https://nettvendt.no/
 * Text Domain: proisp
 * License:     GPL v2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you should not access this file directly.' );	// Silence is golden
}

// Start section to be customized by PRO ISP:
const PROISP_FORCE_USE_HTTPS_URLS = [
	''      => 'https://www.proisp.eu/guides/force-https-domain/',		// Default language
	'en_US' => 'https://www.proisp.eu/guides/force-https-domain/',		// May be omitted. If set, same as default
	'nb_NO' => 'https://www.proisp.no/guider/sikre-https-domene/',		// Norwegian (bokmål)
	'nn_NO' => 'https://www.proisp.no/guider/sikre-https-domene/',		// Norwegian (nynorsk)
];

const PROISP_REMOTE_GET_TIMEOUT = 19;					// Timeout (integer) in seconds used by wp_remote_get. Sane values: 5 to 120 (seconds).
const PROISP_TRANSIENT_NAME     = 'proisp_force_use_https_title';	// Name for the transient (cache entry). Stores the document title.
const PROISP_TRANSIENT_EXPIRE   = 1 * MONTH_IN_SECONDS;	// (true)	// Possible integer values: DAY_IN_SECONDS, WEEK_IN_SECONDS, MONTH_IN_SECONDS, YEAR_IN_SECONDS
// End section to be customized by PRO ISP.
// ----------------------------------------------------------------

add_filter( 'site_status_test_result', 'proisp_site_health_https_link' );			// The filter hook
function proisp_site_health_https_link( $site_health_check ) {					// The filter function, as refrenced in above 2nd argument
	// If the filtered test is not the `https_status` one, return the original result:
	if ( 'https_status' !== $site_health_check['test'] ) {
		return $site_health_check;
	}
 
	// Only add our action if the check did not pass:
	if ( 'good' !== $site_health_check['status'] ) {
		$locale   = get_user_locale();
		$url      = in_array( $locale, array_keys( PROISP_FORCE_USE_HTTPS_URLS ) ) ?
			PROISP_FORCE_USE_HTTPS_URLS[ $locale ] :
			PROISP_FORCE_USE_HTTPS_URLS[''];												// Select URL according to language
		$transient_name = PROISP_TRANSIENT_NAME . '_' . $locale;
		if ( PROISP_TRANSIENT_EXPIRE === true ) {
			delete_transient( $transient_name );											// Optional delete if expire is identical to true, for debug
		}
		$title = get_transient( $transient_name );											// Get cached title, if any unexpired. Deleted if expired.
		if ( $title ) {
			$is_trans = true;
		} else {																			// There is no unexpired transient, retrieve and save
			$is_trans = false;
			$response = wp_remote_get( $url, [
				'type' => 'HEAD',															// Only retrive the HEAD section, not BODY, to save memory space
				'timeout' => PROISP_REMOTE_GET_TIMEOUT,
			] );																			// Retrievs the HEAD response of the guide document
			$html     = wp_remote_retrieve_body( $response );					// Gets the HEAD html elements, assuming a present title element
			$doc      = new DOMDocument();
			@$doc->loadHTML( $html );
			if ( $doc ) {
				$nodes = $doc->getElementsByTagName( 'title' );					// Parses the html, find title element
				$title = $nodes && $nodes->item(0) ? $nodes->item(0)->nodeValue : false;	// Extracts the title string, if any
			}
			if ( $title ) {
				set_transient( $transient_name, $title, PROISP_TRANSIENT_EXPIRE );		// Saves the result as a transient
			}
		}
		$title = $title ?
			$title :
			__( 'Force all traffic to the webpage to use https - PRO ISP', 'proisp' );		// Default title in case a retrieve error, not found or empty
		$tip = defined( 'WP_DEBUG' ) && WP_DEBUG ?											// Add some debug data, like is_trans? ( to be show as title tooltip)
			' title="Title is' . ( $is_trans ?
				'' :
				' not' ) . ' cached."' :
			'';
		$site_health_check['actions'] .= sprintf(
			'<a href="%s" target="_blank"' . $tip .'>%s</a>',
			esc_url( $url ),
			esc_attr( $title )
		);
	}
 	return $site_health_check;	// First argument must always be retured to filter hook
}
//EOF

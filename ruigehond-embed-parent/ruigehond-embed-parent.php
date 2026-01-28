<?php
declare( strict_types=1 );
/*
Plugin Name: Ruigehond embed parent
Plugin URI: https://github.com/joerivanveen/ruigehond-embed
Description: When enabled, use the shortcode [ruigehond-embed-parent src="&lt;Iframe src&gt;"] to embed urls from sites where the full version of ruigehond-embed is installed.
Version: 1.3.0
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Author: Joeri van Veen
Author URI: https://wp-developer.eu
License: GPLv3
Text Domain: ruigehond-embed
Domain Path: /languages/
*/

defined( 'ABSPATH' ) || die();
/* this is for the parent website, belongs to ruigehond-embed: */
add_shortcode( 'ruigehond-embed-parent', 'ruigehond_embed_parent_shortcode' );
function ruigehond_embed_parent_shortcode( $attributes = [], $content = null, $short_code = 'ruigehond-embed-parent' ): string {
	if ( false === isset( $attributes['src'] ) ) {
		return esc_html__( 'Attribute src missing', 'ruigehond-embed' );
	}
	$src = $attributes['src'];
	$url = wp_parse_url( $src );
	if ( ! isset( $url['scheme'] ) || ! in_array( $url['scheme'], array( 'http', 'https' ) ) ) {
		return "Ruigehond embed: src not recognized as a valid iframe src. Use a fully qualified url.";
	}
	wp_enqueue_script( 'ruigehond015_snuggle_javascript', plugin_dir_url( __FILE__ ) . 'snuggle.js', [], '1.2.20' );

	// add the current domain to the iframe src slug
	if ( isset($attributes['domain']) && true === isset( $_SERVER['HTTP_HOST'] ) ) {
		// https://stackoverflow.com/questions/7111881/what-are-the-allowed-characters-in-a-subdomain
		$domain = str_replace( '.', '-', trim( preg_replace( '/[^a-z0-9\-\.]/', '', strtolower( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ), '-.' ) );
		if ( 0 === strpos( $domain, 'www-' ) ) {
			$domain = substr( $domain, 4 );
		}
		if ('/' === substr($src, -1) ) {
			$src = "$src$domain";
		} else {
			$src = "$src-$domain";
		}
	}

	return "<iframe style='width:100%;border:0;frame-border:0;height:100vh;' loading='eager' src='$src'></iframe>";
}

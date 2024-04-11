<?php
declare( strict_types=1 );
/*
Plugin Name: Ruigehond embed parent
Plugin URI: https://github.com/joerivanveen/ruigehond-embed
Description: When enabled, use the shortcode [ruigehond-embed-parent src="&lt;Iframe src&gt;"] to embed urls from sites where the full version of ruigehond-embed is installed.
Version: 1.0.1
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
	wp_enqueue_script( 'ruigehond015_snuggle_javascript', plugin_dir_url( __FILE__ ) . 'snuggle.js', [], '1.0.0' );

	return "<iframe style='width:100%;border:0;frame-border:0;height:100vh;' loading='eager' src='$src'></iframe>";
}

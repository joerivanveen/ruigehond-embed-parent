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
// This is plugin nr. 18 by Ruige hond. It identifies as: ruigehond018.
const RUIGEHOND018_VERSION = '1.3.0';
/* this is for the parent website, belongs to ruigehond-embed: */
add_shortcode( 'ruigehond-embed-parent', 'ruigehond_embed_parent_shortcode' );
function ruigehond_embed_parent_shortcode( $attributes = [], $content = null, $short_code = 'ruigehond-embed-parent' ): string {
	if ( false === isset( $attributes['src'] ) ) {
		$vars = get_option( 'ruigehond018' );
		if (isset ($vars['client_side']) && true === $vars['client_side']) {
			wp_enqueue_script( 'ruigehond015_snuggle_javascript', plugin_dir_url( __FILE__ ) . 'snuggle.js', [], RUIGEHOND018_VERSION );
			wp_enqueue_script( 'ruigehond018_handle_iframe_src', plugin_dir_url( __FILE__ ) . 'iframe-src.js', [], RUIGEHOND018_VERSION );
			wp_localize_script( 'ruigehond018_handle_iframe_src', 'ruigehond018_domains', $vars['domains'] ?? array() );
			return "<iframe style='width:100%;border:0;frame-border:0;height:100vh;' id='ruigehond018-iframe' src=''></iframe>";
		}
		// add the current domain to the iframe src slug
		if ( true === isset( $_SERVER['HTTP_HOST'] ) ) {
			$domain = ruigehond018_sanitize_domain( $_SERVER['HTTP_HOST'] );
		}
		if ( ! isset( $domain ) ) {
			return 'Ruigehond embed: ' . esc_html__( 'HTTP_HOST is missing, cannot proceed.', 'ruigehond-embed-parent' );
		}
		// find the current src path from settings
		if ( ! is_array( $vars ) || ! isset( $vars['domains'] ) ) {
			return 'Ruigehond embed: ' . esc_html__( 'Attribute src missing and no settings found.', 'ruigehond-embed-parent' );
		}
		if ( ! isset( $vars['domains'][ $domain ] ) ) {
			return "Ruigehond embed $domain: " . esc_html__( 'Domain not found in settings.', 'ruigehond-embed-parent' );
		}
		$attributes['src'] = $vars['domains'][ $domain ];
	}
	$src = $attributes['src'];
	$url = wp_parse_url( $src );
	if ( ! isset( $url['scheme'] ) || ! in_array( $url['scheme'], array( 'http', 'https' ) ) ) {
		return 'Ruigehond embed: ' . esc_html__( 'src not recognized as a valid iframe src. Use a fully qualified url.', 'ruigehond-embed-parent' );
	}
	wp_enqueue_script( 'ruigehond015_snuggle_javascript', plugin_dir_url( __FILE__ ) . 'snuggle.js', [], RUIGEHOND018_VERSION );

	return "<!--Embed: $domain--><iframe style='width:100%;border:0;frame-border:0;height:100vh;' loading='eager' src='$src'></iframe>";
}

add_action( 'init', 'ruigehond018_run' );

function ruigehond018_sanitize_domain( string $domain ) {
	if (false !== strpos($domain, '://')) {
		$parts  = wp_parse_url( $domain );
		if ( false === $parts || ! isset( $parts['host'] ) ) {
			return '';
		}
		$domain = $parts['host'];
	}
	// https://stackoverflow.com/questions/7111881/what-are-the-allowed-characters-in-a-subdomain
	$domain = trim( preg_replace( '/[^a-z0-9\-\.]/', '', strtolower( wp_unslash( $domain ) ) ), '-.' );
	if ( 0 === strpos( $domain, 'www.' ) ) {
		$domain = substr( $domain, 4 );
	}

	return $domain;
}

/**
 * Added section for settings below, @since 1.3.0
 */


function ruigehond018_run(): void {
	if ( is_admin() ) {
		load_plugin_textdomain( 'ruigehond-embed', '', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		add_action( 'admin_init', 'ruigehond018_settings' );
		add_action( 'admin_menu', 'ruigehond018_menuitem' );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ruigehond018_settingslink' ); // settings link on plugins page
	}
}

function ruigehond018_menuitem(): void {
	add_submenu_page(
		'', // this will hide the settings page in the "settings" menu
		'Ruigehond Embed Parent Settings',
		'Ruigehond Embed Parent',
		'administrator',
		'ruigehond-embed-parent',
		'ruigehond018_settingspage'
	);
}

function ruigehond018_settingspage(): void {
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}
	wp_enqueue_style( 'ruigehond018_admin_stylesheet', plugin_dir_url( __FILE__ ) . 'admin.css', [], RUIGEHOND018_VERSION );
	echo '<div class="wrap ruigehond018"><h1>Ruigehond Embed Parent</h1><p>';
	$str = esc_html__( 'Here you can map domains to Iframe src-s that will be embedded when you use the shortcode without src, like this: %s.', 'ruigehond-embed-parent' );
	if ( false === strpos( $str, '%s' ) ) {
		// translators messed up
		$str = 'Here you can map domains to iframe urls that will be embedded when you use the shortcode without src, like this: %s.';
	}
	printf( $str, '[ruigehond-embed-parent]' );
	echo '</p><form action="options.php" method="post" id="ruigehond018-settings-form" autocomplete="off">';
	// output security fields for the registered setting
	settings_fields( 'ruigehond018' );
	// output setting sections and their fields
	do_settings_sections( 'ruigehond018' );
	// output save settings button
	submit_button( esc_html__( 'Save Settings', 'ruigehond-embed-parent' ) );
	echo '</form></div>';
}

function ruigehond018_settings(): void {
	register_setting( 'ruigehond018', 'ruigehond018', 'ruigehond018_settings_validate' );
	// don’t bother with all this if we’re not even on the settings page
	if ( false === isset( $_GET['page'] ) || 'ruigehond-embed-parent' !== $_GET['page'] ) {
		return;
	}

	$vars = get_option( 'ruigehond018' );

	echo "<!--Ruigehond embed parent vars:\n";
	var_dump( $vars );
	echo '-->';

	// register a new section in the page
	add_settings_section(
		'ruigehond_embed_parent_settings', // section id
		esc_html__( 'Settings', 'ruigehond-embed-parent' ), // title
		function () {
			echo '<p>';
			echo esc_html__( 'By default the iframe is built on the server. Check this box to manage the iframe on the client.', 'ruigehond-embed-parent' );
			echo '</p>';
		}, //callback
		'ruigehond018' // page
	);

	// normalise our array for settings purposes
	if ( ! is_array( $vars ) ) {
		$vars = array();
	}
	if ( ! isset( $vars['domains'] ) ) {
		$vars['domains'] = array();
	}
	if ( ! is_array( $vars['srcs'] ) ) {
		$vars['srcs'] = array();
	}
	sort( $vars['srcs'] );
	ksort( $vars['domains'] );

	add_settings_field(
		"ruigehond018_client_side",
		__( 'Client side', 'ruigehond-embed-parent' ),
		function ( $args ) {
			$checked      = $args['checked'];
			$setting_name = $args['setting_name'];
			// make checkbox that transmits 1 or 0, depending on status
			echo '<label><input type="hidden" name="ruigehond018[';
			echo esc_attr( $setting_name );
			echo ']" value="';
			echo ( true === $checked ) ? '1' : '0';
			echo '"><input type="checkbox"';
			if ( true === $checked ) {
				echo ' checked="checked"';
			}
			echo ' onclick="this.previousSibling.value=1-this.previousSibling.value"/>';
			echo esc_html( $args['label_for'] );
			echo '</label><br>';
		},
		'ruigehond018',
		'ruigehond_embed_parent_settings',
		array(
			'checked' => isset($vars['client_side']) && true === $vars['client_side'],
			'setting_name' => 'client_side',
			'label_for' => __( 'Use client side embedding.', 'ruigehond-embed-parent'),
		)
	);

	// register a new section in the page
	add_settings_section(
		'ruigehond_embed_parent', // section id
		esc_html__( 'Embeds with their domains', 'ruigehond-embed-parent' ), // title
		function () {
			echo '<p>';
			echo esc_html__( 'Each Iframe src you want to use can be summoned by several urls, that you can type in the textarea (one per line).', 'ruigehond-embed-parent' );
			echo '<br>';
			echo esc_html__( 'To add an entry, paste your Iframe src in the lowest entry.', 'ruigehond-embed-parent' );
			echo ' ';
			echo esc_html__( 'To delete, empty the Iframe src and hit Save. This can not be undone!', 'ruigehond-embed-parent' );
			echo '</p>';
		}, //callback
		'ruigehond018' // page
	);

	$output_domains_field = function ( array $domains, int $index ) {
		// output textarea with domains
		add_settings_field(
			"ruigehond018_domains_$index",
			__( 'Domains (one per line)', 'ruigehond-embed-parent' ),
			function ( $args ) {
				echo '<textarea name="ruigehond018[', (int) $args['index'], '][domains]" class="domains">';
				echo esc_html( $args['value'] );
				echo '</textarea>';
			},
			'ruigehond018',
			'ruigehond_embed_parent',
			array(
				'value' => $domains ? implode( "\n", $domains ) : '',
				'index' => $index,
			)
		);
	};
	$output_src_field     = function ( string $src, int $index ) {
		add_settings_field(
			"ruigehond018_src_$index",
			'Iframe src',
			function ( $args ) {
				echo '<input type="text" name="ruigehond018[', (int) $args['index'], '][src]" value="';
				echo esc_html( $args['value'] );
				echo '" class="regular-text src"/>';
			},
			'ruigehond018',
			'ruigehond_embed_parent',
			array(
				'value' => $src,
				'index' => $index,
			)
		);
	};
	foreach ( $vars['srcs'] as $index => $src ) {
		$current_domains = array();
		$output_src_field( $src, $index );
		foreach ( $vars['domains'] as $domain => $domain_src ) {
			if ( $domain_src !== $src ) {
				continue;
			}
			$current_domains[] = $domain;
		}
		$output_domains_field( $current_domains, $index );
	}
	// new src:
	$output_src_field( '', - 1 );
}

function ruigehond018_settings_validate( $input ): array {
	$options = get_option( 'ruigehond018' );

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	if ( false === is_array( $input ) ) {
		return $options;
	}

	if (isset ($input['client_side'])) {
		$options['client_side'] = '1' === $input['client_side'];
	}

	$options['domains'] = array(); // <- we build it entirely anew with the posted data
	$options['srcs']    = array();

	foreach ( array_values( $input ) as $index => $pair ) {
		if ( ! isset( $pair['src'] ) || '' === $pair['src'] ) {
			continue;
		}
		if ( ! isset( $pair['domains'] ) ) {
			$pair['domains'] = array();
		} else {
			$pair['domains'] = explode( "\n", $pair['domains'] );
		}

		foreach ( $pair['domains'] as $i => $domain ) {
			$domain = ruigehond018_sanitize_domain( $domain );
			if ( '' === $domain ) {
				continue;
			}
			$options['domains'][ $domain ] = trim( sanitize_text_field( $pair['src'] ) );
		}
		$options['srcs'][ $index ] = $pair['src'];
	}

	return $options;
}

function ruigehond018_settingslink( $links ): array {
	$url           = get_admin_url();
	$link_text     = esc_html__( 'Settings', 'ruigehond-embed-parent' );
	$settings_link = "<a href=\"{$url}options-general.php?page=ruigehond-embed-parent\">$link_text</a>";
	array_unshift( $links, $settings_link );

	return $links;
}

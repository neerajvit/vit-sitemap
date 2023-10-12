<?php
/**
 * Plugin Name: VIT Sitemap
 * Plugin URI: -- 
 * Description: VIT Sitemap plugin allows you to effortlessly create both HTML and XML sitemaps.
 * Version: 1.0
 * Author: Vidushi Infotech
 * Author URI: https://vidushiinfotech.com/
 * Text Domain: --
 * License: --
 *
 * @since 0.1
 *
 * @package sitemap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define( 'SITEMAP_PLUGIN_FILE', __FILE__ );

/**
 * Loads the action plugin
 */
require_once dirname( SITEMAP_PLUGIN_FILE ) . '/includes/sitemap_Main.php';

sitemap_Main::instance();

register_activation_hook( SITEMAP_PLUGIN_FILE, array( 'sitemap_Main', 'activate' ) );

register_deactivation_hook( SITEMAP_PLUGIN_FILE, array( 'sitemap_Main', 'deactivate' ) );

register_uninstall_hook( SITEMAP_PLUGIN_FILE, array( 'sitemap_Main', 'uninstall' ) ); 

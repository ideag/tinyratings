<?php
/**
 * Plain and simple ratings plugin. Rate anything.
 *
 * @since 0.1.0
 * @package TinyRatings
 *
 * @wordpress-plugin
 * Plugin Name: tinyRatings
 * Plugin URI: http://arunas.co
 * Description: Plain and simple ratings plugin. Rate anything.
 * Version: 0.1.4
 * Author: Arūnas Liuiza
 * Author URI: http://arunas.co
 * Text Domain: tinyratings
 */

// TO DO: better fields for the widget
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( __DIR__ . '/class-tinyratings.php' );
add_action( 'plugins_loaded', array( 'TinyRatings', 'init' ) );

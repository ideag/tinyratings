<?php
/**
 * Plain and simple ratings plugin. Rate anything.
 *
 * LlONG DESCRITOD.
 *
 * @since 0.1.0
 * @package TinyRatings
 *
 * @wordpress-plugin
 * Plugin Name: tinyRatings
 * Plugin URI: http://arunas.co
 * Description: Plain and simple ratings plugin. Rate anything.
 * Version: 0.1.1
 * Author: Arūnas Liuiza
 * Author URI: http://arunas.co
 * Text Domain: tinyratings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( __DIR__ . '/class-tinyratings.php' );
add_action( 'plugins_loaded', array( 'TinyRatings', 'init' ) );

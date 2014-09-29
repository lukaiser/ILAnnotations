<?php
/**
 * @package ILAnnotations 
 */
/*
Plugin Name: ILAnnotations
Plugin URI: https://github.com/lukaiser/ILAnnotations
Description: Adds the Annotation functionality to the existing commenting system of Wordpress
Version: 0.9.0
Author: Lukas Kaiser
Author URI: http://emperor.ch
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'ILANNOTATIONS_VERSION', '0.9.0' );
define( 'ILANNOTATIONS__MINIMUM_WP_VERSION', '3.0' );
define( 'ILANNOTATIONS__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ILANNOTATIONS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( ILANNOTATIONS__PLUGIN_DIR . 'class.ilannotations.php' );

add_action( 'init', array( 'ILAnnotations', 'init' ) );
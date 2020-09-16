<?php

/**
 * Plugin Name: Hierarchical Posts
 * Description: A small plugin to allow posts to be hierarchical.
 * Version: 1.0.0
 * Plugin URI: https://skape.co/
 * Author: Skape Collective
 * Author URI: https://skape.co/
 * Text Domain: hposts
 * Network: false
 * Requires at least: 5.0.0
 * Requires PHP: 7.2
 */

require_once plugin_dir_path( __FILE__ ) . 'source/Autoload.php';
$autoload = new HPosts\Autoload( plugin_dir_path( __FILE__ ) );

$autoload->loadArray( [
	'HPosts\\' => 'source'
], 'psr-4' );

// Register global constants
HPosts\Utilities\Constants::set( 'FILE', __FILE__ );
HPosts\Utilities\Constants::set( 'DEBUG', defined( 'WP_DEBUG' ) && WP_DEBUG );
HPosts\Utilities\Constants::set( 'VERSION', '1.0.0' );
HPosts\Utilities\Constants::set( 'PATH', plugin_dir_path( __FILE__ ) );
HPosts\Utilities\Constants::set( 'URL', plugin_dir_url( __FILE__ ) );
HPosts\Utilities\Constants::set( 'BASENAME', plugin_basename( __FILE__ ) );

// Hardcoded settings
HPosts\Utilities\Constants::set( 'PERMALINKS', true );
HPosts\Utilities\Constants::set( 'TYPES', [ 'post' ] );

// Initialisers
HPosts\Handler::init();


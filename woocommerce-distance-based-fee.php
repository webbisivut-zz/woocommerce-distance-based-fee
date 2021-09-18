<?php
/*
 * Plugin Name: WebData Distance Based Fee for WooCommerce
 * Version: 1.1.15
 * Plugin URI: https://www.web-data.online/
 * Description: This plugin adds fee based on distance
 * Author: web-data.online
 * Requires at least: 4.0
 * Tested up to: 5.7
 *
 * Text Domain: woocommerce-distance-based-fee
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author web-data.io
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-woocommerce-distance-based-fee.php' );
require_once( 'includes/class-woocommerce-distance-based-fee-settings.php' );
require_once( 'includes/class-woocommerce-distance-based-fee-functions.php' );

// Load plugin libraries
require_once( 'includes/lib/class-woocommerce-distance-based-fee-admin-api.php' );

/**
 * Returns the main instance of WooCommerce_Distance_Based_Fee to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WooCommerce_Distance_Based_Fee
 */
function WooCommerce_Distance_Based_Fee () {
	$instance = WooCommerce_Distance_Based_Fee::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WooCommerce_Distance_Based_Fee_Settings::instance( $instance );
	}

	return $instance;
}

WooCommerce_Distance_Based_Fee();

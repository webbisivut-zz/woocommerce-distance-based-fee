<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check selected shipping method
 * Return string
 */
function checkChosenMethod() {
	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
	if(isset($chosen_methods[0])) {
		$chosen_method = $chosen_methods[0];

		$chosen_method = preg_replace('/[0-9:]+/', '', $chosen_method);

		return $chosen_method;
	}

	return;
}

/**
 * Send data to Google API
 * Return array
 */
function sendDataToAPI($setUnits, $origin, $destination, $apiKey) {
	$header = array();
	$header[] = 'Content-length: 0';
	$header[] = 'Content-type: application/json';
																												
	$service_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?mode=driving&language=en-GB' . $setUnits . '&origins=' . $origin . '&destinations=' . $destination . '&key=' . $apiKey;

	$response = wp_remote_get($service_url);
	$body = wp_remote_retrieve_body( $response );
	$bodyDecoded = json_decode($body, true);

	return $bodyDecoded;
}

/**
 * Add fee on article specifics
 * @param WC_Cart $cart
 */
function add_woocommerce_distance_fee_fees(){
	
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) 
	return;

	global $woocommerce;

	$chosen_method = checkChosenMethod();

	$apiKey = esc_attr( get_option('wc_distance_fee_google_api_key') );
	$feeName = esc_attr( get_option('wc_distance_fee_fee_name') );
	$divider = floatval(esc_attr( get_option('wc_distance_fee_divider') ));
	$price = floatval(esc_attr( get_option('wc_distance_fee_price') ));
	$units = esc_attr( get_option('wc_distance_fee_units') );
	$methods = get_option('wc_distance_fee_methods');
	$tax = esc_attr( get_option('wc_distance_fee_taxable') );
	$minimum_distance = esc_attr( get_option('wc_distance_fee_minimum_distance') );
	$maximum_distance = esc_attr( get_option('wc_distance_fee_maximum_distance') );
	$minimum_cart_price = esc_attr( get_option('wc_distance_fee_minimum_cart_price') );
	$fixed_fee = esc_attr( get_option('wc_distance_fee_fixed_fee') );
	$disable_shippings = esc_attr( get_option('wc_distance_fee_disable_shippings') );
	$disable_virtual = esc_attr( get_option('wc_distance_fee_disable_virtual') );

	$cart_total_price = floatval( preg_replace( '#[^\d.]#', '', $woocommerce->cart->get_cart_total() ) );

	$add_fee = true;

	if($disable_virtual == 'yes') {
		$items = $woocommerce->cart->get_cart();

		foreach($items as $item => $values) { 
			$terms = get_the_terms($values['data']->get_id(), 'product_type');
			$_product = wc_get_product( $values['data']->get_id() );

			if ($_product->is_virtual('yes')) { 
				$add_fee = false;
			}

		}
	} elseif($disable_virtual == 'all_virtual') {
		$virtualsArr = array();

		$items = $woocommerce->cart->get_cart();

		foreach($items as $item => $values) { 
			$terms = get_the_terms($values['data']->get_id(), 'product_type');
			$_product = wc_get_product( $values['data']->get_id() );

			if ($_product->is_virtual('yes')) { 
				array_push($virtualsArr, 'virtual product detected');
			}

		}

		$itemsSize = sizeof($items);
		$virtualsSize = sizeof($virtualsArr);

		if($itemsSize == $virtualsSize) {
			$add_fee = false;
		}
	}

	$calculatedFee = 0;

	if($units == 'ml') {
		$setUnits = '&units=imperial';
	} else {
		$setUnits = '&units=metric';
	}

	if( $tax == 'yes' ) {
		$addTaxes = true;
	} else {
		$addTaxes = false;
	}

	$originCity = esc_attr(get_option('woocommerce_store_city'));
	$originAddress = esc_attr(get_option('woocommerce_store_address'));
	$originPostcode = esc_attr(get_option('woocommerce_store_postcode'));

	$originAdressSettings = esc_attr(get_option('wc_distance_fee_origin_address'));
	$originZipSettings = esc_attr(get_option('wc_distance_fee_origin_zip'));
	$originCitySettings = esc_attr(get_option('wc_distance_fee_origin_city'));

	if($originAdressSettings != '') {
		$originAddress = $originAdressSettings;
	}

	if($originZipSettings != '') {
		$originPostcode = $originZipSettings;
	}

	if($originCitySettings != '') {
		$originCity = $originCitySettings;
	}

	$originCity = apply_filters( 'dbf_origin_city_filter', $originCity);
	$originPostcode = apply_filters( 'dbf_origin_zip_filter', $originPostcode);
	$originAddress = apply_filters( 'dbf_origin_address_filter', $originAddress);

	if(!file_exists(plugin_dir_path(__FILE__).'../logs/')) {
		mkdir(plugin_dir_path(__FILE__).'../logs/');
	}

	if(!file_exists(plugin_dir_path(__FILE__).'../logs/debug.log')) {
		$log_file = fopen(plugin_dir_path(__FILE__).'../logs/debug.log', 'w');
		fwrite($log_file, '');
		fclose($log_file);
	}

	$date = date("D M j G:i:s T Y");

	if($originCity == '' || $originCity == null) {
		$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
		$message = $date . ': ' . 'Error. Store city is missing. Please enter your city to the WooCommerce settings at: WooCommerce - Settings - General or at the plugin settings at: Settings - Distance Based Fee.'.PHP_EOL;
		error_log($message, 3, $pluginlog);
	}

	if($originAddress == '' || $originAddress == null) {
		$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
		$message = $date . ': ' . 'Error. Store address is missing. Please enter your address to the WooCommerce settings at: WooCommerce - Settings - General or at the plugin settings at: Settings - Distance Based Fee.'.PHP_EOL;
		error_log($message, 3, $pluginlog);
	}

	if($originPostcode == '' || $originPostcode == null) {
		$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
		$message = $date . ': ' . 'Error. Store zip code is missing. Please enter your zip code to the WooCommerce settings at: WooCommerce - Settings - General or at the plugin settings at: Settings - Distance Based Fee.'.PHP_EOL;
		error_log($message, 3, $pluginlog);
	}

	$to_address = esc_attr(get_option('wc_distance_fee_to_address'));

	if($to_address == 'billing') {
		$destinationCity = WC()->customer->get_billing_city();
		$destinationAddress = WC()->customer->get_billing_address();
		$destinationPostcode = WC()->customer->get_billing_postcode();
	} else {
		$destinationCity = WC()->customer->get_shipping_city();
		$destinationAddress = WC()->customer->get_shipping_address();
		$destinationPostcode = WC()->customer->get_shipping_postcode();
	}

	if($destinationCity !== '' && $destinationPostcode !== '') {
		$origin = urlencode($originAddress . ',' . $originPostcode . ' ' .$originCity);
		$destination = urlencode($destinationAddress . ',' . $destinationPostcode . ' ' . $destinationCity);

		$response = sendDataToAPI($setUnits, $origin, $destination, $apiKey); 

		if(isset($response['error_message'])) {
			error_log($response['error_message']);

			$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
			$message = $date . ': ' . $response['error_message'].PHP_EOL;
			error_log($message, 3, $pluginlog);
		}

		if(!isset($response['rows'][0])) {
			$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
			$message = $date . ': ' . json_encode($response) .PHP_EOL;
			error_log($message, 3, $pluginlog);
		}

		if(!isset($response['rows'][0]['elements'][0])) {
			$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
			$message = $date . ': ' . json_encode($response) .PHP_EOL;
			error_log($message, 3, $pluginlog);
		}

		$kiloMeters = 0;
		$setDistance = 0;
		
		if(isset($response['rows'][0]) && $response['rows'][0]['elements'][0]['status'] !== 'NOT_FOUND' && $response['rows'][0]['elements'][0]['status'] !== 'ZERO_RESULTS') {
			if(isset($response['rows'][0]['elements'][0]['distance'])) {
				$meters = $response['rows'][0]['elements'][0]['distance']['value'];
				$kiloMeters = ($meters / 1000);
				$miles = $meters * 0.00062137119224;

				if($units == 'ml') {
					$setDistance = $miles;
				} else {
					$setDistance = $kiloMeters;
				}

				if($minimum_distance != '' && (float)$minimum_distance >= $setDistance) {
					$calculatedFee = 0;
				} else {
					$calculatedFee = ($setDistance / $divider) * $price;
				}

				if($maximum_distance != '' && (float)$maximum_distance <= $setDistance OR $minimum_distance != '' && (float)$minimum_distance >= $setDistance OR $minimum_cart_price != '' && (float)$minimum_cart_price > $cart_total_price) { 
					if($disable_shippings == 'fixed' && $fixed_fee != '') {
						$calculatedFee = (float)$fixed_fee;
					} else {
						$calculatedFee = 0;
					}
				} else {
					$calculatedFee = ($setDistance / $divider) * $price;
				}
			} else {
				$calculatedFee = 0;
			}

			$calculatedFee = apply_filters('dbf_calculated_fee', $calculatedFee, $setDistance, $divider, $price);
			
		}

		if ( $calculatedFee > 0 && in_array( $chosen_method, $methods ) && $add_fee ) {
			WC()->cart->add_fee( $feeName, $calculatedFee, $addTaxes, '' );
		}
	}
	
}

add_action( 'woocommerce_cart_calculate_fees' , 'add_woocommerce_distance_fee_fees' );
add_action( 'woocommerce_after_cart_item_quantity_update', 'add_woocommerce_distance_fee_fees' );

/**
 * Hide shipping methods if destination cannot be found
 * @param WC_Cart $cart
 */
function hide_show_fee_based_shipping( $rates, $package ) {
	global $woocommerce;

	$originCity = esc_attr(get_option('woocommerce_store_city'));
	$originAddress = esc_attr(get_option('woocommerce_store_address'));
	$originPostcode = esc_attr(get_option('woocommerce_store_postcode'));
	
	$originAdressSettings = esc_attr(get_option('wc_distance_fee_origin_address'));
	$originZipSettings = esc_attr(get_option('wc_distance_fee_origin_zip'));
	$originCitySettings = esc_attr(get_option('wc_distance_fee_origin_city'));
	
	$to_address = esc_attr(get_option('wc_distance_fee_to_address'));

	if($to_address == 'billing') {
		$destinationCity = WC()->customer->get_billing_city();
		$destinationAddress = WC()->customer->get_billing_address();
		$destinationPostcode = WC()->customer->get_billing_postcode();
	} else {
		$destinationCity = WC()->customer->get_shipping_city();
		$destinationAddress = WC()->customer->get_shipping_address();
		$destinationPostcode = WC()->customer->get_shipping_postcode();
	}

	if($originAdressSettings != '') {
		$originAddress = $originAdressSettings;
	}

	if($originZipSettings != '') {
		$originPostcode = $originZipSettings;
	}

	if($originCitySettings != '') {
		$originCity = $originCitySettings;
	}

	$originCity = apply_filters( 'dbf_origin_city_filter', $originCity);
	$originPostcode = apply_filters( 'dbf_origin_zip_filter', $originPostcode);
	$originAddress = apply_filters( 'dbf_origin_address_filter', $originAddress);

	if($destinationCity == '' || $destinationPostcode == '') {
		return $rates;
	}

	$origin = urlencode($originAddress . ',' . $originPostcode . ' ' .$originCity);
	$destination = urlencode($destinationAddress . ',' . $destinationPostcode . ' ' . $destinationCity);

	$apiKey = esc_attr( get_option('wc_distance_fee_google_api_key') );
	$units = esc_attr( get_option('wc_distance_fee_units') );
	$maximum_distance = esc_attr( get_option('wc_distance_fee_maximum_distance') );
	$minimum_cart_price = esc_attr( get_option('wc_distance_fee_minimum_cart_price') );
	$disable_shippings = esc_attr( get_option('wc_distance_fee_disable_shippings') );
	$fixed_fee = esc_attr( get_option('wc_distance_fee_fixed_fee') );

	if($disable_shippings == 'not_hide') {
		return $rates;
	}

	$cart_total_price = floatval( preg_replace( '#[^\d.]#', '', $woocommerce->cart->get_cart_total() ) );

	if($units == 'ml') {
		$setUnits = '&units=imperial';
	} else {
		$setUnits = '&units=metric';
	}

	$methods = get_option('wc_distance_fee_methods');
	$chosen_method = checkChosenMethod();

	$response = sendDataToAPI($setUnits, $origin, $destination, $apiKey);

	$calculatedFee = 0;

	if(!file_exists(plugin_dir_path(__FILE__).'../logs/')) {
		mkdir(plugin_dir_path(__FILE__).'../logs/');
	}

	if(!file_exists(plugin_dir_path(__FILE__).'../logs/debug.log')) {
		$log_file = fopen(plugin_dir_path(__FILE__).'../logs/debug.log', 'w');
		fwrite($log_file, '');
		fclose($log_file);
	}

	$date = date("D M j G:i:s T Y");

	if(isset($response['error_message'])) {
		error_log($response['error_message']);

		$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
		$message = $date . ': ' . $response['error_message'].PHP_EOL;
		error_log($message, 3, $pluginlog);
	}

	if(!isset($response['rows'][0])) {
		$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
		$message = $date . ': ' . json_encode($response) .PHP_EOL;
		error_log($message, 3, $pluginlog);
	}

	if(!isset($response['rows'][0]['elements'][0])) {
		$pluginlog = plugin_dir_path(__FILE__).'../logs/debug.log';
		$message = $date . ': ' . json_encode($response) .PHP_EOL;
		error_log($message, 3, $pluginlog);
	}

	if(isset($response['rows'][0]) && $response['rows'][0]['elements'][0]['status'] == 'NOT_FOUND' && $response['rows'][0]['elements'][0]['status'] !== 'ZERO_RESULTS') {
		if($disable_shippings == 'hide' OR $disable_shippings == '') {
			$new_rates = array();

			foreach ( $rates as $rate_id => $rate ) {

				if ( !in_array($rate->method_id, $methods) ) {
					$new_rates[ $rate_id ] = $rate;
				}
			}
			
			return $new_rates;
		} else {
			return $rates;
		}

	} else  {
		if(!isset($response['rows'][0]['elements'][0]['distance'])) return $rates;
		
		$meters = $response['rows'][0]['elements'][0]['distance']['value'];
		$kiloMeters = ($meters / 1000);
		$miles = $meters * 0.00062137119224;

		if($units == 'ml') {
			$setDistance = $miles;
		} else {
			$setDistance = $kiloMeters;
		}

		if($maximum_distance != '' && (float)$maximum_distance <= $setDistance || $minimum_cart_price != '' && $minimum_cart_price > $cart_total_price) {
			if($disable_shippings == 'hide' OR $disable_shippings == '' OR $disable_shippings == 'fixed' && $fixed_fee == '') {
				$new_rates = array();

				foreach ( $rates as $rate_id => $rate ) {
					if ( !in_array($rate->method_id, $methods) ) {
						$new_rates[ $rate_id ] = $rate;
					}
				}
				
				return $new_rates;
			} else {
				return $rates;
			}
		} else {
			return $rates;
		}
	}
}

add_filter( 'woocommerce_package_rates', 'hide_show_fee_based_shipping' , 100, 2 );
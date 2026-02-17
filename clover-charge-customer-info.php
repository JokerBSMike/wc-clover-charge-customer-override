<?php
/**
 * Plugin Name: Clover Charge Customer Info (Override)
 * Description: Injects WooCommerce billing customer info into Clover /v1/charges requests for the First Data/Clover gateway.
 * Author: Joker
 */

defined('ABSPATH') || exit;

/**
 * SkyVerge API filter format: wc_{api_id}_http_request_args
 * For Clover CC gateway in this plugin, api_id = first_data_clover_credit_card
 */
add_filter('wc_first_data_clover_credit_card_http_request_args', function ($args, $api) {

	// Only touch JSON bodies
	if (empty($args['body']) || !is_string($args['body'])) {
		return $args;
	}

	// Make sure we’re only modifying Clover "create charge" calls
	// We check the request path if available.
	if (is_object($api) && method_exists($api, 'get_request')) {
		$request = $api->get_request();

		if (is_object($request) && method_exists($request, 'get_path')) {
			$path = $request->get_path();

			// Only inject for v1/charges (create charge/auth)
			if ($path !== 'v1/charges') {
				return $args;
			}
		}
	}

	$data = json_decode($args['body'], true);

	// If body isn’t valid JSON, bail
	if (!is_array($data)) {
		return $args;
	}

	// If customer already present, don’t stomp it
	if (!empty($data['customer']) && is_array($data['customer'])) {
		return $args;
	}

	// Best way to get the order: Clover API stores it on the API instance
	$order = null;
	if (is_object($api) && method_exists($api, 'get_order')) {
		$order = $api->get_order();
	}

	if (!$order || !is_object($order) || !method_exists($order, 'get_billing_first_name')) {
		return $args;
	}

	$customer = [
		'first_name' => (string) $order->get_billing_first_name(),
		'last_name'  => (string) $order->get_billing_last_name(),
		'email'      => (string) $order->get_billing_email(),
	];

	$phone = (string) $order->get_billing_phone();
	if ($phone !== '') {
		$customer['phone'] = $phone;
	}

	// Don’t send empty names if checkout had none
	$customer = array_filter($customer, static function ($v) {
		return $v !== null && $v !== '';
	});

	if (!empty($customer)) {
		$data['customer'] = $customer;
		$args['body'] = wp_json_encode($data);
	}

	return $args;

}, 10, 2);
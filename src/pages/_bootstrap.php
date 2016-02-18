<?php
	require('_env.php');
	require(WP_PATH.'/wp-load.php');

	// Locale to use (mainly for dates)
	setlocale(LC_ALL, LOCALE);

	// Get the avatar's URL (check the URL first to provide an SVG fallback — not possible with gravatar)
	function avatar_url() {
		global $current_user;

		// Simplified version of https://gist.github.com/justinph/5197810
		$hash = md5(strtolower(trim($current_user->user_email)));
		$url  = 'http://www.gravatar.com/avatar/'.$hash.'?s=90';
		$code = wp_cache_get($hash);

		if (!$code) {
			$response = wp_remote_head($url.'&d=400');
			$code     = is_wp_error($response) ? '400' : $response['response']['code'];
			wp_cache_set($hash, $code, null, 60 * 5);
		}

		return $code == '200' ? $url : '/img/profile.svg';
	}

	// Get all meta related to the given user
	function get_all_user_meta($id) {
		$meta     = get_user_meta($id);
		$invoices = isset($meta['invoices']) ? $meta['invoices'] : array(); // Makes sure we have an invoices array

		$meta = array_map(function($value) { return count($value) == 1 ? $value[0] : $value; }, $meta);
		$meta['invoices'] = array_map(function($value) { return json_decode($value, true); }, $invoices);

		return $meta;
	}

	global $PLANS;
	$PLANS = array(
		'jouriste' => array(
			'name'     => 'Jouriste',
			'price'    => 9,
			'duration' => '1 month'
		),

		'jouriste-cash' => array(
			'name'     => 'Jouriste cash',
			'price'    => 90,
			'duration' => '1 year'
		),

		'jouriste-desargente' => array(
			'name'     => 'Jouriste désargenté',
			'price'    => 5,
			'duration' => '1 month'
		)
	);

	// In "pilot" mode monthly subscription cost 1€
	$PLANS['jouriste']['price'] = $PLANS['jouriste-desargente']['price'] = 1;
?>
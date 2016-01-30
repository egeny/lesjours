<?php
	set_include_path(dirname(__FILE__).'/../../lj');
	require('wp-load.php');

	// Locale to use (mainly for dates)
	setlocale(LC_ALL, 'fr_FR');

	// be2bill credentials (payment service)
	define('BE2BILL_IDENTIFIER', 'LES JOURS TEST');
	define('BE2BILL_PASSWORD',   '<P?[E}D4pRBGl%qO');

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
?>
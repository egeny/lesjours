<?php
require('_bootstrap.php');
global $wpdb;

// Set a time limit anyway
set_time_limit(10 * 60); // 10 minutes

$today = date('Y-m-d');

// Prepare and launch the query
$conditions = array(
	'meta_key = "expire"  AND meta_value  = "'.$today.'"',
	'meta_key = "payment" AND meta_value  = "card"',
	'meta_key = "plan"    AND meta_value != ""'
);

$conditions = array_map(function($value) { return 'user_id IN (SELECT user_id FROM '.$wpdb->usermeta.' WHERE '.$value.')'; }, $conditions);
$results    = $wpdb->get_col('SELECT user_id FROM '.$wpdb->usermeta.' WHERE '.implode(' AND ', $conditions).' GROUP BY user_id');

// Prepare some variable to use cURL asynchronously
$mh      = curl_multi_init();
$queue   = array();
$running = null;

foreach ($results as $id) {
	$user = get_user_data($id); // Get the user's object to retrieve its mail
	$meta = get_all_user_meta($id);

	// Prepare the payload
	$payload = array(
		'alias'           => $meta['alias'],
		'aliasmode'       => 'subscription',
		'amount'          => $PLANS[$meta['plan']]['price'] * 100,
		'clientemail'     => $user->user_email,
		'clientident'     => $id,
		'clientip'        => '127.0.0.1',
		//'clientreferrer'  => $_SERVER['HTTP_REFERER'],
		'clientuseragent' => '',
		'description'     => 'Abonnement',
		'identifier'      => BE2BILL_IDENTIFIER,
		'operationtype'   => 'payment',
		'orderid'         => $today.'-'.$id,
		'version'         => '2.0'
	);

	// Uppercase the payload's keys, compute the hash and prepare for POSTing
	$payload = array_change_key_case($payload, CASE_UPPER);
	$payload['HASH'] = signature($payload);
	$payload = http_build_query(array(
		'method' => 'payment',
		'params' => $payload
	));

	$queue[] = $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,            BE2BILL_REST_URL);
	curl_setopt($ch, CURLOPT_POST,           true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_multi_add_handle($mh, $ch);
}

// Launch the cURL requests
do {
	curl_multi_exec($mh, $running);
	curl_multi_select($mh);
} while ($running > 0);

// We don't have to update the user's account based on the response
// It will be handled by the notification URL (abonnement.html?notification=card)
foreach ($queue as $ch) {
	curl_close($ch);
	curl_multi_remove_handle($mh, $ch);
}

curl_multi_close($mh);
?>
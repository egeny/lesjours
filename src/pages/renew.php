<?php
require('_bootstrap.php');
global $wpdb;

// Set a time limit anyway
set_time_limit(60 * 60); // 1 hour (~ 2000 accounts using synchronous cURL)

$today = date('Y-m-d');
file_put_contents('renew.log', print_r(date('d-m-Y @ H:i:s'), true)."\n");

// Prepare and launch the query
$conditions = array(
	'meta_key = "expire"  AND meta_value  = "'.$today.'"',
	'meta_key = "payment" AND meta_value  = "card"',
	'meta_key = "plan"    AND meta_value != ""'
);

$conditions = array_map(function($value) use ($wpdb) { return 'user_id IN (SELECT user_id FROM '.$wpdb->usermeta.' WHERE '.$value.')'; }, $conditions);
$results    = $wpdb->get_col('SELECT user_id FROM '.$wpdb->usermeta.' WHERE '.implode(' AND ', $conditions).' GROUP BY user_id');

file_put_contents('renew.log', 'Query: SELECT user_id FROM '.$wpdb->usermeta.' WHERE '.implode(' AND ', $conditions).' GROUP BY user_id'."\n", FILE_APPEND);

foreach ($results as $id) {
	$user = get_userdata($id); // Get the user's object to retrieve its mail
	$meta = get_all_user_meta($id);

	file_put_contents('renew.log', 'User '.$user->user_email.' ('.$id.') — '.$meta['plan']."\n", FILE_APPEND);

	// Prepare the payload
	$payload = array(
		'alias'           => $meta['alias'],
		'aliasmode'       => 'subscription',
		'amount'          => $PLANS[$meta['plan']]['price'] * 100,
		'clientemail'     => $user->user_email,
		'clientident'     => $id,
		'clientip'        => '127.0.0.1', // Deprecated but still mandatory
		'clientuseragent' => 'Hello',     // Deprecated but still mandatory
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

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,            BE2BILL_REST_URL);
	curl_setopt($ch, CURLOPT_POST,           true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	file_put_contents('renew.log', 'Response: '.$response."\n\n", FILE_APPEND);

	curl_close($ch);
}

file_put_contents('renew.log', 'Done — '.print_r(date('d-m-Y @ H:i:s'), true), FILE_APPEND);
?>
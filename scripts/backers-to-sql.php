#!/usr/bin/php
<?php
$fields = array('name', 'firstname', 'gift', 'free', 'mail', 'address1', 'address2', 'zip', 'city', 'country');
$file   = basename(__FILE__, '.php');
$input  = @fopen($file.'.csv', 'r');
$output = @fopen($file.'.sql', 'w');
$row    = 0;

if (!$input)  { $error = error_get_last(); die('Could not open the '.$file.'.csv file.'."\n\t".$error['message']."\n"); }
if (!$output) { $error = error_get_last(); die('Could not create the '.$file.'.sql file.'."\n\t".$error['message']."\n"); }

fwrite($output, 'SET @PASS="Jeudi11";'."\n\n");

while (($data = fgetcsv($input, 1000, ';'))) {
	if (!$row++) { continue; } // Skip first line (fields)

	// Extract the datas and create a variable using the field's name ($name = $data[0])
	for ($i = 0, $count = count($fields); $i < $count; $i++) {
		${"$fields[$i]"} = $data[$i];
	}

	$insert = 'INSERT INTO `wp_users` (user_login, user_email, user_pass, user_registered, display_name) VALUES ("'.$mail.'", "'.$mail.'", MD5(@PASS), NOW(), "'.trim($firstname.' '.$name).'");';
	fwrite($output, $insert."\n");

	fwrite($output, 'SET @ID=LAST_INSERT_ID();'."\n\n");

	$meta = array(
		'first_name' => $firstname,
		'last_name'  => $name,
		'wp_capabilities' => 'a:1:{s:10:\"subscriber\";b:1;}',
		'paid' => '1',
		'subscription' => 'NOW()',
		'expire' => 'NOW() + INTERVAL 1 YEAR',
		'backer' => '1',
		'backer_reward' => $gift,
		'backer_free_months' => $free,
		'address' => $address1.' '.$address2,
		'zip' => $zip,
		'city' => $city,
		'country' => $country,
	);

	foreach ($meta as $key => $value) {
		if ($key == 'address' && empty(trim($value))) { continue; }
		if (array_search($key, array('subscription', 'expire')) === false) { $value = '"'.$value.'"'; }

		$insert = 'INSERT INTO `wp_usermeta` (user_id, meta_key, meta_value) VALUES(@ID, "'.$key.'", '.$value.');';
		fwrite($output, $insert."\n");
	}

	fwrite($output, "\n\n");
}

fclose($input);
fclose($output);
?>
<?php
	require('wp.php');

	// When receiving data, try to log the user
	if (!empty($_POST)) {
		$user = wp_signon(array(
			'user_login'    => $_POST['mail'],
			'user_password' => $_POST['password'],
			'remember'      => true
		), false);

		// TODO: display a message in case of error
		die(header('Location: '.$_SERVER['HTTP_REFERER']));
	}

	// Well, when asked to close the session…
	if (isset($_GET['close'])) {
		wp_logout();
		die(header('Location: /'));
	}

	// $current_user is always an object, check if it has an ID
	if ($current_user->ID) {
		$meta = get_user_meta($current_user->ID);
		$meta = array_map(function($array) { return $array[0]; }, $meta);

		// TODO: redirect to a page if not paid (or pending)
		// TODO: redirect to a page if expired

		// Allow if the user has paid and its subscription isn't expired
		if ($meta['paid'] == 1 && strtotime($meta['expire']) > time()) {
			// Make a sub-request so Apache will handle the request (see .htaccess)
			die(virtual($_SERVER['REQUEST_URI']));
		}
	}

	header('Location: /abonnement.html');
?>
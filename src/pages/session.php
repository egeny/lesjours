<?php
	require('_bootstrap.php');

	// Well, when asked to close the session…
	if (isset($_GET['close'])) {
		wp_logout();
		die(header('Location: /'));
	}

	// Oh snap, I forgot my password
	if (isset($_GET['forgot'])) {
		// Check if this user exists
		$user = get_user_by('email', $_POST['mail']);

		if ($user) {
			// Generate a verification token and send a mail
			$key = get_password_reset_key($user);
			$headers = array();
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=iso-8859-1';
			$headers[] = 'From: Les Jours <contact@lesjours.fr>';
			mail($_POST['mail'], 'Les Jours — j’ai oublié mon mot de passe', 'Tout va bien se passer, rendez vous ici : http://lesjours.fr/?mail='.urlencode($_POST['mail']).'&key='.$key.'#reset', implode("\r\n", $headers));

			// Redirect to the referer and display the appropriate modal
			die(header('Location: '.$_SERVER['HTTP_REFERER'].'#forgot-mailed'));
		}

		// If something went bad, redirect to the ** page
		die(); // TODO
	}

	if (isset($_GET['reset'])) {
		// Retrieve the mail and key for the referer's query parameters
		$parsed = parse_url($_SERVER['HTTP_REFERER']);
		parse_str($parsed['query'], $_GET); // Overwritte the $_GET parameters, because why not?

		// Check if the key is matching and still valid
		$user = check_password_reset_key($_GET['key'], $_GET['mail']);

		if (!is_wp_error($user)) {
			// Everything is good, change the user's password and log him in
			wp_set_password($_POST['password'], $user->ID);
			wp_signon(array(
				'user_login'    => $_GET['mail'],
				'user_password' => $_POST['password'],
				'remember'      => true
			), false);

			// Finally, redirect to the previous page (build the URL using $parsed to discard query parameters)
			die(header('Location: '.$parsed['scheme'].'://'.$parsed['host'].$parsed['path']));
		}

		// If something went bad, redirect to the ** page
		die(); // TODO
	}

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

	// $current_user is always an object, check if it has an ID
	if ($current_user->ID) {
		$meta = get_user_meta($current_user->ID);
		$meta = array_map(function($array) { return $array[0]; }, $meta);

		// TODO: redirect to a page if not paid (or pending)
		// TODO: redirect to a page if expired

		// Allow if the user has paid and its subscription isn't expired
		if ($meta['paid'] == 1 && strtotime($meta['expire']) > time()) {
			// Make a sub-request so Apache will handle the request (see .htaccess)

			$uri  = $_SERVER['REQUEST_URI'];
			$info = apache_lookup_uri($uri);

			// We have to handle 404 "manually", virtual will fail otherwise
			if (!$info->content_type) {
				http_response_code(404);
				$uri = '/404.html';
			}

			virtual($uri); // Will return a boolean so don't wrap in die()
			die();
		}
	}

	header('Location: '.$_SERVER['HTTP_REFERER'].'#login');
?>
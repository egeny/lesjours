<?php
	require('_bootstrap.php');

	$referer = $_SERVER['HTTP_REFERER'];
	$parsed  = parse_url($referer); // Parse the referer, it might be useful

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

			// Prepare an email and send it
			$subject = 'Vous avez oublié votre mot de passe';
			$content = file_get_contents('emails/forgot.html');
			$content = str_replace('mail=', 'mail='.urlencode($_POST['mail']), $content);
			$content = str_replace('key=',  'key='.$key, $content);

			$headers   = array();
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=UTF-8';
			$headers[] = 'From: Les Jours <abonnement@lesjours.fr>';

			mail($_POST['mail'], $subject, $content, implode("\r\n", $headers));

			// Redirect to the referer and display the appropriate modal
			die(header('Location: '.$referer.'#forgot-mailed'));
		}

		// If something went bad, redirect to the referer and display the appropriate modal
		die(header('Location: '.$referer.'#forgot-error'));
	}

	// Asking to reset the password
	if (isset($_GET['reset'])) {
		// Retrieve the mail and key for the referer's query parameters
		parse_str($parsed['query'], $_GET); // Overwrite the $_GET parameters, because why not?

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
			die(header('Location: '.$parsed['scheme'].'://'.$parsed['host'].$parsed['path'].'#reset-done'));
		}

		// If something went bad, redirect to the referer and display the appropriate modal (parameters should be included in the referer)
		die(header('Location: '.$referer.'#reset-error'));
	}

	// When receiving data, try to log the user
	if (!empty($_POST)) {
		$user = wp_signon(array(
			'user_login'    => $_POST['mail'],
			'user_password' => $_POST['password'],
			'remember'      => true
		), false);

		if (!is_wp_error($user)) {
			// Retrieve the "next" query parameters
			parse_str($parsed['query'], $_GET); // Overwrite the $_GET parameters, because why not?

			die(header('Location: '.(isset($_GET['next']) ? $_GET['next'] : $parsed['path'])));
		}

		// Display an evasive message in case of error
		die(header('Location: '.$referer.'#login-error'));
	}

	// $current_user is always an object, check if it has an ID
	if ($current_user->ID) {
		$meta = get_all_user_meta($current_user->ID);

		// Allow the user if its subscription isn't expired
		if (strtotime($meta['expire']) > time()) {
			$uri  = $_SERVER['REQUEST_URI'];
			$info = apache_lookup_uri($uri);

			// We have to handle 404 "manually", virtual will fail otherwise
			if (!$info->content_type) {
				http_response_code(404);
				$uri = '/404.html';
			} else {
				// Force the Content-Type otherwise virtual will serve text/html
				header('Content-Type: '.$info->content_type);
			}

			// Make a sub-request so Apache will handle the request (see .htaccess)
			virtual($uri); // Will return a boolean so don't wrap in die()
			die();
		} else {
			die(header('Location: /abonnement.html'));
		}
	}

	// Check if we can use the referer
	if ($_SERVER['HTTP_HOST'] == $parsed['host']) {
		header('Location: '.$parsed['path'].'?next='.$_SERVER['REQUEST_URI'].'#login');
	} else {
		header('Location: /?next='.$_SERVER['REQUEST_URI'].'#login');
	}
?>
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
			die(header('Location: '.$_SERVER['HTTP_REFERER'].'#forgot-mailed'));
		}

		// If something went bad, redirect to the error page
		die(header('Location: /erreur.html?forgot'));
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
			die(header('Location: '.$parsed['scheme'].'://'.$parsed['host'].(!preg_match('/erreur\.html/', $parsed['path']) ? $parsed['path'] : '/')));
		}

		// If something went bad, redirect to the error page (don't forget to include the mail an key)
		die(header('Location: /erreur.html?mail='.urlencode($_GET['mail']).'&key='.$_GET['key'].'&reset'));
	}

	// When receiving data, try to log the user
	if (!empty($_POST)) {
		$user = wp_signon(array(
			'user_login'    => $_POST['mail'],
			'user_password' => $_POST['password'],
			'remember'      => true
		), false);

		if (!is_wp_error($user)) {
			die(header('Location: '.(!preg_match('/erreur\.html/', $_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/')));
		}

		// Display an evasive message in case of error
		die(header('Location: /erreur.html?login'));
	}

	// $current_user is always an object, check if it has an ID
	if ($current_user->ID) {
		$meta = get_all_user_meta($current_user->ID);

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
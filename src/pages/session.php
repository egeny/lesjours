<?php
	require('wp.php');

	if (!empty($_POST)) {
		$user = wp_signon(array(
			'user_login'    => $_POST['mail'],
			'user_password' => $_POST['password'],
			'remember'      => true
		), false);
		// TODO: paid?
		// TODO: adjust the cookie's expire?
	}

	if (isset($_GET['out'])) {
		wp_logout();
		die(header('Location: /'));
	}

	header('Location: '.$_SERVER['HTTP_REFERER']);
?>
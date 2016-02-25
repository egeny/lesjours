<?php
	// Define the locale here since it may vary on the server
	define('LOCALE', 'fr_FR');

	// Path to wordpress
	define('WP_PATH', dirname(__FILE__).'/../../wp.lesjours.fr');

	// be2bill credentials (payment service)
	define('BE2BILL_IDENTIFIER', 'LES JOURS TEST');
	define('BE2BILL_PASSWORD',   '<P?[E}D4pRBGl%qO');
	define('BE2BILL_URL',        'https://secure-test.be2bill.com/front/form/process');

	// SlimPay credentials (payment service)
	define('SLIMPAY_APP_NAME',   'lesjoursdev');
	define('SLIMPAY_APP_SECRET', 'acQdBtUYymviyPnIFhe5CqlgG#6vGGb');
	define('SLIMPAY_ENDPOINT',   'https://api-sandbox.slimpay.net'); // Do not end with a slash
?>
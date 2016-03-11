<?php
	// Define the locale here since it may vary on the server
	define('LOCALE', 'fr_FR');

	// Path to wordpress
	define('WP_PATH', dirname(__FILE__).'/../../wp.lesjours.fr');

	// be2bill credentials (payment service)
	define('BE2BILL_IDENTIFIER', 'LES JOURS TEST');
	define('BE2BILL_PASSWORD',   '<P?[E}D4pRBGl%qO');
	define('BE2BILL_REST_URL',   'https://secure-test.be2bill.com/front/service/rest/process');
	define('BE2BILL_URL',        'https://secure-test.be2bill.com/front/form/process');

	// SlimPay credentials (payment service)
	define('SLIMPAY_APP_NAME',   'lesjoursdev2');
	define('SLIMPAY_APP_SECRET', '$fxfqWuipSC$T%wO9UbBmXw$~fz8AoV');
	define('SLIMPAY_ENDPOINT',   'https://api-sandbox.slimpay.net'); // Do not end with a slash
?>
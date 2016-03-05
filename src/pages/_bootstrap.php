<?php
	require('_env.php');
	require(WP_PATH.'/wp-load.php');

	// Locale to use (mainly for dates)
	setlocale(LC_ALL, LOCALE);

	global $PLANS;
	$PLANS = array(
		'jouriste' => array(
			'name'     => 'Jouriste',
			'price'    => 9,
			'duration' => '1 month'
		),

		'jouriste-cash' => array(
			'name'     => 'Jouriste cash',
			'price'    => 90,
			'duration' => '1 year'
		),

		'jouriste-desargente' => array(
			'name'     => 'Jouriste désargenté',
			'price'    => 5,
			'duration' => '1 month'
		)
	);

	// In "pilot" mode monthly subscription cost 1€
	$PLANS['jouriste']['price'] = $PLANS['jouriste-desargente']['price'] = 1;

	// Get the avatar's URL (check the URL first to provide an SVG fallback — not possible with gravatar)
	function avatar_url($mail) {
		// Simplified version of https://gist.github.com/justinph/5197810
		$hash = md5(strtolower(trim($mail)));
		$url  = 'https://www.gravatar.com/avatar/'.$hash.'?s=90';
		$code = wp_cache_get($hash);

		if (!$code) {
			$response = wp_remote_head($url.'&d=400');
			$code     = is_wp_error($response) ? '400' : $response['response']['code'];
			wp_cache_set($hash, $code, null, 60 * 5);
		}

		return $code == '200' ? $url : '/img/profile.svg';
	} // end of avatar_url()

	// Get all meta related to the given user
	function get_all_user_meta($id) {
		$meta = get_user_meta($id);

		// Shorten array having one item
		$meta = array_map(function($value) { return count($value) == 1 ? $value[0] : $value; }, $meta);

		// Decode JSON for specific keys (also, make an array by default)
		foreach (array('transactions', 'invoices') as $key) {
			$meta[$key] =  isset($meta[$key])     ? $meta[$key] : array(); // Default to array for undefined keys
			$meta[$key] = !is_string($meta[$key]) ? $meta[$key] : array($meta[$key]); // Transform to array previously shortened values
			$meta[$key] = array_map(function($value) { return json_decode($value); }, $meta[$key]);
		}

		return $meta;
	} // end of get_all_user_meta()

	// Get the client's IP address
	function get_client_ip() {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// IPv6 might be a pain with the local server
		return $ip !== '::1' ? $ip : '127.0.0.1';
	} // end of get_client_ip()

	final class SlimPayClient {
		private $entry; // The main entry point (SlimPayResponse on which we can launch methods)

		public function __construct() {
			$this->entry = new SlimPayResponse(self::cURL(SLIMPAY_ENDPOINT));
		}

		// Redirect every methods to the entry point
		public function __call($method, $args) {
			return call_user_func_array(array($this->entry, $method), $args);
		}

		public static function cURL($url, $headers = null, $post = null) {
			// Default the headers if not set
			if (!isset($headers)) {
				$headers = array(
					'Authorization: Bearer '.SlimPayToken::getInstance()->getToken(),
					'Content-Type: application/json'
				);
			}

			// Append the Accept header
			array_unshift($headers, 'Accept: application/hal+json; profile="https://api.slimpay.net/alps/v1"');

			// Prepare the cURL options
			$options = array(
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER     => $headers
			);

			if (isset($post)) {
				if (in_array('Content-Type: application/x-www-form-urlencoded', $headers)) {
					$options[CURLOPT_POSTFIELDS] = http_build_query($post);
				} else if (in_array('Content-Type: application/json', $headers)) {
					$options[CURLOPT_POSTFIELDS] = json_encode($post, JSON_UNESCAPED_UNICODE);
				}
			}

			// Prepare the cURL session
			$ch       = curl_init();
			$success  = curl_setopt_array($ch, $options);
			$response = json_decode(curl_exec($ch));
			curl_close($ch);

			return $response;
		}
	}

	final class SlimPayToken {
		private static $instance;

		private $token;  // The token to use as bearer
		private $expire; // The timestamp when the token will expire

		public static function getInstance() {
			if (static::$instance === null) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		protected function __construct() {}
		private   function __clone()     {}
		private   function __wakeup()    {}

		public function getToken() {
			// Return the token, if any, and if it is not going to expire
			// (keep a 15 seconds margin for safety)
			if ($this->token && ($this->expire - 15) > time()) { return $this->token; }

			// Prepare the headers to send
			$headers = array(
				'Authorization: Basic '.base64_encode(SLIMPAY_APP_NAME.':'.SLIMPAY_APP_SECRET),
				'Content-Type: application/x-www-form-urlencoded'
			);

			// Prepare the POST body
			$post = array(
				'grant_type' => 'client_credentials',
				'scope'      => 'api'
			);

			$response = SlimPayClient::cURL(SLIMPAY_ENDPOINT.'/oauth/token', $headers, $post);

			// Store the token for further use (and its expiration timestamp)
			$this->token  = $response->access_token;
			$this->expire = $response->expires_in + time();

			return $this->token;
		}
	}

	final class SlimPayResponse {
		private $methods = array(); // An array of method we can call on this response

		public function __construct($data) {
			// Copy the received data, except the reserved ones
			foreach ($data as $key => $value) {
				if (in_array($key, array('_embedded', '_links'))) { continue; }
				$this->{$key} = $value;
			}

			// Create methods for each relations
			if (isset($data->_links)) {
				foreach ($data->_links as $link => $relation) {
					if (preg_match('/#(.+)$/', $link, $matches)) {
						// CamelCase the namespace so we can use it as method
						$name = $matches[1];
						$name = ucwords($name, '-');
						$name = str_replace('-', '', $name);
						$name = lcfirst($name);

						// The "userApproval" is a specific link
						// It shouldn't be used with the API
						// Instead, the end-user should be redirected to its URL
						if ($name == 'userApproval') {
							$this->{$name} = $relation->href;
							continue;
						}

						$this->methods[$name] = $relation;
					}
				}
			}

			if (isset($data->_embedded)) {}
		}

		// Overload the method called to handle dynamic methods
		public function __call($method, $args) {
			// Check if we need to call the "request" method
			if (isset($this->methods[$method])) {
				array_unshift($args, $this->methods[$method]);
				return call_user_func_array(array($this, 'request'), $args);
			}

			// Still display a fatal error if calling an undefined method
			trigger_error('Call to undefined method '.__CLASS__.'::'.$method.'()', E_USER_ERROR);
		}

		// Request for another resource
		private function request($relation, $args) {
			$post = isset($args['post']) ? $args['post'] : null;
			unset($args['post']);

			// A function to replace the URI template's tokens with received parameters
			$replacer = function($matches) use ($args) {
				$replace = explode(',', $matches[1]);
				$replace = array_flip($replace);
				$replace = array_merge($replace, $args);
				$replace = array_intersect($replace, $args); // Keep only received parameters (prevent sending bad values if not provided)
				$replace = http_build_query($replace);
				$replace = str_replace($matches[1], $replace, $matches[0]);
				$replace = substr($replace, 1, strlen($replace) - 2);
				return $replace;
			};

			$url = preg_replace_callback('/\{[?&]?([^\}]+)\}/', $replacer, $relation->href);

			return new SlimPayResponse(SlimPayClient::cURL($url, null, $post));
		}
	}
?>
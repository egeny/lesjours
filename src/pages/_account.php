{% extends "partials/_layout-iframe.html" %}

{% block php -%}
<?php
	require('wp.php');
	$connected = is_user_logged_in();

	// Get the avatar's URL (check the URL first to provide an SVG fallback — not possible with gravatar)
	if ($connected) {
		// Simplified version of https://gist.github.com/justinph/5197810
		$hash = md5(strtolower(trim($current_user->user_email)));
		$url  = 'http://www.gravatar.com/avatar/'.$hash.'?s=90';
		$code = wp_cache_get($hash);

		if (!$code) {
			$response = wp_remote_head($url.'&d=400');
			$code     = is_wp_error($response) ? '400' : $response['response']['code'];
			wp_cache_set($hash, $code, null, 60 * 5);
		}

		$avatar = $code == '200' ? $url : '/img/profile.svg';
	}
?>
{% endblock %}

{% block content %}
	<div class="full-height mh-1m relative">
		<div id="account">
		<?php if ($connected) : ?>
			<a class="btn-round" href="/session?close">
				<img class="responsive full-height" src="<?php echo $avatar; ?>" alt="Accéder à mon profil" />
			</a>
		<?php else : ?>
			<a class="btn-square sm-hidden" href="/abonnement.html">S’abonner</a>
			{# An anchor link won't open in the parent's frame #}
			{# We have to set the parent's URL (referer) on the link #}
			<a class="login" href="<?php echo $_SERVER['HTTP_REFERER'] ?>#login">
				<i class="btn-round md-hidden lg-hidden">{{ icon("login") }}</i>
				<span class="btn-square sm-sr">Se connecter</span>
			</a>
		<?php endif ?>
		</div>
	</div>
{% endblock %}
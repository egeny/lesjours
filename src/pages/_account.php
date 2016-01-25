{% extends "partials/_layout-iframe.html" %}

{% block php -%}
<?php
	require('wp.php');
	$connected = is_user_logged_in();
?>
{% endblock %}

{% block content %}
	<div class="full-height mh-1m relative">
		<div id="account">
		<?php if ($connected) : ?>
			<a class="btn-round" href="/session?close">
				<img class="responsive" src="<?php echo get_avatar_url(get_current_user_id(), array('default' => '/img/profile.svg')); ?>" alt="Accéder à mon profil" />
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
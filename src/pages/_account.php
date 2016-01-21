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
			<a class="btn-round btn-transparent" href="/profile.html">
				<img class="responsive" src="/img/profile.svg" alt="Accéder à mon profil" />
			</a>
		<?php else : ?>
			<a class="sm-hidden btn-square" href="/abonnement.html">S’abonner</a>
			{# An anchor link won't open in the parent's frame #}
			{# We have to set the parent's URL (referer) on the link #}
			<a class="login" href="<?php echo $_SERVER['HTTP_REFERER'] ?>#login">
				<i class="md-hidden lg-hidden btn-round btn-transparent">{{ icon("ui-login") }}</i>
				<span class="sm-sr btn-square">Se connecter</span>
			</a>
		<?php endif ?>
		</div>
	</div>
{% endblock %}
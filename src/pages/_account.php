{% extends "partials/_layout-iframe.html" %}

{% block php -%}
<?php require('wp.php'); ?>
{% endblock %}

{% block content %}
	<div class="full-height mh-1m relative">
		<div id="account">
		<?php if (is_user_logged_in()) : ?>
			<a class="btn-round" href="/session?close">{{ icon("logout", "Se déconnecter") }}</a>
			<a class="btn-round" href="/compte.html">
				<img class="responsive full-height" src="<?php echo avatar_url(); ?>" alt="Accéder à mon compte" />
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
{% set page = { title: "Les Jours" } %}
{% extends "partials/_layout.html" %}

{% block php -%}
<?php
	$state = null;
	$state = !$state && isset($_GET['login'])  ? 'login'  : $state;
	$state = !$state && isset($_GET['forgot']) ? 'forgot' : $state;
	$state = !$state && isset($_GET['reset'])  ? 'reset'  : $state;

	!$state && die(header('Location: /'));
?>
{% endblock %}

{% block css %}
<style>
	body > form { box-shadow: 0 0 5px rgba(0, 0, 0, .3); }

	@media (min-width: 480px) {
		body > form {
			width: 400px;
			margin: 10px auto 0;
		}
	}
</style>
{% endblock %}

{% block content %}
<?php if ($state == 'login') : ?>
<form class="pa-1m" action="/session" method="post">
	<fieldset>
		<legend class="style-meta-larger mb-2g">Se connecter</legend>
		<p class="color-brand">Merci de vérifier vos informations de connexion.</p>
		<div class="field">
			<label for="error-mail">Adresse e-mail</label>
			<input id="error-mail" class="input check" name="mail" type="email" placeholder="mon-email@exemple.com" autocomplete="email" required />
		</div>
		<div class="field">
			<label for="error-password">Mot de passe</label>
			<input id="error-password" class="input check" name="password" type="password" placeholder="××××××××" required />
		</div>
		<a class="link external style-meta" href="#forgot">J’ai oublié mon mot de passe</a>
		<button class="btn-primary btn-brand w-100 mv-4g" type="submit">Valider</button>
		<p class="style-meta mb-0 text-upper color-main">Pas encore jouriste ? <a class="color-brand" href="/abonnement.html">Abonnez-vous maintenant</a>.</p>
	</fieldset>
</form>
<?php elseif ($state == 'forgot') : ?>
<form class="pa-1m" action="/session?forgot" method="post">
	<fieldset>
		<legend class="style-meta-larger mb-2g">Mot de passe oublié</legend>
		<p><span class="color-brand">Merci de vérifier votre adresse e-mail.</span> Un message vous sera envoyé avec un lien vous permettant de créer un nouveau mot de passe personnalisé.</p>
		<div class="field">
			<label for="forgot-mail">Adresse e-mail</label>
			<input id="forgot-mail" class="input check" name="mail" type="email" placeholder="mon-email@exemple.com" autocomplete="email" required />
		</div>
		<button class="btn-primary btn-brand w-100 mb-2g" type="submit">Envoyer</button>
	</fieldset>
</form>
<?php elseif ($state == 'reset') : ?>
<form class="pa-1m" action="/session?reset" method="post">
	<fieldset>
		<legend class="style-meta-larger mb-2g">Créer un nouveau mot de passe</legend>
		<p class="color-brand">Oups, quelque chose s’est mal passé. Merci de saisir votre nouveau mot de passe.</p>
		<p>Si cette erreur persiste, essayez de <a class="link text-inherit" href="#forgot">demander un nouveau message</a> ou <a class="link text-inherit" href="mailto:contact@lesjours.fr">contactez-nous</a>.</p>
		<div class="field">
			<label for="reset-password">Nouveau mot de passe</label>
			<input id="reset-password" class="input check" name="password" type="password" placeholder="××××××××" required />
		</div>
		<button class="btn-primary btn-brand w-100" type="submit">Valider</button>
	</fieldset>
</form>
<?php endif ?>
{% endblock %}
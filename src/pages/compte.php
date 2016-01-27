{% set page = { title: "Mon compte — Les Jours" } %}
{% extends "partials/_layout.html" %}

{% block content %}
<div class="container">
	<div class="row">
		<div class="col default-content md-w-6c md-mh-1c">
			<div class="default-content">
				<h2 class="style-meta-larger">Mes Jours</h2>
				<ul class="list-unstyled">
					<li><a class="link external" href="#mes-identifiants">Mes identifiants</a></li>
					<li><a class="link external" href="#mes-informations">Mes informations</a></li>
					<li><a class="link external" href="#mon-abonnement">Mon abonnement</a></li>
				</ul>
				<section id="mes-identifiants">
					<h3 class="style-meta-large">Mes identifiants</h3>
					<div class="field">
						<label for="mail">Adresse e-mail</label>
						<input id="mail" class="check" name="mail" type="email" placeholder="mon-email@exemple.com" autocomplete="email" required />
					</div>
					<div class="field">
						<label for="password">Mot de passe</label>
						<input id="password" class="check" name="password" type="password" placeholder="××××××××" required />
					</div>
				</section>
				<section id="mes-informations">
					<h3 class="style-meta-large">Mes informations</h3>
					<a class="btn-round" href="#">
						<img class="responsive full-height" src="/img/profile.svg" alt="" />
					</a>
					<div class="field">
						<label for="name">Nom</label>
						<input id="name" class="check md-white-check lg-white-check" name="name" type="text" placeholder="Dupont" autocomplete="family-name" required />
					</div>
					<div class="field">
						<label for="firstname">Prénom</label>
						<input id="firstname" class="check md-white-check lg-white-check" name="firstname" type="text" placeholder="Jean" autocomplete="given-name" required />
					</div>
					<div class="field">
						<label for="address">Adresse</label>
						<input id="address" class="check md-white-check lg-white-check" name="address" type="text" placeholder="1 avenue des Champs-Élysées" autocomplete="street-address" required />
					</div>
					<div class="field">
						<label for="zip">Code postal</label>
						<input id="zip" class="check md-white-check lg-white-check" name="zip" type="text" placeholder="75008" autocomplete="postal-code" required />
					</div>
					<div class="field">
						<label for="city">Ville</label>
						<input id="city" class="check md-white-check lg-white-check" name="city" type="text" placeholder="Paris" autocomplete="address-level2" required />
					</div>
				</section>
				<section id="mon-abonnement">
					<h3 class="style-meta-large">Mon abonnement</h3>
					<p>Vous avez souscrit un abonnement le 16 janvier 2016. <a href="/abonnement-conditions-generales.html">Lire les <abbr title="Conditions Générales de Vente">CGV</abbr></a></p>
					<h4>Votre formule</h4>
					<p>Jouriste cash — 90€ par an (expire le 16 janvier 2017)</p>
					<button class="btn-primary btn-brand md-w-6c md-mh-1c" type="submit">Se désabonner</button>
				</section>
				<button class="btn-primary btn-brand md-w-6c md-mh-1c" type="submit">Valider</button>
			</div>
		</div><!-- end of .col -->
	</div><!-- end of .row -->
</div><!-- end of .container -->
{% endblock %}
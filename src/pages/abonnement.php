{%
	set page = {
		title: "Abonnement — Les Jours",
		class: "page-subscription"
	}
%}
{% extends "partials/_layout.html" %}

{% block content %}
<div class="container">
	<div class="row full-height">
		<div class="col full-height">
			<div class="subscription full-height overflow-auto">
				<h2 class="mt-8g mb-2g md-ml-1c lg-ml-1c style-meta-larger">Devenir jouriste</h2>
				<form method="post" class="mb-2g md-w-6c md-ml-1c relative">
					<fieldset id="formule" class="mb-4g">
						<legend class="style-meta-large relative">Choisir ma formule</legend>
						<ul class="plans row text-center">
							<li class="ma-1g strong">
								<label>
									<span class="price">9<span class="sr"> </span><span class="currency"><span>€</span><span class="sr"> </span><span>par mois</span></span></span>
									<span class="name">Jouriste</span>
									<span class="desc">Sans engagement de durée</span>
									<small>2 profils par abonnement</small>
									<input class="sr" type="radio" name="plan" value="jouriste" required />
									<span class="action">Choisir</span>
								</label>
							</li>
							<li class="ma-1g">
								<label>
									<span class="price">90<span class="sr"> </span><span class="currency"><span>€</span><span class="sr"> </span><span>par an<sup>*</sup></span></span></span>
									<span class="name">Jouriste cash</span>
									<span class="desc">Sans engagement de durée</span>
									<small>2 profils par abonnement</small>
									<input class="sr" type="radio" name="plan" value="jouriste-cash" required />
									<span class="action">Choisir</span>
								</label>
							</li>
							<li class="ma-1g">
								<label>
									<span class="price">5<span class="sr"> </span><span class="currency"><span>€</span><span class="sr"> </span><span>par mois</span></span></span>
									<span class="name">Jouriste désargenté</span>
									<span class="desc">Sans engagement de durée</span>
									<small>Pour les -25 ans, chômeurs, fauchés, radins</small>
									<input class="sr" type="radio" name="plan" value="jouriste-desargente" required />
									<span class="action">Choisir</span>
								</label>
							</li>
							<li class="ma-1g">
								<a href="#">
									<img width="202" height="39" src="img/jouristes.svg" alt="" />
									<span class="name">Jouristes groupés</span>
									<span class="desc">Tarifs sur mesure</span>
									<small>Réservé aux entreprises, collectivités, communautés, sectes</small>
									<span class="action">Nous contacter</span>
								</a>
							</li>
						</ul>
					</fieldset>
					<fieldset id="coordonnees" class="lg-w-6c">
						<legend class="mb-2g style-meta-large relative">Mes coordonnées</legend>
						<div class="field">
							<label for="name">Nom</label>
							<input id="name" class="check md-white-check lg-white-check" name="name" type="text" placeholder="Dupont" autocomplete="family-name" required />
						</div>
						<div class="field">
							<label for="firstname">Prénom</label>
							<input id="firstname" class="check md-white-check lg-white-check" name="firstname" type="text" placeholder="Jean" autocomplete="given-name" required />
						</div>
						<div class="field">
							<label for="email">Adresse e-mail</label>
							<input id="email" class="check md-white-check lg-white-check" name="email" type="email" placeholder="mon-email@exemple.com" autocomplete="email" required />
						</div>
						<div class="field">
							<label for="password">Mot de passe</label>
							<input id="password" class="check md-white-check lg-white-check" name="password" type="password" placeholder="××××××××" autocomplete="new-password" required />
						</div>
						<div class="field">
							<label for="address">Adresse</label>
							<input id="address" class="check md-white-check lg-white-check" name="address" type="text" placeholder="1 avenue des Champs-Élysées" autocomplete="street-address" required />
						</div>
						<div class="field">
							<label for="zip">Code postal</label>
							<input id="zip" class="check md-white-check lg-white-check" name="zip" type="text" minlength="5" maxlength="5" placeholder="75008" autocomplete="postal-code" required />
						</div>
						<div class="field">
							<label for="city">Ville</label>
							<input id="city" class="check md-white-check lg-white-check" name="city" type="text" placeholder="Paris" autocomplete="address-level2" required />
						</div>
					</fieldset>
					<fieldset id="mode-de-paiement">
						<legend class="mb-2g style-meta-large relative">Mon mode de paiement</legend>
						<div class="gift style-meta lh-inherit color-dark">
							<i class="pull-left">{{ icon("bag") }}</i>
							<p><strong class="block text-upper">Un sac Les Jours offert</strong> si je choisis le prélèvement automatique.</p>
						</div>
						<div class="field row mb-1g">
							<label class="col md-w-auto pr-2g pl-0 color-dark">
								<input class="radio" type="radio" name="payment" value="bank" required disabled>
								<span class="radio"></span>
								Prélèvement
							</label>
							<label class="col md-w-auto pr-2g pl-0">
								<input class="radio" type="radio" name="payment" value="card" required>
								<span class="radio"></span>
								Carte bancaire
							</label>
						</div>
					</fieldset>
					<div>
						<label class="mb-2g relative style-meta text-upper">
							<input class="checkbox" type="checkbox" name="allowed" required>
							<span class="checkbox"></span>
							J’accepte les conditions générales de vente. <a class="color-brand" href="/abonnement-conditions-generales.html">Lire les <abbr title="Conditions Générales de Vente">CGV</abbr></a>.
						</label>
						<p class="summary hidden mv-4g pa-2g style-meta lh-inherit text-upper color-main">Vous avez choisi la formule « <span>Jouriste</span> » à <span>9</span> €/<span>mois<span>. <a class="color-brand" href="#formule">Modifier</a></p>
						<div class="mt-2g relative text-center">
							<button class="btn-primary sm-w-4c md-w-5c lg-w-5c" type="submit">
								<i class="legend-before sm-hidden">{{ icon("check") }}</i>
								<span>Accéder au paiement</span>
							</button>
						</div>
					</div>
					<div class="progress sm-hidden">
						<div></div>
					</div>
				</form>
			</div>
		</div><!-- end of .col -->
	</div><!-- end of .row -->
</div><!-- end of .container -->
{% endblock %}

{% block js %}
<script>
	$document.ready(function() {
		var
			$form      = $("div.subscription form"),
			$fieldsets = $form.find("fieldset"),
			$summary   = $form.find("p.summary"),
			$progress  = $form.find("div.progress div");

		function updateProgress() {
			if ($form.is(":valid")) {
				// Prefer handling this in JS
				$fieldsets.addClass("valid");
				$progress.css("height", "100%");
				return;
			}

			var height = 0;

			// Prefer using a class instead of the :valid pseudo-selector
			// So we can set as "invalid" even valid fieldsets (invalid because a previous fieldset isn't valid)
			$fieldsets.removeClass("valid");

			$fieldsets.each(function() {
				var $this = $(this);
				if ($this.is(":valid")) {
					$this.addClass("valid");
					height += $this.outerHeight(true);
				} else {
					return false;
				}
			});

			$progress.height(height);
		}

		// Update the summary when selecting a plan
		$fieldsets.eq(0).find("input").change(function() {
			var
				selected = $(this).val(),
				values   = {
					jouriste: ["Jouriste", 9, "mois"],
					"jouriste-cash": ["Jouriste cash", 90, "an"],
					"jouriste-desargente": ["Jouriste désargenté", 5, "mois"]
				};

			$summary.removeClass("hidden");
			$summary.find("span").each(function(index) {
				$(this).text(values[selected][index]);
			});
		});

		$form.find("input").change(updateProgress);
		$window.resize(updateProgress);
	});
</script>
{% endblock %}
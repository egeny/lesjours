{%
	set page = {
		title: "Abonnement — Les Jours",
		class: "page-subscription"
	}
%}
{% extends "partials/_layout.html" %}

{% block php -%}
<?php
	require('wp.php');

	$data  = $_POST;
	$error = null;

	$hidden = array(
		'amount'        => null,
		'cardfullname'  => null,
		'clientemail'   => null,
		'clientident'   => null,
		'createalias'   => 'yes',
		'description'   => 'Abonnement',
		'identifier'    => 'LES JOURS TEST',
		'operationtype' => 'payment',
		'orderid'       => null,
		'version'       => '2.0',
	);

	$prices = array(
		'jouriste'            => 9,
		'jouriste-cash'       => 90,
		'jouriste-desargente' => 5
	);

	$state = null;

	$subscriptions = array(
		'9'  => '+1 month',
		'90' => '+1 year',
		'5'  => '+1 month'
	);

	function signature($array) {
		$hash = array();
		$secret = '<P?[E}D4pRBGl%qO'; // FIXME: should be external (const in another file)

		foreach ($array as $name => $value) {
			$name = strtoupper($name);
			if ($name == 'HASH') { continue; }
			$hash[] = $name.'='.$value;
		}

		sort($hash);
		return hash('sha256', $secret.implode($secret, $hash).$secret);
	}

	// Receiving a notification from the payment service
	if (isset($_GET['notification'])) {
		// TODO (have to be online)
		file_put_contents('notification.log', print_r($_GET, true)."\n".print_r($_POST, true));
	}

	// Receiving a result from the payment service
	if (isset($_GET['result'])) {
		$state = 'result';

		if (empty($_GET['result'])) {
			unset($_GET['result']); // Exclude for the hash computation
			$hash  = signature($_GET);

			$error = !$error && $hash != $_GET['HASH']      ? 'hash'            : $error;
			$error = !$error && $_GET['EXECCODE'] != '0000' ? $_GET['EXECCODE'] : $error;

			if (!$error) {
				// TODO: should be done in the notification
				// TODO: should send email (in notification)
				$expire  = date('Y-m-d', strtotime($subscriptions[substr($_GET['AMOUNT'], 0, -2)]));
				$user_id = $_GET['CLIENTIDENT'];

				// Update the user's account
				update_user_meta($user_id, 'alias',  $_GET['ALIAS']);
				update_user_meta($user_id, 'expire', $expire);
				update_user_meta($user_id, 'paid',   true);

				// TODO: login
				wp_set_auth_cookie($user_id, true, false);
			}

			// Prefer redirecting to remove informations in the URL
			die(header('Location: /abonnement.html?result='.($error ? $error : 'success')));
		}
	}

	// Receiving data from the form
	if (!empty($_POST)) {
		// Makes sure there is an "accept" field to check against
		$data['accept'] = isset($data['accept']) ? $data['accept'] : null;

		// Sanitize and check received data
		foreach ($data as $field => $value) {
			switch ($field) {
				case 'email':
					$value = sanitize_email($_POST[$field]);
					$value = is_email($value) ? $value : null;
				break;

				case 'payment':
					$value = sanitize_text_field($_POST[$field]);
					$value = in_array($value, array('bank', 'card')) ? $value : null;
				break;

				case 'plan':
					$value = sanitize_text_field($_POST[$field]);
					$value = in_array($value, array_keys($prices)) ? $value : null;
				break;

				case 'password': $value = $_POST[$field];
				break;

				default: $data[$field] = $value = sanitize_text_field($_POST[$field]);
			}

			// Set an error flag if necessary
			if (empty($value)) {
				$error = is_array($error) ? $error : array();
				$error[$field] = true;
			}
		}

		if (!$error) {
			// Try to create a new user
			$user_id = wp_insert_user(array(
				'user_email' => $data['email'],
				'user_login' => $data['email'],
				'user_pass'  => $data['password'],
				'first_name' => $data['firstname'],
				'last_name'  => $data['name']
			));

			$error = is_wp_error($user_id) ? array('account' => $user_id) : null;

			if (!$error) {
				// Add additionnal metadata
				foreach (array('plan', 'address', 'zip', 'city', 'payment') as $field) {
					add_user_meta($user_id, $field, $data[$field], true);
				}

				// Mark as "unpaid" for now
				add_user_meta($user_id, 'paid', false, true);

				if ($data['payment'] == 'card') {
					// Complete the payload for the payment service
					$hidden['amount']       = $prices[$data['plan']] * 100;
					$hidden['cardfullname'] = $data['name'].' '.$data['firstname'];
					$hidden['clientemail']  = $data['email'];
					$hidden['clientident']  = $user_id;
					$hidden['orderid']      = date('Y-m-d').'-'.$user_id;
					$hidden['hash']         = signature($hidden);

					// Generate an hidden form containing the needed informations for the payment service
					$state = 'redirect';
				} else {
					// TODO: bank
				}
			}
		}
	} // end of if (!empty($_POST))
?>
{% endblock %}

{% block content %}
<div class="container">
	<div class="row full-height">
		<div class="col full-height">
			<div class="subscription full-height overflow-auto">
			<?php if ($state == 'redirect') : ?>
				<h2 class="mt-8g mb-2g md-ml-1c lg-ml-1c style-meta-larger">Redirection vers le paiement</h2>
				<form id="redirect" class="md-ml-1c lg-ml-1c" action="https://secure-test.be2bill.com/front/form/process.php" method="post">
				<?php foreach ($hidden as $name => $value) : ?>
					<input type="hidden" name="<?php echo strtoupper($name) ?>" value="<?php echo $value ?>" />
				<?php endforeach ?>
					<p>Si vous n'êtes pas redirigé automatiquement <button class="btn-blank" type="submit">cliquez-ici</button>.</p>
				</form>
				<script>
					document.getElementById("redirect").submit();
				</script>
			<?php elseif ($state == 'result') : ?>
				<?php if ($_GET['result'] == 'success') : ?>
					<h2 class="mt-8g mb-2g md-ml-1c lg-ml-1c style-meta-larger"><i class="legend-before">{{ icon("check") }}</i>Confirmation d’abonnement</h2>
					<div class="md-ml-1c lg-ml-1c">
						<p>Félicitations ! Vous êtes désormais jouriste. <a class="text-upper" href="#">Voir mon profil</a></p>
						<p>Vous pouvez maintenant naviguer sur le site.</p>
						<a class="btn-primary" href="/">Voir la une</a>
					</div>
				<?php else : ?>
					<h2 class="mt-8g mb-2g md-ml-1c lg-ml-1c style-meta-larger"><i class="legend-before">{{ icon("check") }}</i>Erreur</h2>
					<div class="md-ml-1c lg-ml-1c">
						<p><?php echo $_GET['result'] == 'hash' ? 'bad HASH' : $_GET['result'] ?></p>
					</div>
				<?php endif ?>
			<?php else : ?>
				<?php
					// FIXME: errors are disabled for now
					$error = null;
				?>
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
									<input class="sr" type="radio" name="plan" value="jouriste" required <?php if ($data['plan'] == 'jouriste') : ?>checked <?php endif ?>/>
									<span class="action">Choisir</span>
								</label>
							</li>
							<li class="ma-1g">
								<label>
									<span class="price">90<span class="sr"> </span><span class="currency"><span>€</span><span class="sr"> </span><span>par an<sup>*</sup></span></span></span>
									<span class="name">Jouriste cash</span>
									<span class="desc">Sans engagement de durée</span>
									<small>2 profils par abonnement</small>
									<input class="sr" type="radio" name="plan" value="jouriste-cash" required <?php if ($data['plan'] == 'jouriste-cash') : ?>checked <?php endif ?>/>
									<span class="action">Choisir</span>
								</label>
							</li>
							<li class="ma-1g">
								<label>
									<span class="price">5<span class="sr"> </span><span class="currency"><span>€</span><span class="sr"> </span><span>par mois</span></span></span>
									<span class="name">Jouriste désargenté</span>
									<span class="desc">Sans engagement de durée</span>
									<small>Pour les -25 ans, chômeurs, fauchés, radins</small>
									<input class="sr" type="radio" name="plan" value="jouriste-desargente" required <?php if ($data['plan'] == 'jouriste-desargente') : ?>checked <?php endif ?>/>
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
						<!-- TODO: error message -->
					</fieldset>
					<fieldset id="coordonnees" class="lg-w-6c">
						<legend class="mb-2g style-meta-large relative">Mes coordonnées</legend>
						<div class="field">
							<label for="name">Nom</label>
							<input id="name" class="check md-white-check lg-white-check" name="name" type="text" placeholder="Dupont" autocomplete="family-name" <?php if ($data['name']) { echo 'value="'.$data['name'].'" '; } ?>required />
							<?php if ($error['name']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="firstname">Prénom</label>
							<input id="firstname" class="check md-white-check lg-white-check" name="firstname" type="text" placeholder="Jean" autocomplete="given-name" <?php if ($data['firstname']) { echo 'value="'.$data['firstname'].'" '; } ?>required />
							<?php if ($error['firstname']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="email">Adresse e-mail</label>
							<input id="email" class="check md-white-check lg-white-check" name="email" type="email" placeholder="mon-email@exemple.com" autocomplete="email" <?php if ($data['email']) { echo 'value="'.$data['email'].'" '; } ?>required />
							<?php if ($error['email']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
							<?php if ($error['account']) : ?><span class="error color-brand">Ce compte existe</span><?php endif ?>
						</div>
						<div class="field">
							<label for="password">Mot de passe</label>
							<input id="password" class="check md-white-check lg-white-check" name="password" type="password" placeholder="××××××××" autocomplete="new-password" <?php if ($data['password']) { echo 'value="'.$data['password'].'" '; } ?>required />
							<?php if ($error['password']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="address">Adresse</label>
							<input id="address" class="check md-white-check lg-white-check" name="address" type="text" placeholder="1 avenue des Champs-Élysées" autocomplete="street-address" <?php if ($data['address']) { echo 'value="'.$data['address'].'" '; } ?>required />
							<?php if ($error['address']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="zip">Code postal</label>
							<input id="zip" class="check md-white-check lg-white-check" name="zip" type="text" minlength="5" maxlength="5" placeholder="75008" autocomplete="postal-code" <?php if ($data['zip']) { echo 'value="'.$data['zip'].'" '; } ?>required />
							<?php if ($error['zip']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="city">Ville</label>
							<input id="city" class="check md-white-check lg-white-check" name="city" type="text" placeholder="Paris" autocomplete="address-level2" <?php if ($data['city']) { echo 'value="'.$data['city'].'" '; } ?>required />
							<?php if ($error['city']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
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
								<input class="radio" type="radio" name="payment" value="bank" <?php if ($data['payment'] == 'bank') : ?>checked <?php endif ?>required disabled />
								<span class="radio"></span>
								Prélèvement
							</label>
							<label class="col md-w-auto pr-2g pl-0">
								<input class="radio" type="radio" name="payment" value="card" <?php if ($data['payment'] == 'card') : ?>checked <?php endif ?>required />
								<span class="radio"></span>
								Carte bancaire
							</label>
							<!-- TODO: error message -->
						</div>
					</fieldset>
					<div>
						<label class="mb-2g relative style-meta text-upper">
							<input class="checkbox" type="checkbox" name="accept" <?php if ($data['accept']) : ?>checked <?php endif ?>required>
							<span class="checkbox"></span>
							J’accepte les conditions générales de vente. <a class="color-brand" href="/abonnement-conditions-generales.html">Lire les <abbr title="Conditions Générales de Vente">CGV</abbr></a>.
							<!-- TODO: error message -->
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
			<?php endif ?>
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
		updateProgress();
	});
</script>
{% endblock %}
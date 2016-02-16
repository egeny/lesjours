{%
	set page = {
		title: "Abonnement — Les Jours",
		class: "page-subscription md-fixed lg-fixed"
	}
%}
{% extends "partials/_layout.html" %}

{% block php -%}
<?php
	require('_bootstrap.php');

	$data  = $_POST;
	$error = null;

	$hidden = array(
		'amount'        => null,
		'cardfullname'  => null,
		'clientemail'   => null,
		'clientident'   => null,
		'createalias'   => 'yes',
		'description'   => 'Abonnement',
		'identifier'    => BE2BILL_IDENTIFIER,
		'language'      => 'FR',
		'operationtype' => 'payment',
		'orderid'       => null,
		'version'       => '2.0',
	);

	$state = null;
	$title = 'Devenir jouriste';

	// Check if we need to use another $title or $state
	if (is_user_logged_in()) {
		$expired = get_user_meta($current_user->ID, 'expire');

		// Found an expiration (meaning the user has previously subscribed)
		if (is_array($expired) && isset($expired[0])) {
			$expired = time() > strtotime($expired[0]);
			$state   = !$expired ? 'subscribed' : $state;
			$title   = 'Renouveler mon abonnement';
		}
	}

	function signature($array) {
		$hash = array();

		foreach ($array as $name => $value) {
			$name = strtoupper($name);
			if ($name == 'HASH') { continue; }
			$hash[] = $name.'='.$value;
		}

		sort($hash);
		return hash('sha256', BE2BILL_PASSWORD.implode(BE2BILL_PASSWORD, $hash).BE2BILL_PASSWORD);
	}

	// Receiving a notification from the payment service
	if (isset($_GET['notification'])) {
		unset($_GET['notification']); // Exclude for the hash computation
		$hash  = signature($_GET);

		if ($hash == $_GET['HASH']) {
			$user_id = $_GET['CLIENTIDENT'];
			$date    = date('Y-m-d H:i:s');
			$plan    = get_user_meta($user_id, 'plan')[0];

			// Add a transaction trace (debugging purpose, should NOT be unique)
			$transaction = add_user_meta($user_id, 'transactions', json_encode(array(
				'date' => $date,
				'_get' => $_GET
			)));

			if ($_GET['EXECCODE'] == '0000') {
				// Retrieve the global invoice number and increment it
				$number  = intval(get_option('invoice_number', 0)) + 1;

				// Add an invoice (warning: should NOT be unique, obviously)
				add_user_meta($user_id, 'invoices', json_encode(array(
					'date'        => $date,
					'number'      => $number,
					'plan'        => $plan,
					'price'       => $PLANS[$plan]['price'],
					'transaction' => $transaction
				)));

				// Don't forget to update the invoice_number
				update_option('invoice_number', $number);

				// Update the user's account
				update_user_meta($user_id, 'alias',        $_GET['ALIAS']);
				update_user_meta($user_id, 'expire',       date('Y-m-d', strtotime('+'.$PLANS[$plan]['duration'])));
				update_user_meta($user_id, 'subscription', $date);

				// Prepare an email and send it
				$subject = 'Confirmation de votre abonnement aux « Jours »';
				$content = file_get_contents('emails/abonnement.html');

				$headers   = array();
				$headers[] = 'MIME-Version: 1.0';
				$headers[] = 'Content-type: text/html; charset=UTF-8';
				$headers[] = 'From: Les Jours <abonnement@lesjours.fr>';

				// Prevent displaying an error message (see below)
				@mail($_GET['CLIENTEMAIL'], $subject, $content, implode("\r\n", $headers));
			}
		}

		// As stated in the documentation, the payment service waits for "OK"
		// Otherwise, it will re-send a notification
		// See https://developer.be2bill.com/callbacks#c3
		die('OK');
	}

	// Receiving a result from the payment service
	if (isset($_GET['result'])) {
		$state = 'result';

		if (empty($_GET['result'])) {
			unset($_GET['result']); // Exclude for the hash computation
			$hash  = signature($_GET);

			$error = !$error && $hash != $_GET['HASH']      ? '1003'            : $error;
			$error = !$error && $_GET['EXECCODE'] != '0000' ? $_GET['EXECCODE'] : $error;

			// Prefer redirecting to remove informations in the URL
			die(header('Location: ?result='.($error ? $error : 'success')));
		}
	}

	// Receiving data from the form
	if (!empty($_POST)) {
		// Makes sure there is an "accept" field to check against
		$data['accept'] = isset($data['accept']) ? $data['accept'] : null;

		// Sanitize and check received data
		foreach ($data as $field => $value) {
			switch ($field) {
				case 'mail':
					$value = sanitize_email($_POST[$field]);
					$value = is_email($value) ? $value : null;
				break;

				case 'payment':
					$value = sanitize_text_field($_POST[$field]);
					$value = in_array($value, array('bank', 'card')) ? $value : null;
				break;

				case 'plan':
					$value = sanitize_text_field($_POST[$field]);
					$value = in_array($value, array_keys($PLANS)) ? $value : null;
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

		// If received data is fine, create a new user or use the current one
		if (!$error) {
			if ($current_user->ID) {
				$user_id = $current_user->ID;
				$meta = get_all_user_meta($user_id);

				// Fill $data with some informations for the payment service
				$data['name']      = $meta['last_name'];
				$data['firstname'] = $meta['first_name'];
				$data['mail']      = $current_user->user_email;
			} else {
				// Try to create a new user
				$user_id = wp_insert_user(array(
					'user_email' => $data['mail'],
					'user_login' => $data['mail'],
					'user_pass'  => $data['password'],
					'first_name' => $data['firstname'],
					'last_name'  => $data['name']
				));

				$error  = is_wp_error($user_id) ? array('account' => $user_id) : null;
				$fields = array('address', 'zip', 'city', 'country');
			}
		}

		// Check if an error occured while creating or finding the user
		if (!$error) {
			// Add additionnal metadata
			foreach (array_merge(array('plan', 'payment'), $fields ? $fields : array()) as $field) {
				update_user_meta($user_id, $field, $data[$field]);
			}

			// Look the user if not already logged-in
			wp_set_auth_cookie($user_id, true, false);

			if ($data['payment'] == 'card') {
				// Complete the payload for the payment service
				$hidden['amount']       = $PLANS[$data['plan']]['price'] * 100;
				$hidden['cardfullname'] = $data['name'].' '.$data['firstname'];
				$hidden['clientemail']  = $data['mail'];
				$hidden['clientident']  = $user_id;
				$hidden['orderid']      = date('Y-m-d').'-'.$user_id;
				$hidden['hash']         = signature($hidden);

				// Generate an hidden form containing the needed informations for the payment service
				$state = 'redirect';
			} else {
				// TODO: bank
			}
		}
	} // end of if (!empty($_POST))

	// Makes sure to have a default country, for the <select>
	$data['country'] = !empty($data['country']) ? $data['country'] : 'fr';
?>
{% endblock %}

{% block content %}
<?php if ($error) : ?>
<p class="flash mh-1m sm-mh-0 style-meta">
	<?php
		foreach ($error as $name => $value) {
			switch ($name) {
				case 'account': $value = 'd’utiliser une autre adresse e-mail, celle-ci existe déjà';
				break;

				case 'plan': $value = 'de choisir une formule';
				break;

				case 'payment': $value = 'de choisir un mode de paiement';
				break;

				case 'accept': $value = 'd’accepter les conditions générales de vente';
				break;

				default: $value = 'de vérifier les champs';
			}

			$error[$name] = $value;
		}

		if (count($error) == 1) {
			$error = implode(', ', $error);
		} else {
			$error = implode(', ', array_splice($error, 0, count($error) - 1)).' et '.end($error);
		}
	?>
	Merci <?php echo $error; ?>.
</p>
<?php endif ?>
<div class="container">
	<div class="row h-100">
		<div class="col h-100">
			<div class="subscription h-100 overflow-auto">
			<?php if ($state == 'redirect') : ?>
				<h2 class="mt-8g mb-2g md-ml-1c lg-ml-1c style-meta-larger">Redirection vers le paiement</h2>
				<form id="redirect" class="md-ml-1c lg-ml-1c" action="<?php echo BE2BILL_URL; ?>" method="post">
				<?php foreach ($hidden as $name => $value) : ?>
					<input type="hidden" name="<?php echo strtoupper($name) ?>" value="<?php echo $value ?>" />
				<?php endforeach ?>
					<p>Si vous n'êtes pas redirigé automatiquement <button class="btn-blank" type="submit">cliquez-ici</button>.</p>
				</form>
				<script>
					document.getElementById("redirect").submit();
				</script>
			<?php elseif ($state == 'result') : ?>
				<h2 class="mt-4g mb-2g md-ml-1c lg-ml-1c style-meta-larger">Devenir jouriste</h2>
				<?php if ($_GET['result'] == 'success') : ?>
					<div class="md-ml-1c lg-ml-1c">
						<h3 class="mb-1g relative style-meta-large"><i class="legend-before color-brand">{{ icon("check") }}</i>Confirmation d’abonnement</h3>
						<div class="default-content">
							<p class="mt-0">Félicitations ! Vous êtes désormais jouriste. <a class="link-unstyled" href="/mon-compte.html">Accéder à mon compte</a>.</p>
							<p>Vous pouvez maintenant naviguer sur le site.</p>
						</div>
					</div>
					<div class="mh-auto md-w-4c lg-w-4c">
						<a class="btn-primary btn-brand w-100" href="/">Voir la une</a>
					</div>
				<?php else : ?>
					<div class="md-ml-1c lg-ml-1c">
						<h3 class="mb-1g relative style-meta-large"><i class="legend-before color-brand">{{ icon("cross") }}</i>Erreur</h3>
						<div class="default-content">
							<?php $code = intval($_GET['result']); ?>
							<?php if ($code == 3) : ?>
								<p class="mt-0">Votre transaction est en cours. Veuillez contacter <a href="mailto:abonnement@lesjours.fr">abonnement@lesjours.fr</a> pour plus d’informations.</p>
							<?php elseif ($code > 3000 && !in_array($code, array(4017, 5001, 5002, 5004))) : ?>
								<p class="mt-0">Suite à un incident de paiement (<?php echo $_GET['result'] ?>) votre transaction n'a pu être réalisée. Veuillez contacter <a href="mailto:abonnement@lesjours.fr">abonnement@lesjours.fr</a> pour plus d’informations.</p>
							<?php else : ?>
								<p class="mt-0">Suite à une erreur technique (<?php echo $_GET['result'] ?>) votre transaction n’a pu être réalisée. Veuillez contacter <a href="mailto:abonnement@lesjours.fr">abonnement@lesjours.fr</a> pour plus d’informations.</p>
							<?php endif ?>
						</div>
					</div>
				<?php endif ?>
			<?php elseif ($state == 'subscribed') : ?>
				<h2 class="mt-4g mb-2g md-ml-1c lg-ml-1c style-meta-larger">Renouveler mon abonnement</h2>
				<div class="md-ml-1c lg-ml-1c">
					<h3 class="mb-1g relative style-meta-large"><i class="legend-before color-brand">{{ icon("check") }}</i>Vous êtes déjà abonné</h3>
					<div class="default-content">
						<p>Vous pouvez naviguer sur le site.</p>
					</div>
				</div>
			<?php else : ?>
				<h2 class="mt-8g mb-2g md-ml-1c lg-ml-1c style-meta-larger"><?php echo $title ?></h2>
				<form method="post" class="mb-2g md-w-6c md-ml-1c relative">
					<fieldset id="formule" class="mb-4g">
						<legend class="style-meta-large relative">Choisir ma formule</legend>
						<ul class="plans row text-center">
							<li class="ma-1g strong">
								<label>
									<span class="price">9<span class="sr"> </span><span class="currency"><span>€</span><span class="sr"> </span><span>par mois<sup>*</sup></span></span></span>
									<span class="name">Jouriste</span>
									<span class="desc">1 €/mois pendant le pilote*</span>
									<small>Sans engagement de durée</small>
									<input class="sr" type="radio" name="plan" value="jouriste" required <?php if (isset($data['plan']) && $data['plan'] == 'jouriste') : ?>checked <?php endif ?>/>
									<span class="action">Choisir</span>
								</label>
							</li>
							<li class="ma-1g">
								<label>
									<span class="price">90<span class="sr"> </span><span class="currency"><span>€</span><span class="sr"> </span><span>par an<sup>*</sup></span></span></span>
									<span class="name">Jouriste cash</span>
									<span class="desc">Un an à compter de la fin du pilote*</span>
									<small>Sans engagement de durée</small>
									<input class="sr" type="radio" name="plan" value="jouriste-cash" required <?php if (isset($data['plan']) && $data['plan'] == 'jouriste-cash') : ?>checked <?php endif ?>/>
									<span class="action">Choisir</span>
								</label>
							</li>
							<li class="ma-1g">
								<label>
									<span class="price">5<span class="sr"> </span><span class="currency"><span>€</span><span class="sr"> </span><span>par mois<sup>*</sup></span></span></span>
									<span class="name">Jouriste désargenté</span>
									<span class="desc">1 €/mois pendant le pilote*</span>
									<small>Sans engagement de durée</small>
									<small>Étudiant, chômeur, fauché</small>
									<input class="sr" type="radio" name="plan" value="jouriste-desargente" required <?php if (isset($data['plan']) && $data['plan'] == 'jouriste-desargente') : ?>checked <?php endif ?>/>
									<span class="action">Choisir</span>
								</label>
							</li>
							<li class="ma-1g">
								<a href="mailto:abonnement@lesjours.fr">
									<span class="name">Jouristes groupés</span>
									<span class="desc">Tarifs sur mesure</span>
									<small>Réservé aux entreprises, collectivités, communautés, sectes</small>
									<span class="action">Nous contacter</span>
								</a>
							</li>
						</ul>
						<!-- TODO: error message -->
					</fieldset>
					<?php if (!is_user_logged_in()) : ?>
					<fieldset id="coordonnees" class="lg-w-6c">
						<legend class="mb-2g style-meta-large relative">Mes coordonnées</legend>
						<div class="field">
							<label for="name">Nom</label>
							<input id="name" class="input check md-white-check lg-white-check" name="name" type="text" placeholder="Dupont" autocomplete="family-name" <?php if (isset($data['name'])) { echo 'value="'.$data['name'].'" '; } ?>required />
							<?php if (isset($error['name'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="firstname">Prénom</label>
							<input id="firstname" class="input check md-white-check lg-white-check" name="firstname" type="text" placeholder="Jean" autocomplete="given-name" <?php if (isset($data['firstname'])) { echo 'value="'.$data['firstname'].'" '; } ?>required />
							<?php if (isset($error['firstname'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="subscription-mail">Adresse e-mail</label>
							<input id="subscription-mail" class="input check md-white-check lg-white-check" name="mail" type="email" placeholder="mon-email@exemple.com" autocomplete="email" <?php if (isset($data['mail'])) { echo 'value="'.$data['mail'].'" '; } ?>required />
							<?php if (isset($error['mail'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="subscription-password">Mot de passe</label>
							<input id="subscription-password" class="input check md-white-check lg-white-check" name="password" type="password" placeholder="××××××××" autocomplete="new-password" <?php if (isset($data['password'])) { echo 'value="'.$data['password'].'" '; } ?>required />
							<?php if (isset($error['password'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="address">Adresse</label>
							<input id="address" class="input check md-white-check lg-white-check" name="address" type="text" placeholder="1 avenue des Champs-Élysées" autocomplete="street-address" <?php if (isset($data['address'])) { echo 'value="'.$data['address'].'" '; } ?>required />
							<?php if (isset($error['address'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="zip">Code postal</label>
							<input id="zip" class="input check md-white-check lg-white-check" name="zip" type="text" placeholder="75008" autocomplete="postal-code" <?php if (isset($data['zip'])) { echo 'value="'.$data['zip'].'" '; } ?>required />
							<?php if (isset($error['zip'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="city">Ville</label>
							<input id="city" class="input check md-white-check lg-white-check" name="city" type="text" placeholder="Paris" autocomplete="address-level2" <?php if (isset($data['city'])) { echo 'value="'.$data['city'].'" '; } ?>required />
							<?php if (isset($error['city'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="country">Pays</label>
							<select id="country" name="country" class="select">
							{% for value, label in countries %}
								<option value="{{ value }}"<?php if ($data['country'] == '{{ value }}') { echo ' selected'; } ?>>{{ label }}</option>
							{% endfor %}
							</select>
						</div>
					</fieldset>
					<?php endif ?>
					<fieldset id="mode-de-paiement">
						<legend class="mb-2g style-meta-large relative">Mon mode de paiement</legend>
						<div class="gift style-meta lh-inherit color-dark">
							<i class="pull-left">{{ icon("bag") }}</i>
							<p><strong class="block text-upper">Un sac Les Jours offert</strong> si je choisis le prélèvement automatique.</p>
						</div>
						<div class="field row mb-1g">
							<label class="col md-w-auto pr-2g pl-0 color-dark">
								<input class="radio" type="radio" name="payment" value="bank" <?php if (isset($data['payment']) && $data['payment'] == 'bank') : ?>checked <?php endif ?>required disabled />
								<span class="radio"></span>
								Prélèvement
							</label>
							<label class="col md-w-auto pr-2g pl-0">
								<input class="radio" type="radio" name="payment" value="card" checked required />
								<span class="radio"></span>
								Carte bancaire
							</label>
							<!-- TODO: error message -->
						</div>
					</fieldset>
					<div>
						<label class="mb-2g relative style-meta text-upper">
							<input class="checkbox" type="checkbox" name="accept" <?php if (isset($data['accept']) && $data['accept']) : ?>checked <?php endif ?>required>
							<span class="checkbox"></span>
							J’accepte les conditions générales de vente. <a class="color-brand" href="/abonnement-conditions-generales.html">Lire les <abbr title="Conditions Générales de Vente">CGV</abbr></a>.
							<!-- TODO: error message -->
						</label>
						<p class="summary hidden mv-4g pa-2g style-meta lh-inherit text-upper color-main">Vous avez choisi la formule « <span>Jouriste</span> » à <span>9</span> €/<span>mois<span>. <a class="color-brand" href="#formule">Modifier</a></p>
						<div class="mt-2g relative text-center">
							<button class="btn-primary btn-brand sm-w-4c md-w-5c lg-w-5c" type="submit">
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
<?php if (!$state) : ?>
<script src="/js/pages/subscription.min.js"></script>
<?php endif; ?>
{% endblock %}
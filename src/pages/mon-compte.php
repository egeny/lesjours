{% set page = { title: "Mon compte — Les Jours" } %}
{% extends "partials/layout/layout.html" %}

{% block php -%}
<?php
	require('_bootstrap.php');
	require(WP_PATH.'/wp-admin/includes/user.php'); // This file is required in order to delete an user

	$inspecting = false;   // Are we inspecting (default to no, obviously)
	$user = $current_user; // Get the current_user (might be overwritten just after)

	// Prevent accessing this URL if there is no logged-in user
	if (!$user->ID) { die(header('Location: /')); }

	// Allow super admins to inspect accounts
	if (isset($_GET['inspect']) && is_super_admin()) {
		if (!empty($_GET['inspect'])) {
			$user = get_user_by(intval($_GET['inspect']) ? 'id' : 'email', $_GET['inspect']);
		}

		$inspecting = !!$user;
		$user = $user ? $user : $current_user; // Fallback to the current_user if we couldn't found the requested user
	}

	$meta = get_all_user_meta($user->ID);

	$error = null;
	$data  = array(
		'mail'      => $user->user_email,
		'name'      => $meta['last_name'],
		'firstname' => $meta['first_name'],
		'address'   => $meta['address'],
		'zip'       => $meta['zip'],
		'city'      => $meta['city'],
		'country'   => !empty($meta['country']) ? $meta['country'] : 'fr'
	);

	if (isset($_GET['delete'])) {
		// TODO: Should send an email
		wp_delete_user($user->ID);
		die(header('Location: /'));
	}

	if (isset($_GET['unsubscribe'])) {
		delete_user_meta($user->ID, 'plan');
		delete_user_meta($user->ID, 'expire');
		delete_user_meta($user->ID, 'subscription');
		// TODO: Should send an email

		die(header('Location: /mon-compte.html#unsubscribed'));
	}

	if (!empty($_POST)) {
		// Sanitize and check received data
		foreach ($_POST as $field => $value) {
			switch ($field) {
				case 'password': $data[$field] = $value;
				break;

				default: $data[$field] = $value = sanitize_text_field($_POST[$field]);
			}

			// Set an error flag if necessary
			if (empty($value)) {
				if (in_array($field, array('plan', 'subscription', 'expire'))) { continue; } // Ignore some fields
				$error = is_array($error) ? $error : array();
				$error[$field] = true;
			}
		}

		if (!$error) {
			if (isset($data['password'])) {
				wp_set_password($data['password'], $user->ID);
				!$inspecting && wp_set_auth_cookie($user->ID, true, false);
			}

			// Prefer wp_update_user for the name and firstname since it will generate the display name
			wp_update_user(array(
				'ID'         => $user->ID,
				'first_name' => $data['firstname'],
				'last_name'  => $data['name']
			));

			foreach ($data as $field => $value) {
				if (in_array($field, array('mail', 'password', 'firstname', 'name'))) { continue; } // Ignore some fields
				if (in_array($field, array('plan', 'subscription', 'expire')) && !$inspecting) { continue; } // Constraint subscription edition to inspection mode

				update_user_meta($user->ID, $field, $value);
				$meta[$field] = $value; // Makes sure the meta are up to date (check below)
			}
			// I don't redirect because I'm lazy
			// Besides, we'll loose the active tab
		}
	}

	$data         = array_map('stripslashes', $data); // Remove slashes added while sanitizing to display them correctly
	$plan         = $PLANS[$meta['plan']];
	$expire       = strtotime($meta['expire']);
	$subscription = strtotime($meta['subscription']);
?>
{% endblock %}

{% block content %}
<div class="container">
	<div class="row">
		<div class="col default-content">
			<h2 class="style-meta-larger md-ml-1c lg-ml-1c">Mes Jours</h2>

			<ul class="list-inline style-meta md-ml-1c lg-ml-1c" role="tablist">
				<li class="mr-1g" role="presentation"><a role="tab" id="tab-mes-identifiants" class="link external" aria-controls="mes-identifiants" href="#mes-identifiants" aria-selected="true"  tabindex="0">Mes identifiants</a></li>
				<li class="mr-1g" role="presentation"><a role="tab" id="tab-mes-informations" class="link external" aria-controls="mes-informations" href="#mes-informations" aria-selected="false" tabindex="-1">Mes informations</a></li>
				<li class="mr-1g" role="presentation"><a role="tab" id="tab-mon-abonnement"   class="link external" aria-controls="mon-abonnement"   href="#mon-abonnement"   aria-selected="false" tabindex="-1">Mon abonnement</a></li>
				<li class="mr-1g" role="presentation"><a role="tab" id="tab-mes-factures"     class="link external" aria-controls="mes-factures"     href="#mes-factures"     aria-selected="false" tabindex="-1">Mes factures</a></li>
			</ul>

			<div class="tab-container md-w-6c md-ml-1c lg-w-10c lg-ml-1c">
				<section id="mes-identifiants" role="tabpanel" aria-labelledby="tab-mes-identifiants" aria-hidden="false">
					<h3 class="mb-4g style-meta-large">Mes identifiants</h3>
					<form method="post">
						<div class="field lg-w-½">
							<label for="account-mail">Adresse e-mail</label>
							<input id="account-mail" class="input check" name="mail" type="email" placeholder="mon-email@exemple.com" autocomplete="email"  <?php if (isset($data['mail'])) { echo 'value="'.$data['mail'].'" '; } ?>disabled required />
							<?php if (isset($error['mail'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field lg-w-½">
							<label for="account-password">Mot de passe</label>
							<input id="account-password" class="input check" name="password" type="password" placeholder="××××××××" required />
							<?php if (isset($error['password'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<button class="btn-primary btn-brand sm-w-100 md-w-6c md-mh-1c lg-w-⅓ lg-mh-4c" type="submit">Valider</button>
					</form>
				</section>

				<section id="mes-informations" role="tabpanel" aria-labelledby="tab-mes-informations" aria-hidden="true">
					<h3 class="mb-4g style-meta-large">Mes informations</h3>
					<a class="mb-2g sm-w-1c md-w-1c lg-w-1c block" href="https://gravatar.com" target="_blank">
						<img class="responsive h-100 radius" src="<?php echo avatar_url($user->user_email); ?>" alt="Mon avatar" />
					</a>
					<form method="post">
						<div class="field lg-w-½">
							<label for="name">Nom</label>
							<input id="name" class="input check" name="name" type="text" placeholder="Dupont" autocomplete="family-name" <?php if (isset($data['name'])) { echo 'value="'.$data['name'].'" '; } ?>required />
							<?php if (isset($error['name'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field lg-w-½">
							<label for="firstname">Prénom</label>
							<input id="firstname" class="input check" name="firstname" type="text" placeholder="Jean" autocomplete="given-name" <?php if (isset($data['firstname'])) { echo 'value="'.$data['firstname'].'" '; } ?>required />
							<?php if (isset($error['firstname'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field lg-w-½">
							<label for="address">Adresse</label>
							<input id="address" class="input check" name="address" type="text" placeholder="1 avenue des Champs-Élysées" autocomplete="street-address" <?php if (isset($data['address'])) { echo 'value="'.$data['address'].'" '; } ?>required />
							<?php if (isset($error['address'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field lg-w-½">
							<label for="zip">Code postal</label>
							<input id="zip" class="input check" name="zip" type="text" placeholder="75008" autocomplete="postal-code" <?php if (isset($data['zip'])) { echo 'value="'.$data['zip'].'" '; } ?>required />
							<?php if (isset($error['zip'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field lg-w-½">
							<label for="city">Ville</label>
							<input id="city" class="input check" name="city" type="text" placeholder="Paris" autocomplete="address-level2" <?php if (isset($data['city'])) { echo 'value="'.$data['city'].'" '; } ?>required />
							<?php if (isset($error['city'])) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field lg-w-½">
							<label for="country">Pays</label>
							<select id="country" name="country" class="select">
							{% for value, label in countries %}
								<option value="{{ value }}"<?php if ($data['country'] == '{{ value }}') { echo ' selected'; } ?>>{{ label }}</option>
							{% endfor %}
							</select>
						</div>
						<button class="btn-primary btn-brand sm-w-100 md-w-6c md-mh-1c lg-w-⅓ lg-mh-4c" type="submit">Valider</button>
					</form>
				</section>

				<section id="mon-abonnement" role="tabpanel" aria-labelledby="tab-mon-abonnement" aria-hidden="true">
					<h3 class="mb-3g style-meta-large">Mon abonnement</h3>
					<?php if ($plan && $subscription) : ?>
						<p>Vous avez souscrit un abonnement le <?php $day = strftime('%e', $subscription); echo ($day == '1' ? '1<sup>er</sup>' : $day).strftime(' %B %Y', $subscription) ?>. <a class="text-upper fw-bold color-brand" href="/abonnement-conditions-generales.html">Lire les <abbr title="Conditions Générales de Vente">CGV</abbr></a></p>
						<h4 class="h5">Votre formule</h4>
						<p><?php echo $plan['name'] ?> — <?php echo $plan['price'] ?> € par <?php echo $plan['duration'] == '1 year' ? 'an' : 'mois' ?> (<?php echo time() > $expire ? 'expiré' : 'expire' ?> le <?php $day = strftime('%e', $expire); echo ($day == '1' ? '1<sup>er</sup>' : $day).strftime(' %B %Y', $expire) ?>)</p>
					<?php else : ?>
						<p>Vous n’avez aucun abonnement en cours.</p>
						<a href="/abonnement.html" class="btn-primary btn-brand sm-w-100 md-w-6c md-mh-1c lg-w-⅓ lg-mh-4c">S’abonner</a>
					<?php endif ?>
					<?php if ($inspecting) : ?>
						<form class="row mt-8g" method="post">
							<div class="col field w-⅓">
								<label for="plan">Plan</label>
								<select id="plan" name="plan" class="select">
								<option value=""<?php if ($meta['plan'] == $name) { echo ' selected'; } ?>>Aucun</option>
								<?php foreach ($PLANS as $name => $plan) : ?>
									<option value="<?php echo $name ?>"<?php if ($meta['plan'] == $name) { echo ' selected'; } ?>><?php echo $plan['name'] ?> (<?php echo $plan['duration'] ?>)</option>
								<?php endforeach ?>
								</select>
							</div>
							<div class="col field w-⅓">
								<label for="subscription">Subscription</label>
								<input id="subscription" class="input" name="subscription" type="text" placeholder="<?php echo date('Y-m-d H:i:s') ?>" <?php if ($subscription) { echo 'value="'.date('Y-m-d H:i:s', $subscription).'" '; } ?> />
							</div>
							<div class="col field w-⅓">
								<label for="expire">Expire</label>
								<input id="expire" class="input" name="expire" type="text" placeholder="<?php echo date('Y-m-d'); ?>" <?php if ($expire) { echo 'value="'.date('Y-m-d', $expire).'" '; } ?> />
							</div>
							<button class="btn-primary btn-brand sm-w-100 md-w-6c md-mh-1c lg-w-⅓ lg-mh-4c" type="submit">Valider</button>
						</form>
					<?php endif ?>
				</section>

				<section id="mes-factures" role="tabpanel" aria-labelledby="tab-mes-factures" aria-hidden="true">
					<h3 class="mb-3g style-meta-large">Mes factures</h3>
					<?php if (count($meta['invoices'])) : ?>
						<table class="sm-w-100 lg-min-w-50 lg-max-w-75">
							<thead class="sr">
								<tr>
									<th>Date</th>
									<th>Description</th>
									<th>Prix</th>
									<th class="text-right">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach (array_reverse($meta['invoices']) as $invoice) : ?>
									<?php $plan = $PLANS[$invoice->plan]; ?>
									<tr>
										<td><?php echo date('d.m.Y', strtotime($invoice->date)) ?></td>
										<td>Abonnement <?php echo $plan['duration'] == '1 year' ? 'annuel' : 'mensuel' ?> au site <i>lesjours.fr</i><?php if ($invoice->price == 1) : ?> -<br/>tarif pilote<?php endif ?></td>
										<td><?php echo price($invoice->price) ?> €</td>
										<td class="text-right"><a class="text-upper fw-bold color-brand" href="/facture.html?n=<?php echo $invoice->number ?>" target="_blank">Voir la facture</a></td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					<?php else : ?>
						<p>Vous n’avez aucune facture.</p>
					<?php endif ?>
				</section>
			</div>
		</div><!-- end of .col -->
	</div><!-- end of .row -->
</div><!-- end of .container -->

{% include "partials/modals/delete.html"       %}
{% include "partials/modals/unsubscribe.html"  %}
{% include "partials/modals/unsubscribed.html" %}

{% include "partials/layout/footer.html" %}
{% endblock %}
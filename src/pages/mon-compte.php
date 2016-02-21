{% set page = { title: "Mon compte — Les Jours" } %}
{% extends "partials/layout/layout.html" %}

{% block php -%}
<?php
	require('_bootstrap.php');

	$inspecting = false;   // Are we inspecting (default to no, obviously)
	$user = $current_user; // Get the current_user (might be overwritten just after)

	// Prevent accessing this URL if there is no logged-in user
	if (!$user->ID) { die(header('Location: /')); }

	// Allow super admins to inspect accounts
	if (isset($_GET['inspect']) && is_super_admin()) {
		$user = get_user_by(intval($_GET['inspect']) ? 'id' : 'email', $_GET['inspect']);

		$inspecting = !!$user;
		$user = $user ? $user : $current_user; // Fallback to the current_user if we couldn't found the requested user
	}

	$meta = get_all_user_meta($user->ID);

	$error = null;
	$data  = array(
		'email'     => $user->user_email,
		'name'      => $meta['last_name'],
		'firstname' => $meta['first_name'],
		'address'   => $meta['address'],
		'zip'       => $meta['zip'],
		'city'      => $meta['city'],
		'country'   => !empty($meta['country']) ? $meta['country'] : 'fr'
	);

	if (isset($_GET['unsubscribe'])) {
		delete_user_meta($user->ID, 'plan');
		delete_user_meta($user->ID, 'expire');
		delete_user_meta($user->ID, 'subscription');
		// TODO: Should send an email

		die(header('Location: /mon-compte.html#unsubscribed'));
	}

	if (!empty($_POST)) {
		if (isset($_POST['password'])) {
			if (empty($_POST['password'])) {
				$error['password'] = true;
			} else {
				wp_set_password($_POST['password'], $user->ID);
				!$inspecting && wp_set_auth_cookie($user->ID, true, false);
			}
		} else {
			// Sanitize and check received data
			foreach ($_POST as $field => $value) {
				$data[$field] = $value = sanitize_text_field($value);

				// Set an error flag if necessary
				if (empty($value)) {
					$error = is_array($error) ? $error : array();
					$error[$field] = true;
				}
			}

			if (!$error) {
				// Prefer wp_update_user for the name and firstname since it will generate the display name
				wp_update_user(array(
					'ID'         => $user->ID,
					'first_name' => $data['firstname'],
					'last_name'  => $data['name']
				));

				foreach (array('address', 'zip', 'city', 'country') as $field) {
					update_user_meta($user->ID, $field, $data[$field]);
				}
			}
		}
	}

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
							<input id="account-mail" class="input check" name="mail" type="email" placeholder="mon-email@exemple.com" autocomplete="email"  <?php if (isset($data['email'])) { echo 'value="'.$data['email'].'" '; } ?>disabled required />
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
				</section>

				<section id="mes-factures" role="tabpanel" aria-labelledby="tab-mes-factures" aria-hidden="true">
					<h3 class="mb-3g style-meta-large">Mes factures</h3>
					<table>
						<thead>
							<tr>
								<th>N°</th>
								<th>Description</th>
								<th>Prix</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($meta['invoices'] as $invoice) : ?>
								<?php
									$plan  = $PLANS[$invoice['plan']];
									$start = strtotime($invoice['date']);
									$end   = strtotime('+'.$plan['duration'], $start);
								?>
							<tr>
								<td><?php echo $invoice['number'] ?></td>
								<td>Abonnement « <?php echo $plan['name'] ?> » (du <?php echo date('d-m-Y', $start); ?> au <?php echo date('d-m-Y', $end); ?>)</td>
								<td><?php echo $invoice['price'] ?> €</td>
							</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</section>
			</div>
		</div><!-- end of .col -->
	</div><!-- end of .row -->
</div><!-- end of .container -->

{% include "partials/modals/unsubscribe.html"  %}
{% include "partials/modals/unsubscribed.html" %}

{% include "partials/layout/footer.html" %}
{% endblock %}
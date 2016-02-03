{% set page = { title: "Mon compte — Les Jours" } %}
{% extends "partials/_layout.html" %}

{% block php -%}
<?php
	require('_bootstrap.php');
	require(WP_PATH.'/wp-admin/includes/user.php'); // This file needs to be included in order to delete an user

	// Prevent accessing this URL if there is no logged-in user
	if (!$current_user->ID) { die(header('Location: /')); }

	$meta = get_user_meta($current_user->ID);
	$meta = array_map(function($array) { return $array[0]; }, $meta);

	$error = null;
	$data  = array(
		'email'     => $current_user->user_email,
		'name'      => $meta['last_name'],
		'firstname' => $meta['first_name'],
		'address'   => $meta['address'],
		'zip'       => $meta['zip'],
		'city'      => $meta['city'],
		'country'   => !empty($meta['country']) ? $meta['country'] : 'fr'
	);

	if (isset($_GET['delete'])) {
		// TODO: Should send an email
		wp_delete_user($current_user->ID);
		die(header('Location: /'));
	}

	if (!empty($_POST)) {
		if (isset($_POST['password'])) {
			if (empty($_POST['password'])) {
				$error['password'] = true;
			} else {
				wp_set_password($_POST['password'], $current_user->ID);
				wp_set_auth_cookie($current_user->ID, true, false);
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
					'ID'         => $current_user->ID,
					'first_name' => $data['firstname'],
					'last_name'  => $data['name']
				));

				foreach (array('address', 'zip', 'city', 'country') as $field) {
					update_user_meta($current_user->ID, $field, $data[$field]);
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
			</ul>

			<div class="tab-container">
				<section id="mes-identifiants" class="tab md-w-6c md-ml-1c"  role="tabpanel" aria-labelledby="tab-mes-identifiants" aria-hidden="false">
					<h3 class="mb-4g style-meta-large">Mes identifiants</h3>
					<form method="post">
						<div class="field">
							<label for="account-mail">Adresse e-mail</label>
							<input id="account-mail" class="input check" name="mail" type="email" placeholder="mon-email@exemple.com" autocomplete="email"  <?php if ($data['email']) { echo 'value="'.$data['email'].'" '; } ?>disabled required />
						</div>
						<div class="field">
							<label for="account-password">Mot de passe</label>
							<input id="account-password" class="input check" name="password" type="password" placeholder="××××××××" required />
							<?php if ($error['password']) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<button class="btn-primary btn-brand md-w-6c md-mh-1c" type="submit">Valider</button>
					</form>
				</section>

				<section id="mes-informations" class="tab md-w-6c md-ml-1c"  role="tabpanel" aria-labelledby="tab-mes-informations" aria-hidden="true">
					<h3 class="mb-4g style-meta-large">Mes informations</h3>
					<a class="md-w-1c mb-2g block" href="https://gravatar.com">
						<img class="responsive full-height radius" src="<?php echo avatar_url(); ?>" alt="" />
					</a>
					<form method="post">
						<div class="field">
							<label for="name">Nom</label>
							<input id="name" class="input check" name="name" type="text" placeholder="Dupont" autocomplete="family-name" <?php if ($data['name']) { echo 'value="'.$data['name'].'" '; } ?>required />
							<?php if ($error['name']) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="firstname">Prénom</label>
							<input id="firstname" class="input check" name="firstname" type="text" placeholder="Jean" autocomplete="given-name" <?php if ($data['firstname']) { echo 'value="'.$data['firstname'].'" '; } ?>required />
							<?php if ($error['firstname']) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="address">Adresse</label>
							<input id="address" class="input check" name="address" type="text" placeholder="1 avenue des Champs-Élysées" autocomplete="street-address" <?php if ($data['address']) { echo 'value="'.$data['address'].'" '; } ?>required />
							<?php if ($error['address']) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="zip">Code postal</label>
							<input id="zip" class="input check" name="zip" type="text" placeholder="75008" autocomplete="postal-code" <?php if ($data['zip']) { echo 'value="'.$data['zip'].'" '; } ?>required />
							<?php if ($error['zip']) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="city">Ville</label>
							<input id="city" class="input check" name="city" type="text" placeholder="Paris" autocomplete="address-level2" <?php if ($data['city']) { echo 'value="'.$data['city'].'" '; } ?>required />
							<?php if ($error['city']) : ?><span class="error">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="country">Pays</label>
							<select id="country" name="country" class="select">
							{% for value, label in countries %}
								<option value="{{ value }}"<?php if ($data['country'] == '{{ value }}') { echo ' selected'; } ?>>{{ label }}</option>
							{% endfor %}
							</select>
						</div>
						<button class="btn-primary btn-brand md-w-6c md-mh-1c" type="submit">Valider</button>
					</form>
				</section>

				<section id="mon-abonnement" class="tab md-w-6c md-ml-1c"  role="tabpanel" aria-labelledby="tab-mon-abonnement" aria-hidden="true">
					<h3 class="style-meta-large">Mon abonnement</h3>
					<?php if ($plan) : ?>
						<p>Vous avez souscrit un abonnement le <?php $day = strftime('%e', $subscription); echo ($day == '1' ? '1<sup>er</sup>' : $day).strftime(' %B %Y', $subscription) ?>. <a href="/abonnement-conditions-generales.html">Lire les <abbr title="Conditions Générales de Vente">CGV</abbr></a></p>
						<h4>Votre formule</h4>
						<p><?php echo $plan['name'] ?> — <?php echo $plan['price'] ?> € par <?php echo $plan['duration'] == '1 year' ? 'an' : 'mois' ?> (expire le <?php $day = strftime('%e', $expire); echo ($day == '1' ? '1<sup>er</sup>' : $day).strftime(' %B %Y', $expire) ?>)</p>
						<a href="?delete" class="btn-primary btn-brand md-w-6c md-mh-1c" type="submit">Se désabonner</a>
					<?php else : ?>
						<p>Vous n’avez pas d’abonnement.</p>
					<?php endif ?>
				</section>
			</div>
		</div><!-- end of .col -->
	</div><!-- end of .row -->
</div><!-- end of .container -->
{% include "partials/_footer.html" %}
{% endblock %}
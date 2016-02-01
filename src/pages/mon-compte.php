{% set page = { title: "Mon compte — Les Jours" } %}
{% extends "partials/_layout.html" %}

{% block php -%}
<?php
	require('_bootstrap.php');

	$meta = get_user_meta($current_user->ID);
	$meta = array_map(function($array) { return $array[0]; }, $meta);

	//print_r($current_user);
	//print_r($meta);

	$data = array(
		'email' => $current_user->user_email,
		'name' => $meta['last_name'],
		'firstname' => $meta['first_name'],
		'address' => $meta['address'],
		'zip' => $meta['zip'],
		'city' => $meta['city'],
		'country' => !empty($meta['country']) ? $meta['country'] : 'fr'
	);

	$plan         = $PLANS[$meta['plan']];
	$expire       = strtotime($meta['expire']);
	$subscription = strtotime($meta['subscription']);
?>
{% endblock %}

{% block content %}
{# FIXME: externalize #}
{%
	set countries = {
		"af": "Afghanistan",
		"za": "Afrique du Sud",
		"al": "Albanie",
		"dz": "Algérie",
		"de": "Allemagne",
		"ad": "Andorre",
		"ao": "Angola",
		"ai": "Anguilla",
		"aq": "Antarctique",
		"ag": "Antigua-et-Barbuda",
		"sa": "Arabie saoudite",
		"ar": "Argentine",
		"am": "Arménie",
		"aw": "Aruba",
		"au": "Australie",
		"at": "Autriche",
		"az": "Azerbaïdjan",
		"bs": "Bahamas",
		"bh": "Bahreïn",
		"bd": "Bangladesh",
		"bb": "Barbade",
		"be": "Belgique",
		"bz": "Belize",
		"bj": "Bénin",
		"bm": "Bermudes",
		"bt": "Bhoutan",
		"by": "Biélorussie",
		"bo": "Bolivie",
		"ba": "Bosnie-Herzégovine",
		"bw": "Botswana",
		"br": "Brésil",
		"bn": "Brunéi Darussalam",
		"bg": "Bulgarie",
		"bf": "Burkina Faso",
		"bi": "Burundi",
		"kh": "Cambodge",
		"cm": "Cameroun",
		"ca": "Canada",
		"cv": "Cap-Vert",
		"ea": "Ceuta et Melilla",
		"cl": "Chili",
		"cn": "Chine",
		"cy": "Chypre",
		"co": "Colombie",
		"km": "Comores",
		"cg": "Congo-Brazzaville",
		"cd": "Congo-Kinshasa",
		"kp": "Corée du Nord",
		"kr": "Corée du Sud",
		"cr": "Costa Rica",
		"ci": "Côte d’Ivoire",
		"hr": "Croatie",
		"cu": "Cuba",
		"cw": "Curaçao",
		"dk": "Danemark",
		"dg": "Diego Garcia",
		"dj": "Djibouti",
		"dm": "Dominique",
		"eg": "Égypte",
		"sv": "El Salvador",
		"ae": "Émirats arabes unis",
		"ec": "Équateur",
		"er": "Érythrée",
		"es": "Espagne",
		"ee": "Estonie",
		"va": "État de la Cité du Vatican",
		"fm": "États fédérés de Micronésie",
		"us": "États-Unis",
		"et": "Éthiopie",
		"fj": "Fidji",
		"fi": "Finlande",
		"fr": "France",
		"ga": "Gabon",
		"gm": "Gambie",
		"ge": "Géorgie",
		"gh": "Ghana",
		"gi": "Gibraltar",
		"gr": "Grèce",
		"gd": "Grenade",
		"gl": "Groenland",
		"gp": "Guadeloupe",
		"gu": "Guam",
		"gt": "Guatemala",
		"gg": "Guernesey",
		"gn": "Guinée",
		"gq": "Guinée équatoriale",
		"gw": "Guinée-Bissau",
		"gy": "Guyana",
		"gf": "Guyane française",
		"ht": "Haïti",
		"hn": "Honduras",
		"hu": "Hongrie",
		"cx": "Île Christmas",
		"ac": "Île de l’Ascension",
		"im": "Île de Man",
		"nf": "Île Norfolk",
		"ax": "Îles Åland",
		"ky": "Îles Caïmans",
		"ic": "Îles Canaries",
		"cc": "Îles Cocos",
		"ck": "Îles Cook",
		"fo": "Îles Féroé",
		"gs": "Îles Géorgie du Sud et Sandwich du Sud",
		"fk": "Îles Malouines",
		"mp": "Îles Mariannes du Nord",
		"mh": "Îles Marshall",
		"um": "Îles mineures éloignées des États-Unis",
		"sb": "Îles Salomon",
		"tc": "Îles Turques-et-Caïques",
		"vg": "Îles Vierges britanniques",
		"vi": "Îles Vierges des États-Unis",
		"in": "Inde",
		"id": "Indonésie",
		"iq": "Irak",
		"ir": "Iran",
		"ie": "Irlande",
		"is": "Islande",
		"il": "Israël",
		"it": "Italie",
		"jm": "Jamaïque",
		"jp": "Japon",
		"je": "Jersey",
		"jo": "Jordanie",
		"kz": "Kazakhstan",
		"ke": "Kenya",
		"kg": "Kirghizistan",
		"ki": "Kiribati",
		"xk": "Kosovo",
		"kw": "Koweït",
		"re": "La Réunion",
		"la": "Laos",
		"ls": "Lesotho",
		"lv": "Lettonie",
		"lb": "Liban",
		"lr": "Libéria",
		"ly": "Libye",
		"li": "Liechtenstein",
		"lt": "Lituanie",
		"lu": "Luxembourg",
		"mk": "Macédoine",
		"mg": "Madagascar",
		"my": "Malaisie",
		"mw": "Malawi",
		"mv": "Maldives",
		"ml": "Mali",
		"mt": "Malte",
		"ma": "Maroc",
		"mq": "Martinique",
		"mu": "Maurice",
		"mr": "Mauritanie",
		"yt": "Mayotte",
		"mx": "Mexique",
		"md": "Moldavie",
		"mc": "Monaco",
		"mn": "Mongolie",
		"me": "Monténégro",
		"ms": "Montserrat",
		"mz": "Mozambique",
		"mm": "Myanmar",
		"na": "Namibie",
		"nr": "Nauru",
		"np": "Népal",
		"ni": "Nicaragua",
		"ne": "Niger",
		"ng": "Nigéria",
		"nu": "Niue",
		"no": "Norvège",
		"nc": "Nouvelle-Calédonie",
		"nz": "Nouvelle-Zélande",
		"om": "Oman",
		"ug": "Ouganda",
		"uz": "Ouzbékistan",
		"pk": "Pakistan",
		"pw": "Palaos",
		"pa": "Panama",
		"pg": "Papouasie-Nouvelle-Guinée",
		"py": "Paraguay",
		"nl": "Pays-Bas",
		"bq": "Pays-Bas caribéens",
		"pe": "Pérou",
		"ph": "Philippines",
		"pn": "Pitcairn",
		"pl": "Pologne",
		"pf": "Polynésie française",
		"pr": "Porto Rico",
		"pt": "Portugal",
		"qa": "Qatar",
		"hk": "R.A.S. chinoise de Hong Kong",
		"mo": "R.A.S. chinoise de Macao",
		"cf": "République centrafricaine",
		"do": "République dominicaine",
		"cz": "République tchèque",
		"ro": "Roumanie",
		"gb": "Royaume-Uni",
		"ru": "Russie",
		"rw": "Rwanda",
		"eh": "Sahara occidental",
		"bl": "Saint-Barthélemy",
		"kn": "Saint-Christophe-et-Niévès",
		"sm": "Saint-Marin",
		"mf": "Saint-Martin (partie française)",
		"sx": "Saint-Martin (partie néerlandaise)",
		"pm": "Saint-Pierre-et-Miquelon",
		"vc": "Saint-Vincent-et-les-Grenadines",
		"sh": "Sainte-Hélène",
		"lc": "Sainte-Lucie",
		"ws": "Samoa",
		"as": "Samoa américaines",
		"st": "Sao Tomé-et-Principe",
		"sn": "Sénégal",
		"rs": "Serbie",
		"sc": "Seychelles",
		"sl": "Sierra Leone",
		"sg": "Singapour",
		"sk": "Slovaquie",
		"si": "Slovénie",
		"so": "Somalie",
		"sd": "Soudan",
		"ss": "Soudan du Sud",
		"lk": "Sri Lanka",
		"se": "Suède",
		"ch": "Suisse",
		"sr": "Suriname",
		"sj": "Svalbard et Jan Mayen",
		"sz": "Swaziland",
		"sy": "Syrie",
		"tj": "Tadjikistan",
		"tw": "Taïwan",
		"tz": "Tanzanie",
		"td": "Tchad",
		"tf": "Terres australes françaises",
		"io": "Territoire britannique de l’océan Indien",
		"ps": "Territoires palestiniens",
		"th": "Thaïlande",
		"tl": "Timor oriental",
		"tg": "Togo",
		"tk": "Tokelau",
		"to": "Tonga",
		"tt": "Trinité-et-Tobago",
		"ta": "Tristan da Cunha",
		"tn": "Tunisie",
		"tm": "Turkménistan",
		"tr": "Turquie",
		"tv": "Tuvalu",
		"ua": "Ukraine",
		"uy": "Uruguay",
		"vu": "Vanuatu",
		"ve": "Venezuela",
		"vn": "Vietnam",
		"wf": "Wallis-et-Futuna",
		"ye": "Yémen",
		"zm": "Zambie",
		"zw": "Zimbabwe"
	}
%}
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
							<input id="account-mail" class="check" name="mail" type="email" placeholder="mon-email@exemple.com" autocomplete="email"  <?php if ($data['email']) { echo 'value="'.$data['email'].'" '; } ?>disabled required />
						</div>
						<div class="field">
							<label for="account-password">Mot de passe</label>
							<input id="account-password" class="check" name="password" type="password" placeholder="××××××××" required />
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
							<input id="name" class="check md-white-check lg-white-check" name="name" type="text" placeholder="Dupont" autocomplete="family-name" <?php if ($data['name']) { echo 'value="'.$data['name'].'" '; } ?>required />
							<?php if ($error['name']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="firstname">Prénom</label>
							<input id="firstname" class="check md-white-check lg-white-check" name="firstname" type="text" placeholder="Jean" autocomplete="given-name" <?php if ($data['firstname']) { echo 'value="'.$data['firstname'].'" '; } ?>required />
							<?php if ($error['firstname']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="address">Adresse</label>
							<input id="address" class="check md-white-check lg-white-check" name="address" type="text" placeholder="1 avenue des Champs-Élysées" autocomplete="street-address" <?php if ($data['address']) { echo 'value="'.$data['address'].'" '; } ?>required />
							<?php if ($error['address']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="zip">Code postal</label>
							<input id="zip" class="check md-white-check lg-white-check" name="zip" type="text" placeholder="75008" autocomplete="postal-code" <?php if ($data['zip']) { echo 'value="'.$data['zip'].'" '; } ?>required />
							<?php if ($error['zip']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="city">Ville</label>
							<input id="city" class="check md-white-check lg-white-check" name="city" type="text" placeholder="Paris" autocomplete="address-level2" <?php if ($data['city']) { echo 'value="'.$data['city'].'" '; } ?>required />
							<?php if ($error['city']) : ?><span class="error color-brand">Vérifiez ce champ</span><?php endif ?>
						</div>
						<div class="field">
							<label for="country">Pays</label>
							<select id="country" name="country">
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
						<a href="?unsubscribe" class="btn-primary btn-brand md-w-6c md-mh-1c" type="submit">Se désabonner</a>
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
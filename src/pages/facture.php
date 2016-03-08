{%
	set page = {
		title: "Facture — Les Jours",
		class: "page-invoice"
	}
%}
{% extends "partials/layout/layout-iframe.html" %}

{% block php -%}
<?php
	require('_bootstrap.php');

	// Prevent accessing this URL if there is no logged-in user
	if (!$current_user->ID) { die(header('Location: /')); }

	// Retrieve the metas and the requested invoice
	$meta    = get_all_user_meta($current_user->ID);
	$invoice = array_filter($meta['invoices'], function($value) { return $value->number == $_GET['n']; });
	$invoice = count($invoice) ? end($invoice) : false;

	// Stop if we couldn't find the invoice
	if (!$invoice) { die(header('Location: /')); }

	// Gather the necessary variables
	$plan  = $PLANS[$invoice->plan];
	$start = strtotime($invoice->date);
	$end   = strtotime($plan['duration'], $start);
	$taxes = (2.1 / 100) * $invoice->price;
?>
{% endblock %}

{% block content %}
<h1 class="pt-1g pb-2g text-center">
	<a class="block" href="/">
		<img width="55" height="42" src="/img/logo-text.svg" alt="Les Jours" />
	</a>
</h1>

<div class="container">
	<div class="row">
		<div class="col default-content md-w-6c md-mh-1c lg-w-8c lg-ml-1c">
			<h2 class="style-meta-larger">Mes jours</h2>

			<div class="row">
				<div class="w-50 fw-bold text-upper">
					<address>
						Les Jours<br/>
						14 rue de Rouen<br/>
						75019 Paris, France
					</address>
					<div class="smallest">
						<p class="mb-0">SAS au capital de 90 000 euros</p>
						<p class="mb-0"><abbr title="Registre du Commerce et des Sociétés">RCS</abbr> de Paris : 812 749 323</p>
						<p class="mb-0"><abbr title="Taxe sur la Valeur Ajoutée">TVA</abbr> : FR 12 812749323</p>
					</div>
				</div>

				<div class="w-50 fw-bold text-upper">
					<div>
						<?php echo $meta['first_name'] ?> <?php echo $meta['last_name'] ?><br/>
						<?php echo $meta['address'] ?><br/>
						<?php echo $meta['zip'] ?> <?php echo $meta['city'] ?>, <?php echo $meta['country'] ?>
					</div>
					<p class="smallest"><?php echo $current_user->user_email ?></p>
				</div>
			</div>

			<p class="larger color-brand"><?php echo date('d.m.Y') ?> | Facture <?php echo ucfirst($invoice->plan) ?>-<?php echo date('Y', $start) ?>-<?php echo $invoice->number ?></p>

			<div class="fw-bold text-upper">
				<p class="mb-0">Mode de règlement : <?php echo $invoice->payment == 'bank' ? 'prélèvement bancaire' : 'carte bancaire' ?> le <?php echo date('d.m.Y', $start) ?></p>
				<p>Période d’abonnement : du <?php echo date('d.m.Y', $start) ?> au <?php echo date('d.m.Y', $end) ?></p>
			</div>

			<table class="w-100 color-dark">
				<thead class="smaller">
					<tr>
						<th>Libellé</th>
						<th><abbr title="Quantité">Qté</abbr></th>
						<th>Prix <abbr title="Hors Taxe">HT</abbr></th>
						<th>Total <abbr title="Hors Taxe">HT</abbr></th>
					</tr>
				</thead>
				<tfoot class="smaller">
					<tr>
						<th colspan="3" scope="row">Total de la facture <abbr title="Hors Taxe">HT</abbr></th>
						<td><?php echo price($invoice->price - $taxes) ?> €</td>
					</tr>
					<tr>
						<th colspan="3" scope="row">Montant de la TVA (2,10 %)</th>
						<td><?php echo price($taxes) ?> €</td>
					</tr>
					<tr class="color-brand">
						<th colspan="3" scope="row">Total de la facture TTC</th>
						<td><?php echo price($invoice->price) ?> €</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td>Abonnement <?php echo $plan['duration'] == '1 year' ? 'annuel' : 'mensuel' ?> au site <i>lesjours.fr</i><?php if ($invoice->price == 1) : ?> -<br/>tarif pilote<?php endif ?></td>
						<td>1</td>
						<td><?php echo price($invoice->price - $taxes) ?> €</td>
						<td><?php echo price($invoice->price - $taxes) ?> €</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col text-center">
			<button class="print btn-primary btn-brand sm-w-100 md-w-½ lg-w-⅓" type="submit" onclick="window.print()">Imprimer</button>
		</div>
	</div>
</div>
{% include "partials/layout/footer.html" %}
{% endblock %}
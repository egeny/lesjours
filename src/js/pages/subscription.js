$document.ready(function() {
	"use strict";

	var
		$form      = $("div.subscription form"),
		$fieldsets = $form.find("fieldset"),
		$summary   = $form.find("p.summary"),
		$progress  = $form.find("div.progress div");

	function updateProgress() {
		if (!$form.length) { return; }

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
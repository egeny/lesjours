$document.ready(function() {
	"use strict";

	var
		$header = $("#header"),
		$burger = $("#burger"),
		timer;

	function hashchange() {
		var hash = window.location.hash;

		if (hash === "#menu") {
			$body.addClass("has-modal"); // Use a class to disable scrolling
			$header.addClass("fixed");
			$burger.removeClass("initial").addClass("close"); // The "initial" class is used to disable the animation (when the page load)
		} else {
			$body.removeClass("has-modal");
			$header.removeClass("fixed");
			$burger.removeClass("close");

			// Bonus: Revert the "initial" class so the transition on the burger will work again
			timer = window.setTimeout(function() {
				$burger.addClass("initial");
			}, 300);
		}
	}

	$window.bind("hashchange", hashchange);
	window.location.hash && hashchange();

	$burger.click(function(e) {
		window.clearTimeout(timer);
		if ($burger.hasClass("close")) {
			window.location.hash = "";
			e.preventDefault();
		}
	});
});
$document.ready(function() {
	"use strict";

	var
		$container = $("#header-container"),
		$burger    = $("#burger"),
		timer;

	$window.bind("hashchange", function() {
		var hash = window.location.hash;

		if (hash === "#menu") {
			$body.css("overflow", "hidden"); // Prefer inline style, the body may already have a "no-overflow" class and it shouldn't be removed
			$container.addClass("fixed");
			$burger.removeClass("initial").addClass("close"); // The "initial" class is used to disable the animation (when the page load)
		} else {
			$body.css("overflow", ""); // Remove the inline style
			$container.removeClass("fixed");
			$burger.removeClass("close");

			// Bonus: Revert the "initial" class so the transition on the burger will work again
			timer = window.setTimeout(function() {
				$burger.addClass("initial");
			}, 300);
		}
	});

	// Trigger a hashchange on loading to check if there is already a hash
	$window.trigger("hashchange");

	$burger.click(function(e) {
		window.clearTimeout(timer);
		if ($burger.hasClass("close")) {
			window.location.hash = "";
			e.preventDefault();
		}
	});
});
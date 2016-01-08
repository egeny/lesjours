$document.ready(function() {
	"use strict";

	// TODO: check with multiple carousel (might occurs?)
	// TODO: fallback for older browsers?
	// TODO: one item?
	// TODO: bug with two items

	var
		$carousel = $(".carousel"),
		$items    = $carousel.children(),
		$controls = $(".carousel-controls"),
		$buttons  = $controls.find("button"),
		$pager    = $controls.find("li"),
		interval  = $carousel.data("interval"),
		timer;

	function next($item) {
		return $item.next().length ? $item.next() : $items.first();
	}

	function previous($item) {
		return $item.prev().length ? $item.prev() : $items.last();
	}

	function change(e) {
		var
			// Get the direction depending on the "data-direction" of the target (default to "forward")
			direction = $(this).data("direction") === "backward" ? "backward" : "forward",
			selected  = $carousel.find(".selected"), // Find the currently selected item
			i = 0, count = $items.length, order = 2;

		// Stop the interval if this function was called with an event (its target should be a button)
		if (e) {
			$carousel.data("state", "paused");
			$carousel.trigger("state-changed");
		}

		// Makes sure we have a selected item (fallback to the first item)
		selected = selected.length ? selected : $items.first();

		// Find the new selected item
		selected.removeClass("selected expanded");
		selected = direction === "forward" ? next(selected) : previous(selected);
		selected.addClass("selected");

		// Update the pager
		$pager.removeClass("selected");
		$pager.eq(selected.index()).addClass("selected");

		// Loop through the items to re-order them
		for (; i < count; i++) {
			order = order === count + 1 ? 1 : order; // The last item have to be set as first
			selected.css("order", order++);
			selected = next(selected);
		}

		// Add a "hold" class to offset the carousel to the previous item, use also a "reverse" class depending on the direction
		$carousel.addClass("hold");
		$carousel[(direction === "forward" ? "remove" : "add") + "Class"]("reverse");

		// Remove the "hold" class to launch the animation
		window.setTimeout(function() {
			$carousel.removeClass("hold");
		}, 50); // Use a timeout so the browser have the time to set the "hold" transition and transformation (the animation won't work otherwise)
	}

	// Listen for state-changed event to automatically switch items
	$carousel.on("state-changed", function() {
		switch ($carousel.data("state")) {
			case "playing":
			case "running":
				if (interval) { timer = window.setInterval(change, interval); }
				break;

			case "paused":
				window.clearInterval(timer);
				break;
		}
	});

	$carousel.hammer().bind("swiperight", function() { $buttons.filter("[data-direction=backward]").click(); });
	$carousel.hammer().bind("swipeleft", change);

	$buttons.click(change);
	$carousel.trigger("state-changed");
});
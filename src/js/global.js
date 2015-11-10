$("body").addClass("js");

(function() {
	// TODO: check with multiple carousel (might occurs?)
	// TODO: swipe
	// TODO: fallback for older browsers?
	// TODO: list items
	// TODO: auto-next
	// TODO: one item?
	// TODO: bug with two items
	var
		$carousel = $(".carousel"),
		$items    = $carousel.children()
		$buttons  = $carousel.siblings(".carousel-controls").find("button");

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

		// Makes sure we have a selected item (fallback to the first item)
		selected = selected.length ? selected : $items.first();

		// Find the new selected item
		selected.removeClass("selected");
		selected = direction === "forward" ? next(selected) : previous(selected);
		selected.addClass("selected");

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

	$buttons.click(change);
}());
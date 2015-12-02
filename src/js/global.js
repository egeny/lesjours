document.body.classList.add("js");

(function() {
	var
		affixes  = [],
		previous = 0,
		$window  = $(window);

	$("[data-spy=affix]").each(function() {
		var affix = { $element: $(this) };
		affix.initial = affix.$element.offset().top;
		affix.start   = affix.initial + affix.$element.height();
		affix.snap    = affix.start - 10;
		affixes.push(affix);
	});

	$window.scroll(function() {
		var current = $window.scrollTop();

		affixes.forEach(function(item) {
			// When reaching the point where we have to start displaying as fixed
			if (current > item.start) {
				if (!item.$element.hasClass("fixed")) {
					item.$element.removeClass("animated show"); // Clear some classes
					item.$element.addClass("fixed"); // Set as fixed
				}
			}

			// If going up…
			if (current < previous) {
				// Check if we reached the point where to snap to initial position
				if (current < item.snap) {
					// Snap only once
					if (item.$element.hasClass("fixed")) {
						// Set a translateY as the distance between the initial position and current one
						item.$element.css("transform", "translateY(" + (current - item.initial) + "px)");
						item.$element.removeClass("fixed animated show"); // Clear all the classes (even animated)

						// Wait for the browser to apply the classes and style
						window.setTimeout(function() {
							item.$element.addClass("animated"); // Set as animated
							item.$element.attr("style", null);  // Release
						}, 50);
					}
				} else {
					// Otherwise, simply show
					item.$element.addClass("animated show");
				}
			} else {
				// If going down, hide
				item.$element.removeClass("show");
			}
		});

		// Remember the previous scroll position to know if we are going up or down
		previous = current;
	});
}());

$("[role=tablist] a").click(function(e) {
	e.preventDefault();

	var
		$this   = $(this),
		$links  = $this.parent().siblings().find("[aria-selected]"),
		$target = $("#" + $this.attr("aria-controls")),
		$panels = $target.siblings("[role=tabpanel]");

	$links.attr("aria-selected", false);
	$links.attr("tabindex", -1);

	$this.attr("aria-selected", true);
	$this.attr("tabindex", 0);

	$panels.attr("aria-hidden", true);
	$target.attr("aria-hidden", false);
});

$(".wall .title .btn").click(function(e) {
	e.preventDefault();
	$(this).parents(".wall").toggleClass("expanded");
});

$(document).ready(function() {
	var
		$burger = $("#burger"),
		$menu   = $("#menu");

	$burger.click(function() {
		$burger.removeClass("initial");
		$burger.toggleClass("close");
		$menu.toggleClass("opened");
	});
});

$document.ready(function() {
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
		selected.removeClass("selected expanded");
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
});
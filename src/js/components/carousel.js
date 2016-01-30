$document.ready(function() {
	"use strict";

	// TODO: check with multiple carousel (might occurs?)
	// TODO: fallback for older browsers?

	var
		$carousel = $(".carousel"),
		$items    = $carousel.children(),
		$controls = $(".carousel-controls"),
		$buttons  = $controls.find("button"),
		$pager    = $controls.find("li a"),
		interval  = $carousel.data("interval"),
		timer;

	function next($item) {
		var index = $items.index($item) + 1;
		return $items.eq(index <= $items.length - 1 ? index : 0);
	}

	function previous($item) {
		var index = $items.index($item) - 1;
		return $items.eq(index >= 0 ? index : $items.length - 1);
	}

	function forward() {
		var index = $pager.index($pager.filter(".selected")) + 1;
		$pager.eq(index <= $pager.length - 1 ? index : 0).click();
	}

	function backward() {
		var index = $pager.index($pager.filter(".selected")) - 1;
		$pager.eq(index >= 0 ? index : $pager.length - 1).click();
	}

	function change(options) {
		options = options || {};

		var
			direction = options.direction || "forward",
			current   = $items.filter(".selected"),
			animated  = options.animated !== undefined ? options.animated : true,
			translate = 0,
			selected,
			i = 0, count = $items.length, order;

		// Update the state if necessary
		if (options.state) {
			$carousel.data("state", options.state);
			$carousel.trigger("state-changed");
		}

		// Makes sure we have a current item (fallback to the first item)
		current = current.length ? current : $items.first();

		// Search for the new selected item
		if (options.target) {
			selected = $items.filter(options.target);

			// Abort if we couldn't find the requested target
			if (!selected.length) { return; }

			// Recompute the direction
			// First, get the delta between the selected and the current
			direction = $items.index(selected) - $items.index(current);

			// Then, if the delta is positive the selected item is forward
			if (direction > 0) {
				// If the delta is less than the half of the array, we can go forward
				direction = Math.abs(direction) < ($items.length / 2) ? "forward" : "backward";
			} else {
				direction = Math.abs(direction) < ($items.length / 2) ? "backward" : "forward";
			}
		} else {
			// By default, find the next or previous item
			selected = direction === "forward" ? next(current) : previous(current);
		}

		// Update the item's classes
		current.removeClass("selected");
		selected.addClass("selected");

		// Update the pager
		$pager.removeClass("selected");
		$pager.eq($items.index(selected)).addClass("selected");

		// Set some CSS to disable the animation and offset if going backward
		$carousel.css("left", direction === "backward" ? "-" + (count - 1) + "00%" : "");
		$carousel.css("transition", "none");
		$carousel.css("transform",  "none");

		// Loop through the items to re-order them
		// (I know, this loop might be optimized and clarified but I have no time right now)
		for (order = direction === "forward" ? 1 : count; i < count; i++) {
			current.css("order", order);

			// Count the number of items to translate, update the current item to re-order and the new order value
			translate = $items.index(current) === $items.index(selected) ? i : translate;
			current   = direction === "forward" ? next(current) : previous(current);
			order    += direction === "forward" ? 1 : -1;
		}

		translate = direction === "forward" ? -translate : translate;

		// Update the transform after a timeout, so the browser trigger the animation
		window.setTimeout(function() {
			$carousel.css("transition", animated ? "" : "none");
			$carousel.css("transform",  "translateX(" + (translate * 100) + "%)");
		}, 50);

		// Finally, trigger a "changed" event (some items might need to be notified)
		$carousel.trigger("changed");
	}

	// Listen for state-changed event to automatically switch items
	$carousel.on("state-changed", function() {
		var state = $carousel.data("state");
		switch ($carousel.data("state")) {
			case "playing":
			case "running":
				if (interval) {
					timer = window.setTimeout(function() {
						forward();

						// Calling forward will switch to the "paused" state (default behaviour for the links and buttons)
						// So, after, trigger again the state change to slide automatically
						$carousel.data("state", state);
						$carousel.trigger("state-changed");
					}, interval);
				}
				break;

			case "paused":
				window.clearTimeout(timer);
				break;
		}
	});

	// Set a specific behavior when clicking on the pager
	// We could have simply used the anchors and the hashchange event
	// Unfortunately, some browsers might jiggle because they will try to jump to the anchor
	$pager.click(function(e) {
		var hash = $pager.filter(this).attr("href");

		change({
			target: hash,
			state: "paused"
		});

		history.pushState(null, null, hash);

		e.preventDefault();
	});

	// Handle popstate (going back in history)
	$window.bind("popstate", function() {
		change({
			target: window.location.hash || $pager.first().attr("href"),
			state: "paused"
		});
	});

	// Handle existing hash when loading
	if (window.location.hash) {
		change({
			target: window.location.hash,
			state: "paused",
			animated: false
		});

		// Reset the default scroll caused by the anchor
		window.setTimeout(function() {
			$carousel.parent().scrollLeft(0);
		}, 1);
	}

	// Bind the buttons
	$buttons.filter("[data-direction=forward]").click(forward);
	$buttons.filter("[data-direction=backward]").click(backward);

	// Bind some swipe effects
	$carousel.hammer().bind("swiperight", backward);
	$carousel.hammer().bind("swipeleft",  forward);

	// Bind some keys
	$window.keydown(function(e) {
		switch (e.keyCode) {
			case 39: return forward();  // →
			case 37: return backward(); // ←
		}
	});

	$carousel.trigger("state-changed"); // Trigger a state-changed to launch (or not) the timer
});
document.body.classList.add("js");

var
	$document = $(document),
	$window   = $(window);

function throttle(fn, time) {
	time || (time = 250);
	var wait;

	return function() {
		if (!wait) {
			fn.apply(this, arguments);
			wait = true;
			window.setTimeout(function() { wait = false; }, time);
		}
	}
}

$document.ready(function() {
	var
		affixes  = [],
		previous = 0;

	function find() {
		$("[data-spy=affix]").each(function(index) {
			var
				affix  = affixes[index] || { $element: $(this) },
				offset = affix.$element.offset().top;

			// Avoid recomputing offsets if the element is already fixed
			if (affix.$element.hasClass("fixed")) { return; }

			if (affix.initial !== offset) {
				affix.initial = offset;
				affix.height  = affix.$element.height(),
				affix.start   = offset + affix.height;
				affix.snap    = offset + ((affix.$element.data("snap") * affix.height) || 0);
			}

			affixes[index] = affix; // Use affixes[index] instead of push because we might use this function to update as well
		});
	}

	function check() {
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
	}

	$window.scroll(throttle(check));

	// Find affixes (and their offets)
	find(); // Right now
	$window.load(find); // Check if the offset's haven't changed due to image loading
	$window.bind("font-active", find); // Check if the offset's haven't changed due to font loading
	$window.resize(throttle(find)); // Check if the offset's haven't changed due to window resize
});

$document.ready(function() {
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

		// Add the anchor to the URL, so the user can copy the URL
		history.pushState(null, null, "#" + $this.attr("aria-controls"));

		// Scroll to the target since we might be below
		$window.scrollTop($target.offset().top);
	});

	// Check if a hash is present so we could switch to it
	if (window.location.hash) {
		var
			hash    = window.location.hash,
			$target = $(hash);

		if ($target.hasClass("tab")) {
			$("#" + $target.attr("aria-labelledby")).click();
		} else if ($target.parents(".tab").length) {
			$("#" + $target.parents(".tab").attr("aria-labelledby")).click();
			history.pushState(null, null, hash);
		}
	}
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

$(document).ready(function() {
	$(".player").each(function() {
		var
			$player = $(this),
			 player = $player.find("audio")[0],
			$button = $player.find("button");

		player.addEventListener("playing", function() {
			$player.addClass("animated playing");
		});

		player.addEventListener("pause", function() {
			$player.removeClass("playing");
		});

		player.addEventListener("ended", function() {
			// Remove the "animated" class to reset the CSS animation
			$player.removeClass("animated");
		});

		function handleDuration() {
			$player.css("animation-duration", player.duration / 2 + "s, " + player.duration + "s");
		}

		player.addEventListener("durationchange", handleDuration);
		if (player.duration) { handleDuration(); }

		$button.click(function() {
			player.paused ? player.play() : player.pause();
		});
	});
});
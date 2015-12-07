document.body.classList.add("js");

var
	$document = $(document),
	$window   = $(window);

// Throttle the calls for a function (avoid calling too many times)
function throttle(fn, time) {
	var wait;

	return function() {
		if (!wait) {
			fn.apply(this, arguments);
			wait = true;
			window.setTimeout(function() { wait = false; }, time || 250);
		}
	}
}

// Enable the position: sticky elements
$document.ready(function() {
	$(".sticky").Stickyfill();
});

// Enable the sticky headers
$document.ready(function() {
	var
		headers  = [],
		previous = 0;

	$(".sticky-header").each(function() {
		headers.push({ $element: $(this) });
	});

	$window.scroll(throttle(function() {
		var current = $window.scrollTop();

		headers.forEach(function(header, index) {
			// Recompute the start position of element not yet sticky
			// This might be not really performant but way easier than trying to guess when the element's offset is going to change
			if (!header.$element.hasClass("sticky")) {
				header.start = header.$element.offset().top + header.$element.height();
			}

			// When reaching the point where we have to start displaying as fixed
			if (current > header.start) {
				if (!header.$element.hasClass("sticky")) {
					header.$element.removeClass("animated show"); // Clear some classes
					header.$element.addClass("sticky"); // Set as sticky
					Stickyfill.add(header.$element[0]);
				}
			}

			// If this element isn't sticky yet we can stop
			if (!header.$element.hasClass("sticky")) { return; }

			// If going up…
			if (current < previous) {
				// Check if we need to reset the header to its initial position
				if (current < header.start) {
					header.$element.removeClass("sticky animated show");
					Stickyfill.remove(header.$element[0]);
				} else {
					// Otherwise, simply show
					header.$element.addClass("animated show");
				}
			} else {
				// If going down, hide
				header.$element.removeClass("show");
			}
		});

		// Remember the previous scroll position to know if we are going up or down
		previous = current;
	}, 100)); // Throttle, but with an acceptable delay (250ms is too high)
});

// Enable tabs
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
		var $target = $(window.location.hash);
		if ($target.hasClass("tab")) {
			$("#" + $target.attr("aria-labelledby")).click();
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
		$burger.removeClass("initial"); // The initial class is used to disable animations
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

// Audio player
$(document).ready(function() {
	$(".player").each(function() {
		var
			$container = $(this),
			$player    = $container.find("audio"),
			$button    = $container.find("button");

		$player.on("playing", function() {
			$container.addClass("animated playing");
		});

		$player.on("pause", function() {
			$container.removeClass("playing");
		});

		$player.on("ended", function() {
			$container.removeClass("animated"); // Remove the "animated" class to reset the CSS animation
		});

		$player.on("durationchange", function() {
			var duration = $player[0].duration || 0;
			$container.css("animation-duration", duration / 2 + "s, " + duration + "s");
		});

		$player.trigger("durationchange");

		$button.click(function() {
			var player = $player[0];
			player.paused ? player.play() : player.pause();
		});
	});
});
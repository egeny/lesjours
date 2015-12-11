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

// Set the layout and behaviour for .mini
$document.ready(function() {
	var
		$anchors   = $("[data-mini]"),
		$container = $(".article-container"),
		$minis     = [], // An array of the associated mini
		previous   = { height: 0, top: 0 }, // An object to check if we are going to overlap the previous mini
		offset     = (68 / 2) + 6 + 8, // The offset to the center of the image
		shown      = 0;

	// Extend the anchors' elements once and for all
	$anchors = $anchors.map(function(index, element) { return $(element); });

	function layout() {
		if (!$anchors.length) { return; }

		// Retrieve the container's top offset relative to the document
		// It is easier and more accurate to use document's offset
		var container = $container.offset().top;

		// Position every #mini-X so they will be vertically centered to the anchor
		$anchors.each(function(index, $element) {
			var
				$mini = $minis[index] || ($minis[index] = $("#mini-" + $element.data("mini"))),
				top   = $element.offset().top - container + ($element.height() / 2) - offset;

			// Check if this mini is going to overlap the previous one and fix
			if (top > previous.top && top < previous.top + previous.height) {
				top += previous.top + previous.height - top;
			}

			$mini.css("top", top);
			previous.top    = top;
			previous.height = $mini.outerHeight(true);
		});
	}

	layout();
	$window.resize(throttle(layout));
	$window.load(layout);
	$window.bind("font-active", layout);

	// Override the links if the associated mini contain a player with a button
	$anchors.each(function(index, $element) {
		var
			$mini   = $minis[index],
			$button = $mini.find(".player button");

		function hover() { $mini.addClass("active");    }
		function blur()  { $mini.removeClass("active"); }

		$element.mouseenter(hover);
		$element.focus(hover);
		$element.mouseleave(blur);
		$element.blur(blur);

		if (!$button.length) { return; }

		$element.click(function(e) {
			e.preventDefault();
			$button.click();
		});
	});

	function show() {
		// Avoid unnecessary computation if everything is already shown
		if (shown === $minis.length) { return; }

		var
			current = $window.scrollTop(),
			height  = $window.height();

		// Simply check if the anchor entered the viewport and add a "show" class to the associated mini
		$anchors.each(function(index, $element) {
			if ($minis[index].hasClass("show")) { return; }

			var offset = $element.offset().top;
			if (offset > current && offset < current + height) {
				$minis[index].addClass("show");
				shown++;
			}
		});
	}

	show();
	$window.scroll(throttle(show));
});

// Fade the share when necessary
$document.ready(function() {
	var timer, $share = $(".share");

	function stop() { window.clearTimeout(timer); }
	function resume() {
		timer = window.setTimeout(function() {
			$share.removeClass("show");
		}, $share.data("fade"));
	}

	$share.mouseenter(stop);
	$share.mouseleave(resume);
	$share.find("a, button").focus(stop).blur(resume);

	$window.scroll(throttle(function() {
		$share.addClass("show");
		stop();
		resume();
	}));
});

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

	$window.scroll(function() {
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
	});
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
		//$window.scrollTop($target.offset().top);
	});

	// Check if a hash is present so we could switch to it
	if (window.location.hash) {
		var $target = $(window.location.hash);
		if ($target.hasClass("tab")) {
			$("#" + $target.attr("aria-labelledby")).click();
		}
	}
});

// Set the behaviour for the wall's title buttons
$document.ready(function () {
	$(".wall .title .btn").click(function(e) {
		var
			$this     = $(this),
			$wall     = $this.parents(".wall"),
			$carousel = $wall.parent();

		$wall.toggleClass("expanded");
		$carousel.data("state", "paused");
		$carousel.trigger("state-changed");

		e.preventDefault();
	});
});

// Set the burger's button behaviour
$document.ready(function() {
	var
		$burger = $("#burger"),
		$menu   = $("#menu");

	$burger.click(function() {
		$burger.removeClass("initial"); // The initial class is used to disable animations
		$burger.toggleClass("close");
		$menu.toggleClass("opened");
	});
});

// Carousel
$document.ready(function() {
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
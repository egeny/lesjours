$document.ready(function() {
	var
		$anchors   = $("[data-mini]"),
		$container = $(".article-container"),
		$minis     = [], // An array of the associated mini
		offset     = (68 / 2) + 6 + 8, // The offset to the center of the image
		shown      = 0;

	// Extend the anchors' elements once and for all
	$anchors = $anchors.map(function(index, element) { return $(element); });

	function layout() {
		if (!$anchors.length) { return; }

		// Retrieve the container's top offset relative to the document
		// It is easier and more accurate to use document's offset
		var
			container = $container.offset().top,
			previous  = { height: 0, top: 0 };// An object to check if we are going to overlap the previous mini

		// Position every #mini-X so they will be vertically centered to the anchor
		$anchors.each(function(index, $element) {
			var
				$mini = $minis[index] || ($minis[index] = $("#mini-" + $element.data("mini"))),
				top   = $element.offset().top - container + ($element.height() / 2) - offset;

			// Check if this mini is going to overlap the previous one and fix
			if (top < previous.top + previous.height) {
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
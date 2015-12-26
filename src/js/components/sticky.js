$document.ready(function() {
	$(".sticky").Stickyfill();

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
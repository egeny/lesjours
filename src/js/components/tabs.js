$document.ready(function() {
	"use strict";

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
		// $window.scrollTop($target.offset().top);
	});

	// Check if a hash is present so we could switch to it
	if (window.location.hash) {
		var $target = $(window.location.hash);
		if ($target.hasClass("tab")) {
			$("#" + $target.attr("aria-labelledby")).click();
		}
	}
});
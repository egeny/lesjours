$document.ready(function() {
	"use strict";

	// Close the modal when clicking on its background
	$(".modal").each(function() {
		var $this = $(this);
		$this.click(function(e) {
			if (e.target === $this[0]) {
				window.location.hash = "";
			}
		});
	});
});
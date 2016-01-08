$document.ready(function() {
	"use strict";

	$(".modal").each(function() {
		var $this = $(this);
		$this.click(function(e) {
			if (e.target === $this[0]) {
				window.location.hash = "";
			}
		});
	});

	$(".modal .close").click(function() {
		window.location.hash = "";
	});
});
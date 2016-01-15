$document.ready(function() {
	"use strict";

	$(".cover .btn").click(function(e) {
		var $cover = $(this).parents(".cover");

		$cover.toggleClass("expanded");

		if ($cover.parent().hasClass("carousel")) {
			$cover.parent().data("state", "paused").trigger("state-changed");
		}

		e.preventDefault();
	});
});
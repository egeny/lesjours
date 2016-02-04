$document.ready(function() {
	"use strict";

	$("#bar button").click(function() {
		$(this).parent().addClass("hidden");
	});
});
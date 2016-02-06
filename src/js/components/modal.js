$document.ready(function() {
	"use strict";

	var cache = {};

	function hashchange() {
		var
			hash  = window.location.hash,
			modal = cache[hash] || $(hash + "[role=dialog]");

		if (hash) {
			cache.scroll = $window.scrollTop();
			modal.length && $body.addClass("has-modal");
		} else {
			$body.removeClass("has-modal");
			$window.scrollTop(cache.scroll || 0);
		}
	}

	$window.bind("hashchange", hashchange);
	window.location.hash && hashchange();

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
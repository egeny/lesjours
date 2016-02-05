$document.ready(function() {
	"use strict";

	var
		$carousel = $(".carousel"),
		$covers   = $carousel.find(".cover");

	// Don't bother if there is no carousel (avoid an error with Hammer)
	if (!$carousel.length) { return; }

	function expand(i) {
		var $cover = $covers.eq(i);

		if ($cover.hasClass("expanded")) {
			window.location = $cover.find("[href]").attr("href");
		} else {
			$cover.addClass("expanded");
			$carousel.data("state", "paused").trigger("state-changed");
		}
	}

	function resume(i) {
		var $cover = $covers.eq(i);

		if ($cover.hasClass("expanded")) {
			$cover.removeClass("expanded");
			$carousel.data("state", "running").trigger("state-changed");
		}
	}

	function swiped(e) {
		var
			$cover = $covers.filter(".selected"),
			index  = $cover.length ? $covers.index($cover) : 0;

		if (e.type === "swipedown") {
			resume(index);
		} else {
			expand(index);
		}
	}

	$covers.find("[href]").click(function(e) {
		var $cover = $covers.filter(".selected");
		expand($cover.length ? $covers.index($cover) : 0);
		e.preventDefault();
	});

	// Expand the cover if, on page load, there is an hash
	if (window.location.hash) {
		window.setTimeout(function() {
			$covers.filter(window.location.hash).addClass("expanded");
		}, 300);
	}

	$carousel.on("changed", function() {
		$covers.removeClass("expanded");
	});

	$carousel.hammer().bind("swipedown", swiped);
	$carousel.hammer().bind("swipeup",   swiped);

	// By default Hammer doesn't allow swiping down (for obvious reasons)
	$carousel.data("hammer").get("swipe").set({ direction: Hammer.DIRECTION_ALL });
});
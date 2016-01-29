$document.ready(function() {
	"use strict";

	var
		$carousel = $(".carousel"),
		$covers   = $carousel.find(".cover");

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
			expand(index);
		} else {
			resume(index);
		}
	}

	$covers.find("[href]").click(function(e) {
		var $cover = $covers.filter(".selected");
		expand($cover.length ? $covers.index($cover) : 0);
		e.preventDefault();
	});

	$carousel.hammer().bind("swipedown", swiped);
	$carousel.hammer().bind("swipeup",   swiped);

	// By default Hammer doesn't allow swiping down (for obvious reasons)
	$carousel.data("hammer").get("swipe").set({ direction: Hammer.DIRECTION_ALL });
});
$document.ready(function() {
	var timer, $share = $(".share");

	function stop() { window.clearTimeout(timer); }
	function resume() {
		timer = window.setTimeout(function() {
			$share.removeClass("show");
		}, $share.data("fade"));
	}

	$share.mouseenter(stop);
	$share.mouseleave(resume);
	$share.find("a, button").focus(stop).blur(resume);

	$window.scroll(throttle(function() {
		$share.addClass("show");
		stop();
		resume();
	}));
});
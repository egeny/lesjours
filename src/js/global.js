document.body.classList.add("js");

var
	$document = $(document),
	$window   = $(window);

// Throttle the calls for a function (avoid calling too many times)
function throttle(fn, time) {
	var wait;

	return function() {
		if (!wait) {
			fn.apply(this, arguments);
			wait = true;
			window.setTimeout(function() { wait = false; }, time || 250);
		}
	}
}

// Set the behaviour for the wall's title buttons
$document.ready(function () {
	$(".wall .title .btn").click(function(e) {
		var
			$this     = $(this),
			$wall     = $this.parents(".wall"),
			$carousel = $wall.parent();

		$wall.toggleClass("expanded");
		$carousel.data("state", "paused");
		$carousel.trigger("state-changed");

		e.preventDefault();
	});
});
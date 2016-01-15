/* eslint no-unused-vars: 0 */
var
	$body     = $(document.body),
	$document = $(document),
	$window   = $(window);

$body.addClass("js");

// Throttle the calls for a function (avoid calling too many times)
function throttle(fn, time) {
	var wait;

	return function() {
		if (!wait) {
			fn.apply(this, arguments);
			wait = true;
			window.setTimeout(function() { wait = false; }, time || 250);
		}
	};
}
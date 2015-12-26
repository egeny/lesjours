$document.ready(function() {
	var
		$burger = $("#burger"),
		$menu   = $("#menu");

	$burger.click(function() {
		$burger.removeClass("initial"); // The initial class is used to disable animations
		$burger.toggleClass("close");
		$menu.toggleClass("opened");
	});
});
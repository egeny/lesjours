$document.ready(function() {
	$(".player").each(function() {
		var
			$container = $(this),
			$player    = $container.find("audio"),
			$button    = $container.find("button");

		$player.on("playing", function() {
			$container.addClass("animated playing");
		});

		$player.on("pause", function() {
			$container.removeClass("playing");
		});

		$player.on("ended", function() {
			$container.removeClass("animated"); // Remove the "animated" class to reset the CSS animation
		});

		$player.on("durationchange", function() {
			var duration = $player[0].duration || 0;
			$container.css("animation-duration", duration / 2 + "s, " + duration + "s");
		});

		$player.trigger("durationchange");

		$button.click(function() {
			var player = $player[0];
			player.paused ? player.play() : player.pause();
		});
	});
});
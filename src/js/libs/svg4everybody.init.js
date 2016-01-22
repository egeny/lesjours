svg4everybody({
	fallback: function(src, svg, use) {
		return src.replace(".svg#", "/") + ".png";
	}
});
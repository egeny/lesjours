# Colors

<style>
.container {
	display: -webkit-box;
	display: -webkit-flex;
	display: -ms-flexbox;
	display: flex;
	-webkit-box-align: center;
	-webkit-align-items: center;
	-ms-flex-align: center;
	align-items: center;
	-webkit-box-pack: center;
	-webkit-justify-content: center;
	-ms-flex-pack: center;
	justify-content: center;
}
.sample {
	display: -webkit-box;
	display: -webkit-flex;
	display: -ms-flexbox;
	display: flex;
	-webkit-box-align: center;
	-webkit-align-items: center;
	-ms-flex-align: center;
	align-items: center;
	-webkit-box-pack: center;
	-webkit-justify-content: center;
	-ms-flex-pack: center;
	justify-content: center;

	width:  110px;
	height: 110px;
	padding: 5px;
	margin: 0 5px;

	text-align: center;
	color: #FFF;
}
</style>

With SASS, use `$color-name` to use a color (e.g. `$color-brand`).

Some CSS classes are also available:

* `.color-name` — Set the `color` property
* `.bg-name` — Set the `background-color` property

All these colors might be updated in the `variables.scss` file.  

## Main

<div class="container">
	<div class="sample" style="background: #C83E2C">Brand<br/>#C83E2C</div>
	<div class="sample" style="background: #414141">Main<br/>#414141</div>
	<div class="sample" style="background: #000">Black<br/>#000</div>
	<div class="sample" style="background: #FFF; color: #414141">White<br/>#FFF</div>
</div>

## Fonts

These colors might be used for some fonts:

<div class="container">
	<div class="sample" style="background: #333">Darker<br/>#333</div>
	<div class="sample" style="background: #717171">Dark<br/>#717171</div>
	<div class="sample" style="background: #D7D7D7; color: #414141">Light<br/>#D7D7D7</div>
</div>

## Links

These colors are to use for links and interactive elements.

<div class="container">
	<div class="sample" style="background: #F3DF93; color: #414141">Media<br/>#F3DF93</div>
	<div class="sample" style="background: #E49287">External<br/>#E49287</div>
</div>

In some cases, these colors may be replace with `brand` or an `obsession` color.

## Obsessions

<div class="container">
	<div class="sample" style="background: #A889AD">Obsession 1<br/>#A889AD</div>
	<div class="sample" style="background: #7F8E68">Obsession 2<br/>#7F8E68</div>
	<div class="sample" style="background: #679EA7">Obsession 3<br/>#679EA7</div>
</div>

Note that the opacity might be changed in some contexts.

There is no SASS variable or CSS classes for these colors. Instead, they are stored in a list named `$colors-obsessions`.

When you need to style something based on an obsession, the recommended way is:

```sass
@each $color in $colors-obsessions {
	$i: index($colors-obsessions, $color);
	article.obsession-#{$i} .snake { border-color: $color; }
}
```
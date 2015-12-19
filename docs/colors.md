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
	margin: 5px;

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
	<div class="sample" style="background: #F1C5C0; color: #414141">Brand light<br/>#C83E2C</div>
</div>
<div class="container">
	<div class="sample" style="background: #414141">Main<br/>#414141</div>
	<div class="sample" style="background: #000">Black<br/>#000</div>
	<div class="sample" style="background: #FFF; color: #414141; border: 1px solid #414141">White<br/>#FFF</div>
</div>

## Fonts

These colors might be used for some fonts:

<div class="container">
	<div class="sample" style="background: #290D09">Darkest<br/>#290D09</div>
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

In some cases, these colors may be replace with `brand`, an `obsession` or an `obsession-light` color.

## Obsessions

<div class="container">
	<div class="sample" style="background: #EB8A31">Obsession 1<br/>#EB8A31</div>
	<div class="sample" style="background: #F5C498; color: #414141">Obsession 1 (light)<br/>#F5C498</div>
</div>
<div class="container">
	<div class="sample" style="background: #679EA7">Obsession 2<br/>#679EA7</div>
	<div class="sample" style="background: #B3CED3; color: #414141">Obsession 2 (light)<br/>#B3CED3</div>
</div>
<div class="container">
	<div class="sample" style="background: #DBD2A2">Obsession 3<br/>#DBD2A2</div>
	<div class="sample" style="background: #EDE8D0; color: #414141">Obsession 3 (light)<br/>#EDE8D0</div>
</div>
<div class="container">
	<div class="sample" style="background: #719A82">Obsession 4<br/>#719A82</div>
	<div class="sample" style="background: #B8CCC0; color: #414141">Obsession 4 (light)<br/>#B8CCC0</div>
</div>
<div class="container">
	<div class="sample" style="background: #D71F85">Obsession 5<br/>#D71F85</div>
	<div class="sample" style="background: #EB8FC2; color: #414141">Obsession 5 (light)<br/>#EB8FC2</div>
</div>
<div class="container">
	<div class="sample" style="background: #A889AD">Obsession 6<br/>#A889AD</div>
	<div class="sample" style="background: #D3C4D6; color: #414141">Obsession 6 (light)<br/>#D3C4D6</div>
</div>

There is no SASS variable or CSS classes for these colors. Instead, they are stored in a list named `$colors-obsessions`.

When you need to style something based on an obsession, the recommended way is:

```sass
@each $colors in $colors-obsessions {
	$i: index($colors-obsessions, $colors);
	.obsession-#{$i} .snake { border-color: map-get($colors, "main");  }
	.obsession-#{$i} .btn   { border-color: map-get($colors, "light"); }
}
```
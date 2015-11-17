# Grid

The framework is inspired by [Twitter's Bootstrap's grid system](http://getbootstrap.com/css/#grid).

The grid is fluid which means a column (`.col`) size is never fixed but is a percentage of its parent (usually a `.row`). It use [flexbox](http://caniuse.com/#feat=flexbox) (recommended) when a `.has-flex` class is present on an ancestor, otherwise the layout is based on `float`.

Here is a summary of the grid's configuration:

|              | Small devices | Medium devices | Large devices |
|--------------|---------------|----------------|---------------|
| Columns      | 4             | 8              | 12            |
| Breakpoint   | ø (default)   | 480px          | 840px         |
| Margins      | 1rem (16px)   | 1.5rem (24px)  | 1.5rem (24px) |
| Gutters      | 1rem (16px)   | 1.5rem (24px)  | 1.5rem (24px) |

You can change this configuration in `variables.scss`.

## Containers

Use a container (`.container`) to host row(s).  
It will set the grid's margins and, if you use `container-fixed`, set a `max-width` to the breakpoint value.

### Example
```html
<!-- Fluid container (width: 100%) -->
<div class="container">…</div>

<!-- Fixed container -->
<!-- Small devices:      width: 100%  -->
<!-- Medium devices: max-width: 480px -->
<!-- Large devices:  max-width: 840px -->
<div class="container-fixed">…</div>
```

## Rows

Use a row (`.row`) to host column(s). A row should only host column(s).  
It will set its display as `flex` (when an ancestor have a `has-flex` class) and set some negative margins (to avoid the grid's margin + column's gutter).

To avoid the negative margins, use `.row-full`, `.row-sm-full`, `.row-md-full` and/or `.row-lg-full` in **addition** of `.row`.

### Example
```html
<div class="container">
	<div class="row">…</div>
</div>
```

## Align rows (flexbox only)

You can use the following utility classes to align **vertically** the rows in its container.

* `.row-top` — Align the row at the top of the container
* `.row-center` — Align the row at the center of the container
* `.row-bottom` — Align the row at the bottom of the container

You can also use prefixed classes to target specific devices:

* Small devices
	* `.row-sm-top`
	* `.row-sm-center`
	* `.row-sm-bottom`
* Medium devices
	* `.row-md-top`
	* `.row-md-center`
	* `.row-md-bottom`
* Large devices
	* `.row-lg-top`
	* `.row-lg-center`
	* `.row-lg-bottom`

### Example
```html
<div class="container">
	<!-- Align the row on top on all devices -->
	<div class="row row-top">…</div>

	<!-- Align only small devices -->
	<div class="row row-sm-top">…</div>

	<!-- Align only medium devices -->
	<div class="row row-md-top">…</div>

	<!-- Align only large devices -->
	<div class="row row-lg-top">…</div>

	<!-- You can align in different ways regarding the device -->
	<!-- For example, to align on top on small devices and centered on medium and large devices -->
	<div class="row row-sm-top row-md-center row-ld-center">…</div>
</div>
```

## Columns

Use a column (`.col`) to host the content.
It will set its `width` at 100% (`flex-basis` with flexbox) and its `box-sizing` to **`border-box`**. In `float` mode, its `float` property will be set at `left`.

Usually, you will **not** use the `.col` class but use a specific column size class (see below).

### Example
```html
<div class="container">
	<div class="row">
		<div class="col">Hello</div>
	</div>
</div>
```

## Columns size

Use a **column size class** to quickly and easily set the width of a column.  
It will simply set its `width` (`flex-basis` with flexbox) and its gutters.

All the classes are prefixed to target specific devices:

* Small devices: `.col-sm-1` to `.col-sm-4`
* Medium devices: `.col-md-1` to `.col-md-8`
* Large devices: `.col-lg-1` to `.col-lg-12`

### Example
```html
<div class="container">
	<div class="row">
		<!-- The size of the column will be: -->
		<!-- 1 column on a small device   ((100% / 4)  * 1 = 25%) -->
		<!-- 2 columns on a medium device ((100% / 8)  * 2 = 25%) -->
		<!-- 3 columns on a large device  ((100% / 12) * 3 = 25%) -->
		<div class="col-sm-1 col-md-2 col-lg-3">Hello</div>
	</div>
</div>
```

## Aligning columns (flexbox only)

You can use the following utility classes to align **horizontally** the columns in its row.

* `.col-top` — Align the column to the left of the row
* `.col-center` — Align the column to the center of the row
* `.col-bottom` — Align the column to the right of the row

You can also use prefixed classes to target specific devices:

* Small devices
	* `.col-sm-top`
	* `.col-sm-center`
	* `.col-sm-bottom`
* Medium devices
	* `.col-md-top`
	* `.col-md-center`
	* `.col-md-bottom`
* Large devices
	* `.col-lg-top`
	* `.col-lg-center`
	* `.col-lg-bottom`

### Example
```html
<div class="container">
	<div class="row">
		<!-- Align the column on top on all devices -->
		<div class="col col-top">

		<!-- Align only small devices -->
		<div class="col col-sm-top">…</div>

		<!-- Align only medium devices -->
		<div class="col col-md-top">…</div>

		<!-- Align only large devices -->
		<div class="col col-lg-top">…</div>

		<!-- You can align in different ways regarding the device -->
		<!-- For example, to align on top on small devices and centered on medium and large devices -->
		<div class="col col-sm-top col-md-center col-ld-center">…</div>
	</div>
</div>
```

## Ordering columns (flexbox only)

You can use the following utility classes to change the order of some columns. This is pretty basic, if you need something more advanced just use the `order` property.

* `.col-first` — Set the column as first
* `.col-last` — Set the column as last

You can also use prefixed classes to target specific devices:

* Small devices
	* `.col-sm-first`
	* `.col-sm-last`
* Medium devices
	* `.col-md-first`
	* `.col-md-last`
* Large devices
	* `.col-lg-first`
	* `.col-lg-last`

### Example
```html
<div class="container">
	<div class="row">
		<div class="col col-last">1</div>
		<div class="col">2</div>
		<div class="col col-first">3</div>
	</div>
</div>
```

The columns will be displayed in that order: 3, 2, 1.  
In this case, using `flex-direction: row-reverse` would have done the same thing.

## Offsetting columns

You can use the following classes to quickly offset a column:

* `.col-*-top-*` — Add a margin top of the width of a column
* `.col-*-right-*` — Add a margin right of the width of a column
* `.col-*-bottom-*` — Add a margin bottom of the width of a column
* `.col-*-left-*` — Add a margin left of the width of a column

All the classes are prefixed to target specific devices:

* Small devices: `.col-sm-top-1` to `.col-sm-top-4`
* Medium devices: `.col-md-top-1` to `.col-md-top-8`
* Large devices: `.col-lg-top-1` to `.col-lg-top-12`

For example, using `.col-sm-top-2` will add a `margin-top` having the value of 2 columns, but only on small devices.

You can also use the following classes to quickly offset a column based on the gutter size:

* `.gutter-top-*` — Add a margin top of the value of the gutter
* `.gutter-bottom-*` — Add a margin bottom of the value of the gutter

All the classes are **also** prefixed to target specific devices:

* Small devices: `.gutter-sm-top-1` to `.gutter-sm-top-12`
* Medium devices: `.gutter-md-top-1` to `.gutter-md-top-12`
* Large devices: `.gutter-lg-top-1` to `.gutter-lg-top-12`

Please note the gutter size is **half** of the configured gutter size (`gutter-sm-top-1` will add a `margin-top` of **0.5rem** and not **1rem**). This is because in the configuration the `$gutter` variable represent the **full gutter** ; but in CSS the gutter are applied to each sides of a column so they are divided by 2.

### Examples
```html
<div class="container">
	<div class="row">
		<!-- Offset the column of 1 column on the left, only on small devices -->
		<div class="col col-sm-left-1"></div>

		<!-- Offset the column of 1 column on the left, on all devices -->
		<div class="col col-sm-left-1 col-md-left-1 col-lg-left-1"></div>

		<!-- Add a margin-bottom of one full gutter -->
		<div class="col gutter-bottom-2"></div>

		<!-- Add a margin-bottom of one full gutter, but a half gutter on small devices -->
		<div class="col gutter-bottom-2 gutter-sm-bottom-1"></div>
	</div>
</div>
```

## Sass mixins

The grid framework includes some mixins to ease the device targeting:

### Add a media query for medium devices

```sass
@include media-md {
	.foo { background: red; }
}
```
The `background: red` will be applied on medium and large devices (the media query include only a `min-width`).

### Add a media query for large devices

```sass
@include media-lg {
	.foo { background: green; }
}
```
The `background: green` will be applied on large devices.

There is **no** media query for small devices since it should be the **default**.

## Sass functions

The grid framework includes some function to ease some calculations:

### Retrieve the gutter for a specific device

```sass
.foo { margin-bottom: grid-gutter("sm") * 2; }
```
The `margin-bottom` will have a value of 2 **full gutters** (this function return the gutter value from the configuration).

```sass
.foo { margin: 0 grid-margin("sm"); }
```
The `margin` will have a value of `0 1rem` (this function return the margin value from the configuration).

Most of the time, you will have to use these functions with the mixins since the margins and gutters are differents from a small device to a medium device.
# Typography

There 3 main fonts:

* [Archer](http://www.typography.com/fonts/archer/overview/) for the body
* [Brandon Grotesque](https://typekit.com/fonts/brandon-grotesque) for the headings
* [Proxima Nova](https://typekit.com/fonts/proxima-nova) for contextual texts

## Headings

The heading (`<h1>` to `</h6>`) are **not** styled by default. Either use one of the following class in the HTML or extend it using SASS.

The `.h2` to `.h6` classes use the following CSS:

```sass
.h2, .h3, .h4, .h5, .h6 {
	@include font-brandon;
	@include font-weight-black;
	line-height: 1;
	margin-bottom: .5em;
}
```

Note there is no `.h1` because the `<h1>` is reserved for the brand's name and logo.

These classes will also set a `font-size`:

* `.h2` will set to **3em** (42px)
* `.h3` will set to **2.5em** (35px)
* `.h4` will set to **2em** (28px)
* `.h5` will set to **1.5em** (21px)
* `.h6` will set to **1em** (14px)

Starting with medium devices, these classes have a bigger `font-size`:

* `.h2` will set to **4.374em** (70px)
* `.h3` will set to **3.5em** (56px)
* `.h4` will set to **2.6875em** (43px)
* `.h5` will set to **1.8125em** (29px)
* `.h6` will set to **1em** (16px)

## Styles

## Mixins

To easily set the font stack, use the following mixins:

```sass
@include font-archer
@include font-brandon
@include font-proxima
```

This will set the `font-family` and `font-style` (to `normal`).

Please note you should avoid to use `font-archer` since it is the **default**.

Font-weight may be a nightmare, so you should use the following mixins:

```sass
@include font-weight-thin($font)
@include font-weight-extra-light($font)
@include font-weight-light($font)
@include font-weight-regular($font)
@include font-weight-medium($font)
@include font-weight-semi-bold($font)
@include font-weight-bold($font)
@include font-weight-extra-bold($font)
@include font-weight-black($font)
```

You may omit `$font` except if you use Archer since its weight are poorly referenced. In this case, simply use:

```sass
.foo {
	@include font-archer;
	@include font-weight-bold("archer");
}
```
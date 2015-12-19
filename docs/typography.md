# Typography

There 3 main fonts:

* [Archer](http://www.typography.com/fonts/archer/overview/) for the body
* [Brandon Grotesque](https://typekit.com/fonts/brandon-grotesque) for the headings
* [Proxima Nova](https://typekit.com/fonts/proxima-nova) for contextual texts

## Headings

The heading (`<h1>` to `<h6>`) are **not** styled by default. Either use one of the following class in the HTML or extend it using SASS.

The `.h2` to `.h6` classes use the following CSS:

```sass
.h2, .h3, .h4, .h5, .h6 {
	@extend %font-brandon;
	@include font-weight-black("brandon");
	line-height: 1;
}
```

Note there is no `.h1` because the `<h1>` is reserved for the brand's name and logo.

These classes will also set a `font-size`:

* `.h2` will set to **2rem** (32px)
* `.h3` will set to **1.75rem** (28px)
* `.h4` will set to **1.5rem** (24px)
* `.h5` will set to **1.125rem** (18px)
* `.h6` will set to **.8125rem** (13px)

Starting with medium devices, these classes have a bigger `font-size`:

* `.h2` will set to **4.375rem** (70px)
* `.h3` will set to **3.125rem** (50px)
* `.h4` will set to **2.25rem** (36px)
* `.h5` will set to **1.5rem** (24px)
* `.h6` will set to **.875rem** (14px)

Please note these classes also include a `margin-bottom` and a higher `line-height` for `.h5` and `.h6`.

## Styles

You can use the following classes to quickly set some specific typographic styles:

| Class                | Font    | Weight    | Size    | Line-height |
|----------------------|---------|-----------|---------|-------------|
| `.p`                 | Archer  | medium    | inherit | 1.5         |
| `.p-larger`          | Archer  | semi-bold | ~ +1px  | 1.333       |
| `.style-meta`        | Proxima | bold      | .8125em | 2           |
| `.style-meta-large`  | Proxima | regular   | 1.5em   | 1.75        |
| `.style-meta-larger` | Proxima | light     | 2em     | 1.666       |

Note that the `style-meta` is the only one having a `color` ([$color-dark](colors.html)).

## Fonts

Although it is not recommended to using a specific font (prefer extending a style), you may so by extending the following extend-only classes:

```sass
@extend %font-archer;
@extend %font-archer-smcp; // Small capitals version
@extend %font-brandon;
@extend %font-proxima;
```

**Never** use `font-family` with a font name, extend these classes to use a font stack.

Please note you should avoid to extend `%font-archer` since it is the **default**.

## Mixins

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

In order to keep a trace of used font-weights, and also because Archer’s font-weights are strange, **always** specify the font associated:

```sass
.foo {
	@extend %font-proxima;
	@include font-weight-bold("proxima");
}
```
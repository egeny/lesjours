# Writing

This document overview the semantics and styles available when writing an article.

## Paragraphs

Use a paragraph to develop a point.

```html
<p></p>
```

## Headings

Use headings to structure an article.

There is 4 levels available. The level one (`<h1>`) is reserved for the page's title and the level two (`<h2>`) for the article's title.

```html
<h3>Article heading level 1</h3>
<h4>Article heading level 2</h4>
<h5>Article heading level 3</h5>
<h6>Article heading level 4</h6>
```

This example create this structure:

```
Article heading level 1
  └── Article heading level 2
    └── Article heading level 3
      └── Article heading level 4
```

The level closure is implicit ; you just have to use a lower level:

```html
<h3>Article heading level 1</h3>
<h4>Article heading level 2</h4>
<h3>Article heading level 1</h3>
```

This example create this structure:

```
Article heading level 1
  └── Article heading level 2
Article heading level 1
```

## Links

Use links to point to an external or internal resource.

The `href` attribute is to use to point to the resource. It can be an external URL (starting with `http://` or `https://`) or an internal anchor (starting with `#`).

The `hreflang` is **recommended** for external resources using another language than the current context.

```html
<p>The <a href="https://en.wikipedia.org/wiki/Yuzu">yuzu</a> is an asian fruit.</p>
<p>The <a hreflang="fr" href="https://fr.wikipedia.org/wiki/Yuzu">yuzu</a> is an asian fruit.</p>
<p>As stated in the <a href="#graph">graph below</a>.</p>
```

## Abbreviations

Use an abbreviation to shorten word or explain group of letters. Acronyms are to consider as abbreviations.

The `title` attribute is to use to explain the abbreviation.

```html
<p>He is <abbr title="Away From Keyboard">AFK</abbr></p>
```

Use `<abbr>` only for the first occurence of the abbreviation.

If the abbreviation is in another language than the current context, use the `lang` attribute:

```html
<p>Albert Einstein décrit en 1917 le principe du <abbr lang="en" title="Light Amplification by Stimulated Emission of Radiation">Laser</abbr>.</p>
```

By hovering the abbreviation, the browser usually display the `title` attribute in a tooltip:

<p>Albert Einstein décrit en 1917 le principe du <abbr lang="en" title="Light Amplification by Stimulated Emission of Radiation">Laser</abbr>.</p>

## Inline idioms

These specific idioms are to use carefully while writing.

### Bold

Words written in bold may have different meanings:

* `<b>` — The word(s) represent a keyword (might be repeated in the text)
* `<strong>` — The word(s) are more important (saying it will use another voice tone)

```html
<p>This review of the new Dyson <b>DustCleaner 2000</b> wasn't easy to write.</p>
<p>I am <strong>really</strong> angry.</p>
```

### Italic

Words written in italic may have different meanings:

* `<i>` — The word(s) represent are off the text
* `<em>` — The word(s) are more important (its meaning is different)

```html
<p>The <i>Queen Mary</i> sailed last night.</p>
<p>I <em>love</em> carrots.</p>
```

### Superscript and subscript

Superscript is to use, usually, for notes.  
Subscript is to use, usually, for specific notation suchs as mathematical or chemical formulas.

```html
<p>He spilled some H<sub>2</sub>O on his keyboard, we totally checked<sup>1</sup> this information.</p>
```

Will give the following:

<p>He spilled some H<sub>2</sub>O on his keyboard, we totally checked<sup>1</sup> this information.</p>

## Lists

Lists are either ordered (numbers) or unordered (bullet points). Ordered lists represent a specific order while unordered doesn't.

```html
<ul>
	<li>Milk</li>
	<li>Apple</li>
	<li>Salad</li>
</ul>

<ol>
	<li>Open the water</li>
	<li>Take a shower</li>
	<li>Dry up</li>
</ol>
```

Will give the following:

<ul>
	<li>Milk</li>
	<li>Apple</li>
	<li>Salad</li>
</ul>
<ol>
	<li>Open the water</li>
	<li>Take a shower</li>
	<li>Dry up</li>
</ol>

## Epigraphs

An epigraph is a block of text (or other) extracted from the text.

For example, using `<aside>` allow to extract a sentence from the text so it can be read while scanning the article.

```html
<aside>
	<p>He saw everything.</p>
</aside>
```

You can also style a piece of this sentence using a `span`:

```html
<aside>
	<p>He saw <span>everything</span>.</p>
</aside>
```

Other epigraphs have to use `<figure>`:

```html
<figure>
	<img src="…" alt="" />
	<figcaption>Image legend</figcaption>
</figure>
```

The `<figcaption>` is optionnal.

## Quotes

Use a quote to insert text from another person.

You can use either inline quotes `<q>` or block quotes `<blockquote>` for larger quotes.

```html
<p>He said <q>hurry up</q>.</p>

<blockquote>
	<p>From the Sun I learned this: when he goes down, overrich; he pours gold into the sea out of inexhaustible riches, so that even the poorest fisherman still rows with golden oars. For this I once saw and I did not tire of my tears as I watched it.</p>
	<footer>Friedrich Nietzsche</footer>
</blockquote>
```

When using a `<q>` the quotes will be automatically added.  
When using a `<blockquote>` a big opening quote will be added to the first paragraph and a closing quote at the end of the **last** paragraph.

## Medias

Medias are to be used as an epigraph (see above).

### Images

To insert an image either use `<img>` or `<picture>` (to provide a set of resolution-dependent images).

```html
<figure>
	<img src="…" alt="" />
	<figcaption>Image legend</figcaption>
</figure>

<figure>
	<picture>
		<source srcset="…" media="(min-width: 600px)">
 		<img src="…" alt="" />
	</picture>
	<figcaption>Image legend</figcaption>
</figure>
```

### Audio

```html
<figure>
	<audio controls>
		<source src="…" type="audio/ogg" />
		<source src="…" type="audio/mpeg" />
	</audio>
	<figcaption>Audio legend</figcaption>
</figure>
```

### Video

```html
<figure>
	<video controls>
		<source src="…" type="video/webm" />
		<source src="…" type="video/mp4" />
	</audio>
	<figcaption>Video legend</figcaption>
</figure>
```

## Tables

```html
<figure>
	<table summary="">
		<caption></caption>
		<thead>
			<tr>
				<th>Name</th>
				<th>Firstname</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Name</th>
				<th>Firstname</th>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td>Nietzsche</td>
				<td>Friedrich</td>
			</tr>
		</tbody>
	</table>
	<figcaption>Table legend</figcaption>
</figure>
```

## Glossary

```html
<dl>
	<dt><dfn>RSS</dfn></dt>
	<dd>…</dd>
</dl>
```

## Conversations

```html
<div class="interview">
	<p class="question">Why?</p>
	<p class="answer"></p>
</div>
```
```html
<div class="dialogue">
	<p>Hi!</p>
	<p>Oh… Hello!</p>
</div>
```
## Notes

```html
Word<sup><a href="#note-1">[1]</a></sup>
…
<ol class="notes">
	<li id="note-1">1. Not the Microsoft's application</li>
</ol>
```
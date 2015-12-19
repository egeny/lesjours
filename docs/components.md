# Components

This page list the available components and how to use them.

## Affix

This component might disappear.

## Buttons

`.btn`
`.btn-blank`
`.btn i`
`.btn-reverse`
`.btn-primary`
`.btn-transparent`

```html
<a class="btn" href="#"><i>Show</i></a>
<a class="btn-blank" href="#"></a>
<a class="btn btn-reverse" href="#"></a>
<a class="btn btn-primary" href="#"></a>
<a class="btn btn-transparent" href="#"></a>
<!-- Works the same with <button> -->
```

## Carousel

`.carousel`
`.carousel.hold`
`.carousel li`

## Inputs

`.field label`
`.field input`
`.field input:valid`
`input.radio`
`input.radio + .radio`
`input.checkbox + .checkbox`

## Player

`.player`
`.active .player | .player.playing`
`.player.animated`
`.player .container`
`.player button`
`.player button img`

```html
<div class="player">
	<div class="container">
		<audio aria-hidden="true">
			<source type="audio/webm" src="….webm" />
			<source type="audio/mp4"  src="….m4a" />
		</audio>
		<button class="btn-blank" type="button">
			<div class="container">
				<img class="responsive" src="…" alt="" />
			</div>
		</button>
	</div>
</div>
```

## Snake

`.snake`
`.snake-1`
`.snake-2`
`.snake-3`
`.snake-container`
`.snake-wrapper`
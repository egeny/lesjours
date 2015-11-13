'use strict';

// Inspired by Kraken — http://cferdinandi.github.io/kraken/

var
	del        = require('del'),
	lazypipe   = require('lazypipe'),
	path       = require('path'),
	gulp       = require('gulp'),
	concat     = require('gulp-concat'),
	header     = require('gulp-header'),
	livereload = require('gulp-livereload'),
	rename     = require('gulp-rename'),
	tap        = require('gulp-tap'),

	// HTML
	nunjucks = require('gulp-nunjucks-render'),

	// CSS
	autoprefixer = require('gulp-autoprefixer'),
	csslint      = require('gulp-csslint'),
	minify       = require('gulp-minify-css'),
	sass         = require('gulp-sass'),
	sasslint     = require('gulp-sass-lint'),

	// JS
	eslint = require('gulp-eslint'),
	uglify = require('gulp-uglify'),

	// Image
	image = require('gulp-image'),

	// SVG
	spriter = require('gulp-svg-sprite'),

	banner  = '/*! <%= package.name %> v<%= package.version %> */\n',
	context = { package: require('./package.json') },

	// The spriter configuration
	config = {
		mode: {
			symbol: {
				inline: true,
				dest:   '.',
				sprite: '' // Will be changed when needed (just here as a reminder)
			}
		}
	},

	paths = {
		src:  'src/**/*',
		dist: 'dist/',

		html: {
			input:  'src/',
			output: 'dist/'
		},
		css: {
			input:  'src/css/',
			output: 'dist/css/',
			img: {
				input:  'src/css/img/',
				output: 'dist/css/img/'
			},
			svg: {
				input:  'src/css/img/',
				output: 'dist/css/img/'
			}
		},
		img: {
			input:  'src/img/',
			output: 'dist/img/'
		},
		svg: {
			input:  'src/img/',
			output: 'dist/img/'
		},
		js: {
			input:  'src/js/',
			output: 'dist/js/'
		}
	};

nunjucks.nunjucks.configure([paths.html.input, paths.html.output], { watch: false });

gulp.task('default', ['clean', 'build']);

gulp.task('build', ['build:css', 'build:css:img', 'build:css:svg', 'build:js', 'build:img', 'build:svg', 'build:html']);
gulp.task('lint',  ['lint:sass', 'lint:css', 'lint:js']);

gulp.task('clean', function() {
	del.sync(paths.dist);
});

gulp.task('build:html', function() {
	return gulp.src(paths.html.input + '*.html')
		.pipe(nunjucks())
		.pipe(gulp.dest(paths.html.output))
		.pipe(livereload());
});

gulp.task('lint:sass', function() {
	return gulp.src(paths.css.input + '**/*.{scss,sass}')
		.pipe(sasslint())
		.pipe(sasslint.format());
});

gulp.task('lint:css', function() {
	return;
	return gulp.src(paths.css.input + '**/*.css')
		.pipe(csslint({
			'adjoining-classes': false
		}))
		.pipe(csslint.reporter());
});

gulp.task('build:css', ['lint:sass', 'lint:css'], function() {
	var worker = lazypipe()
		.pipe(sass, { outputStyle: "expanded" })
		.pipe(autoprefixer)
		.pipe(header, banner, context)
		.pipe(gulp.dest, paths.css.output)
		.pipe(rename, { suffix: '.min' })
		.pipe(minify)
		.pipe(gulp.dest, paths.css.output)
		.pipe(livereload);

	function filter() {
		return tap(function(file) {
			if (['.css', '.scss', '.sass'].indexOf(path.extname(file.path)) > -1) {
				return t.through(worker);
			}

			if (file.isDirectory()) {
				return gulp.src(file.path + '/*.css') // FIXME: File order might break things
					.pipe(concat(file.relative + '.css'))
					.pipe(worker());
			}
		});
	}

	return gulp.src(paths.css.input + '*').pipe(filter());
});

gulp.task('lint:js', function() {
	return gulp.src(paths.js.input + '**/*.js')
		.pipe(eslint()) // TODO: configure
		.pipe(eslint.format());
});

gulp.task('build:js', ['lint:js'], function() {
	var worker = lazypipe()
		.pipe(header, banner, context)
		.pipe(gulp.dest, paths.js.output)
		.pipe(rename, { suffix: '.min' })
		.pipe(uglify)
		.pipe(header, banner, context)
		.pipe(gulp.dest, paths.js.output)
		.pipe(livereload);

	function filter() {
		return tap(function(file, t) {
			if (path.extname(file.path) === '.js') {
				return t.through(worker);
			}

			if (file.isDirectory()) {
				return gulp.src(file.path + '/*.js')
					.pipe(concat(file.relative + '.js'))
					.pipe(worker());
			}
		});
	}

	return gulp.src(paths.js.input + '*').pipe(filter());
});

gulp.task('build:css:img', function() {
	return gulp.src(paths.css.img.input + "/**/*.{gif,jpg,png}")
		.pipe(image())
		.pipe(gulp.dest(paths.css.img.output))
		.pipe(livereload());
});

gulp.task('build:img', function() {
	return gulp.src(paths.img.input + "/**/*.{gif,jpg,png}")
		.pipe(image())
		.pipe(gulp.dest(paths.img.output))
		.pipe(livereload());
});

gulp.task('build:css:svg', function() {
	var worker = lazypipe()
		.pipe(gulp.dest, paths.css.svg.output)
		.pipe(livereload);

	function filter() {
		return tap(function(file, t) {
			if (path.extname(file.path) === '.svg') {
				return t.through(worker);
			}

			if (file.isDirectory()) {
				config.mode.symbol.sprite = file.relative + '.svg';
				return gulp.src(file.path + '/*.svg')
					.pipe(spriter(config))
					.pipe(worker());
			}
		});
	}

	return gulp.src(paths.css.svg.input + '*').pipe(filter());
});

gulp.task('build:svg', function() {
	var worker = lazypipe()
		.pipe(gulp.dest, paths.svg.output)
		.pipe(livereload);

	function filter() {
		return tap(function(file, t) {
			if (path.extname(file.path) === '.svg') {
				return t.through(worker);
			}

			if (file.isDirectory()) {
				config.mode.symbol.sprite = file.relative + '.svg';
				return gulp.src(file.path + '/*.svg')
					.pipe(spriter(config))
					.pipe(worker());
			}
		});
	}

	return gulp.src(paths.svg.input + '*').pipe(filter());
});

gulp.task('watch', ['build'], function() {
	livereload.listen();

	gulp.watch(paths.html.input    + '**/*.html',            ['build:html']);
	gulp.watch(paths.css.input     + '**/*.{css,scss,sass}', ['build:css']);
	gulp.watch(paths.css.img.input + '**/*.{gif,jpg,png}',   ['build:css:img']);
	gulp.watch(paths.css.svg.input + '**/*.svg',             ['build:css:svg']);
	gulp.watch(paths.img.input     + '**/*.{gif,jpg,png}',   ['build:img']);
	gulp.watch(paths.svg.input     + '**/*.svg',             ['build:svg']);
	gulp.watch(paths.js.input      + '**/*.js',              ['build:js']);
});
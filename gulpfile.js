'use strict';

// Inspired by Kraken — http://cferdinandi.github.io/kraken/

var
	del        = require('del'),
	lazypipe   = require('lazypipe'),
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
			input:  'src/img/*',
			output: 'dist/img/'
		},
		js: {
			input:  'src/js/',
			output: 'dist/js/'
		}
	};

gulp.task('default', [
	'clean',
	'build:html',
	'build:css',
	'build:css:img',
	'build:css:svg',
	'build:js',
	'build:img',
	'build:svg'
]);

gulp.task('build', ['build:html', 'build:css', 'build:js', 'build:img', 'build:svg']);
gulp.task('lint',  ['lint:sass',  'lint:css',  'lint:js']);

gulp.task('clean', function() {
	del.sync(paths.dist);
});

gulp.task('build:html', function() {
	nunjucks.nunjucks.configure([paths.html.input], { watch: false });
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
	return gulp.src(paths.css.input + '**/*.css')
		.pipe(csslint())
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
			if (file.isDirectory()) {
				var name = file.relative + '.css';
				return gulp.src(file.path + '/*.css') // FIXME: File order might break things
					.pipe(concat(name))
					.pipe(worker());
			}
		});
	}

	return gulp.src(paths.css.input + '*')
		.pipe(filter())
		.pipe(worker());
});

gulp.task('lint:js', function() {
	return gulp.src(paths.js.input + '**/*.js')
		.pipe(eslint())
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
		return tap(function(file) {
			if (file.isDirectory()) {
				var name = file.relative + '.js';
				return gulp.src(file.path + '/*.js')
					.pipe(concat(name))
					.pipe(worker());
			}
		});
	}

	return gulp.src(paths.js.input + '*')
		.pipe(filter())
		.pipe(worker());
});

gulp.task('build:css:img', function() {
	return gulp.src(paths.css.img.input + "*.{gif,jpg,png}")
		.pipe(image())
		.pipe(gulp.dest(paths.css.img.output))
		.pipe(livereload());
});

gulp.task('build:img', function() {
	return gulp.src(paths.img.input + "*.{gif,jpg,png}")
		.pipe(image())
		.pipe(gulp.dest(paths.img.output))
		.pipe(livereload());
});

gulp.task('build:css:svg', function() {
	var worker = lazypipe()
		.pipe(gulp.dest, paths.css.svg.output)
		.pipe(livereload);

	function filter() {
		return tap(function(file) {
			if (file.isDirectory()) {
				var name = file.relative + '.svg';
				return gulp.src(file.path + '/*.svg')
					//.pipe(spriter())
					.pipe(worker());
			}
		});
	}

	return gulp.src(paths.css.svg.input + '*')
		.pipe(filter())
		.pipe(worker());
});


gulp.task('build:svg', function() {
	var worker = lazypipe()
		.pipe(gulp.dest, paths.svg.output)
		.pipe(livereload);

	function filter() {
		return tap(function(file) {
			if (file.isDirectory()) {
				var name = file.relative + '.svg';
				return gulp.src(file.path + '/*.svg')
					//.pipe(spriter())
					.pipe(worker());
			}
		});
	}

	return gulp.src(paths.svg.input + '*')
		.pipe(filter())
		.pipe(worker());
});

gulp.task('watch', ['default'], function() {
	livereload.listen();
	gulp.watch(paths.html.input    + '**/*', ['build:html']);
	gulp.watch(paths.css.input     + '**/*', ['build:css']);
	gulp.watch(paths.css.img.input + '**/*', ['build:css:img']);
	gulp.watch(paths.css.svg.input + '**/*', ['build:css:svg']);
	gulp.watch(paths.img.input     + '**/*', ['build:img']);
	gulp.watch(paths.svg.input     + '**/*', ['build:svg']);
	gulp.watch(paths.js.input      + '**/*', ['build:js']);
});
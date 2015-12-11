'use strict';

// Inspired by Kraken — http://cferdinandi.github.io/kraken/

var
	del        = require('del'),
	fs         = require('fs'),
	merge      = require('merge-stream'),
	path       = require('path'),
	sequence   = require('run-sequence'),
	gulp       = require('gulp'),
	concat     = require('gulp-concat'),
	header     = require('gulp-header'),
	livereload = require('gulp-livereload'),
	rename     = require('gulp-rename'),
	replace    = require('gulp-replace'),

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
	svgmin  = require('gulp-svgmin'),

	banner  = '/*! <%= package.name %> v<%= package.version %> */\n',
	context = { package: require('./package.json') },

	// The middlewares configuration
	config = {
		spriter: {
			mode: {
				symbol: {
					inline: true,
					dest:   '.'
				}
			}
		},
		svgmin: {
			multipass: true,
			js2svg: {
				pretty: true,
				indent: '\t'
			},
			plugins: [
				{ removeTitle:        true },
				{ sortAttrs:          true },
				{ removeDimensions:   true },
				{ removeStyleElement: true }
			]
		}
	},

	paths = {
		dist: 'dist/',

		templates: 'src/templates',
		pages:     'src/pages',

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
	},

	// A set of tasks to launch on different contexts
	tasks = {
		'copy:img': function(context) {
			return function() {
				return gulp.src(context.input + '/**/*.{gif,jpg,png}')
					.pipe(gulp.dest(context.output))
					.pipe(livereload());
			}
		},

		'optimize:img': function(context) {
			return function() {
				return gulp.src(context.input + '**/*.{gif,jpg,png}')
					//.pipe(image()) // For now, disable image's optimization since sometimes it destroy images
					.pipe(gulp.dest(context.input));
			}
		},

		'copy:svg': function(context) {
			return function() {
				return gulp.src([context.input + '*.svg'])
					.pipe(gulp.dest(context.output))
					.pipe(livereload());
			}
		},

		'optimize:svg': function(context) {
			return function() {
				return gulp.src([context.input + '**/*.svg', '!src/img/snake.svg', '!src/img/ui.svg'])
					.pipe(svgmin(config.svgmin))
					.pipe(replace('fill-rule="evenodd"', ''))
					.pipe(gulp.dest(context.input));
			}
		},

		'sprite:svg': function(context) {
			return function() {
				var tasks = folders(context.input).map(function(folder) {
					return gulp.src(path.join(context.input, folder, '*.svg'))
						.pipe(spriter(config.spriter))
						.pipe(rename(folder + '.svg'))
						.pipe(replace('#FFF',    'currentColor'))
						.pipe(replace('#000',    'currentColor'))
						.pipe(replace('#C83E2C', 'currentColor'))
						.pipe(replace('#F3DF93', 'currentColor'))
						.pipe(gulp.dest(context.input)) // Save on input so we could use them as HTML templates (should not be versionned)
						.pipe(livereload());
				});

				// Return a stream merging other streams
				return !merge(tasks).isEmpty() ? merge(tasks) : null;
			}
		}
	};

// Utility function to find folders
function folders(dir) {
	return fs.readdirSync(dir)
		.filter(function(file) {
			return fs.statSync(path.join(dir, file)).isDirectory();
		});
}

// Configure nunjucks
nunjucks.nunjucks.configure(['src'], { watch: false, noCache: true });

gulp.task('default', function() {
	sequence('clean', 'build');
});

gulp.task('build', function(cb) {
	sequence(
		'build:img',
		'build:css:img',
		'build:svg',
		'build:css:svg',
		'build:css',
		'build:js',
		'build:html',
		cb);
});

gulp.task('lint', ['lint:sass', 'lint:css', 'lint:js']);

gulp.task('clean', function() {
	del.sync(paths.dist);
});

gulp.task('build:html', function() {
	var streams = merge();

	function build(folder, parent) {
		var data = {};
		parent = parent || "";

		try {
			data = JSON.parse(fs.readFileSync(path.join(paths.pages, parent, folder, folder + '.json')));
		} catch(e) {}

		try {
			data.content = fs.readFileSync(path.join(paths.pages, parent, folder, folder + '.html'));
		} catch(e) {}

		// Generate the HTML using the metadata and content
		if (data.template) {
			streams.add(
				gulp
					.src(path.join(paths.templates, data.template + '.html'))
					.pipe(nunjucks(data))
					.pipe(rename(path.join(parent, folder, 'index.html')))
					.pipe(gulp.dest(paths.dist))
					.pipe(livereload())
			);
		} else {
			streams.add(
				gulp
					.src(path.join(paths.pages, parent, folder, '*.html'))
					.pipe(nunjucks())
					.pipe(gulp.dest(path.join(paths.dist, parent, folder)))
					.pipe(livereload())
			);
		}

		// Copy the assets
		streams.add(
			gulp
				.src(path.join(paths.pages, parent, folder, '**/*.{gif,png,jpg,m4a,webm,mp4}'), { base: paths.pages })
				.pipe(gulp.dest(paths.dist))
		);

		parent = path.join(parent, folder);
		folders(path.join(paths.pages, parent)).forEach(function(folder) { build(folder, parent); });
	}

	folders(paths.pages).forEach(function(folder) { build(folder); });

	return !streams.isEmpty() ? streams : null;
});

gulp.task('lint:sass', function() {
	return gulp.src(paths.css.input + '**/*.{scss,sass}')
		.pipe(sasslint())
		.pipe(sasslint.format());
});

gulp.task('lint:css', function() {
	return; // Disabled for now since most of the errors are non-sense
	return gulp.src(paths.css.input + '**/*.css')
		.pipe(csslint()) // TODO: configure
		.pipe(csslint.reporter());
});

gulp.task('build:css', function() {
	return gulp.src(paths.css.input + 'global.scss')
		.pipe(sass({ outputStyle: 'expanded' }))
		.pipe(autoprefixer())
		.pipe(header(banner, context))
		.pipe(gulp.dest(paths.css.output))
		.pipe(rename({ suffix: '.min' }))
		.pipe(minify())
		.pipe(gulp.dest(paths.css.output))
		.pipe(livereload());
});

gulp.task('lint:js', function() {
	return gulp.src(paths.js.input + '**/*.js')
		.pipe(eslint()) // TODO: configure
		.pipe(eslint.format());
});

gulp.task('build:js', ['build:js:components'], function() {
	return gulp.src(paths.js.input + '*.js')
		.pipe(header(banner, context))
		.pipe(gulp.dest(paths.js.output))
		.pipe(rename({ suffix: '.min' }))
		.pipe(uglify())
		.pipe(header(banner, context))
		.pipe(gulp.dest(paths.js.output))
		.pipe(livereload());
});

gulp.task('build:js:components', function() {
	var tasks = folders(paths.js.input).map(function(folder) {
		return gulp.src(path.join(paths.js.input, folder, '*.js'))
			.pipe(concat(folder + '.js'))
			.pipe(header(banner, context))
			.pipe(gulp.dest(paths.js.output))
			.pipe(rename({ suffix: '.min' }))
			.pipe(uglify())
			.pipe(header(banner, context))
			.pipe(gulp.dest(paths.js.output))
			.pipe(livereload());
	});

	// Return a stream merging other streams
	return !merge(tasks).isEmpty() ? merge(tasks) : null;
});

gulp.task('build:img',     function(cb) { sequence('optimize:img',     'copy:img',     cb); });
gulp.task('build:css:img', function(cb) { sequence('optimize:css:img', 'copy:css:img', cb); });

gulp.task('build:svg',     function(cb) { sequence('optimize:svg',     'sprite:svg',     'copy:svg',     cb); });
gulp.task('build:css:svg', function(cb) { sequence('optimize:css:svg', 'sprite:css:svg', 'copy:css:svg', cb); });

// run-sequence needs actual gulp tasks, so we have to create them
gulp.task('optimize:img',     tasks['optimize:img'](paths.img));
gulp.task('optimize:css:img', tasks['optimize:img'](paths.css.img));
gulp.task('copy:img',         tasks['copy:img'](paths.img));
gulp.task('copy:css:img',     tasks['copy:img'](paths.css.img));

gulp.task('optimize:svg',     tasks['optimize:svg'](paths.svg));
gulp.task('optimize:css:svg', tasks['optimize:svg'](paths.css.svg));
gulp.task('sprite:svg',       tasks['sprite:svg'](paths.svg));
gulp.task('sprite:css:svg',   tasks['sprite:svg'](paths.css.svg));
gulp.task('copy:svg',         tasks['copy:svg'](paths.svg));
gulp.task('copy:css:svg',     tasks['copy:svg'](paths.css.svg));

gulp.task('watch', ['build'], function() {
	livereload.listen();

	gulp.watch(paths.pages         + '**/*.html',            ['build:html']);
	gulp.watch(paths.templates     + '**/*.html',            ['build:html']);
	gulp.watch(paths.css.input     + '**/*.{css,scss,sass}', ['build:css']);
	gulp.watch(paths.css.img.input + '**/*.{gif,jpg,png}',   ['build:css:img']);
	gulp.watch(paths.css.svg.input + '**/*.svg',             ['build:css:svg']);
	gulp.watch(paths.img.input     + '**/*.{gif,jpg,png}',   ['build:img']);
	gulp.watch(paths.svg.input     + '**/*.svg',             ['build:svg']);
	gulp.watch(paths.js.input      + '**/*.js',              ['build:js']);
});
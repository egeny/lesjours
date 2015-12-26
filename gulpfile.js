'use strict';

// Inspired by Kraken — http://cferdinandi.github.io/kraken/

var
	del        = require('del'),
	finder     = require('find-in-files'),
	fs         = require('fs'),
	glob       = require('glob'),
	merge      = require('merge-stream'),
	path       = require('path'),
	sequence   = require('run-sequence'),
	gulp       = require('gulp'),
	changed    = require('gulp-changed'),
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
	minify       = require('gulp-cssnano'),
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

	// A cache for incremental build
	cache = {},

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

		partials:  'src/partials',
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

	root = '',

	// A set of tasks to launch on different contexts
	tasks = {
		'copy:img': function(context) {
			return function() {
				return gulp.src(context.input + '/**/*.{ico,gif,jpg,png}')
					.pipe(gulp.dest(context.output))
					.pipe(livereload());
			}
		},

		'optimize:img': function(context) {
			return function() {
				return gulp.src(context.input + '**/*.{ico,gif,jpg,png}')
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
					if (folder === 'favicon') { return gulp.src('.'); }
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
nunjucks.nunjucks
	.configure(['src'], { watch: false, noCache: true })
	.addFilter('human', function(input) {
		var date = new Date(input), month;

		switch (date.getMonth()) {
			case 0:  month = 'janvier';   break;
			case 1:  month = 'février';   break;
			case 2:  month = 'mars';      break;
			case 3:  month = 'avril';     break;
			case 4:  month = 'mai';       break;
			case 5:  month = 'juin';      break;
			case 6:  month = 'juillet';   break;
			case 7:  month = 'août';      break;
			case 8:  month = 'septembre'; break;
			case 9:  month = 'octobre';   break;
			case 10: month = 'novembre';  break;
			case 11: month = 'décembre';  break;
		}

		return date.getDate() + ' ' + month + ' ' + date.getFullYear();
	});

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
		'build:assets',
		cb);
});

gulp.task('lint', ['lint:sass', 'lint:css', 'lint:js']);

gulp.task('clean', function() {
	del.sync(paths.dist);
});

gulp.task('build:assets', function(cb) {
	var streams = glob
		.sync(path.join(paths.pages, '**/*.{gif,png,jpg,m4a,webm,mp4,pdf}'))
		.map(function(file) {
			return assets(file);
		});

	return streams.length ? merge(streams) : null;
});

gulp.task('build:html', function() {
	var streams = glob
		.sync(path.join(paths.pages, '**/*.{html,json}'))
		.filter(function(file, index, files) {
			var parsed = path.parse(file);
			return (parsed.ext === '.html') ? (files.indexOf(path.join(parsed.dir, parsed.name + '.json')) < 0) : true;
		})
		.map(function(file) {
			return html(file);
		});

	return streams.length ? merge(streams) : null;
});

// This function might be called as a gulp's task callback (e.g. gulp.watch)
function assets(e) {
	return gulp
		.src(e.path || e, { base: paths.pages })
		.pipe(changed(paths.dist))
		.pipe(gulp.dest(paths.dist))
		.pipe(livereload());
}

// This function might be called as a gulp's task callback (e.g. gulp.watch)
function html(e) {
	var
		data = {}, metadata = {},
		parsed = path.parse(path.relative(paths.pages, e.path || e)),
		file, name;

	// Get the page's metadata
	try {
		metadata = JSON.parse(fs.readFileSync(path.join(paths.pages, parsed.dir, parsed.name + '.json')));

		// Wrap the data in a prefix if necessary (if the "template" key doesn't exist already)
		if (!metadata[metadata.template]) {
			data[metadata.template] = metadata;
		} else {
			data = metadata;
		}

		// Store the pages paths in a cache with its associated template (will be used when watching templates files)
		cache[metadata.template] = cache[metadata.template] || [];
		if (cache[metadata.template].indexOf(path.join(paths.pages, parsed.dir, parsed.base)) === -1) {
			cache[metadata.template].push(path.join(paths.pages, parsed.dir, parsed.base));
		}

		// Get the page's content
		data[metadata.template].content = fs.readFileSync(path.join(paths.pages, parsed.dir, parsed.name + '.html'));

		// Specific data injection
		if (metadata.template === 'episode') {
			data.obsession = JSON.parse(fs.readFileSync(path.join(paths.pages, parsed.dir, '..', parsed.dir.replace('obsessions' + path.sep, '').replace(path.sep + parsed.name, '') + '.json')));
		}
	} catch(e) {}

	file = metadata.template ? path.join(paths.templates, metadata.template + '.html') : path.join(paths.pages, parsed.dir, parsed.base);
	name = metadata.template ? path.join(parsed.dir, 'index.html')                     : path.relative(paths.pages, file);

	return gulp
		.src(file, { base: paths.pages })
		.pipe(nunjucks(data))
		.pipe(rename(name))
		.pipe(replace(/(src|href|action)="\/(\w)/g, '$1="' + root + '/$2'))
		.pipe(replace('href="/"',                 'href="' + root + '/"'))
		.pipe(gulp.dest(paths.dist))
		.pipe(livereload());
}

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

gulp.task('optimize:js', function() {
	function compare(stream, cb, sourceFile, targetPath) {
		var
			parsed = path.parse(targetPath),
			target = path.join(parsed.dir, parsed.name + '.min.js');

		changed.compareLastModifiedTime(stream, cb, sourceFile, target);
	}

	return gulp
		.src([path.join(paths.js.input, '**/*.js'), '!**/*.min.js'])
		.pipe(changed(paths.js.input, { hasChanged: compare }))
		.pipe(rename({ suffix: '.min' }))
		.pipe(uglify())
		.pipe(gulp.dest(paths.js.input));
});

gulp.task('build:js', ['optimize:js'], function() {
	var
		files = ['jquery', 'stickyfill', 'hammer', 'jquery.hammer', 'global', 'components/*'],
		streams = merge();

	streams.add(gulp
		.src(files.map(function(file) { return path.join(paths.js.input, file + '.js') }).concat('!**/*.min.js'))
		.pipe(concat('global.js'))
		.pipe(header(banner, context))
		.pipe(gulp.dest(paths.js.output))
		.pipe(livereload())
	);

	streams.add(gulp
		.src(files.map(function(file) { return path.join(paths.js.input, file + '.min.js') }))
		.pipe(concat('global.min.js'))
		.pipe(header(banner, context))
		.pipe(gulp.dest(paths.js.output))
		.pipe(livereload())
	);

	return streams;
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

	// The callback function used when a partial is changed
	function partials(e) {
		var found = [];

		// When the partials tree is complete, look for affected templates and build the pages using them
		function done() {
			finder.find(found.join('|'), paths.templates).then(function(results) {
				for (var template in results) {
					templates({ path: template });
				};
			});
		}

		// Find partials recursively (after found in which file a partial is used, search for this new partial)
		function find(partial) {
			finder.find(partial, paths.partials).then(function(results) {
				// Clean the results
				var partials = Object.keys(results).map(function(file) { return 'partials/' + path.relative(paths.partials, file); });

				// Concatenate the found partials
				found = found.concat(partials);

				// Call recursively if necessary
				partials.length ? find(partials.join('|')) : done();
			});
		}

		find('partials/' + path.relative(paths.partials, e.path));
	}

	// The callback function used when a template is changed
	function templates(e) {
		var parsed = path.parse(e.path);

		// Simply look the cache for the pages associated to the given template
		(cache[parsed.name] || []).forEach(function(page) {
			html({ path: page });
		});
	}

	gulp.watch(path.join(paths.partials,      '**/*.html'),                           partials);
	gulp.watch(path.join(paths.templates,     '**/*.html'),                           templates);
	gulp.watch(path.join(paths.pages,         '**/*.{html,json}'),                    html);
	gulp.watch(path.join(paths.pages,         '**/*.{gif,png,jpg,m4a,webm,mp4,pdf}'), assets);

	gulp.watch(path.join(paths.css.input,     '**/*.{css,scss,sass}'),      ['build:css']);
	gulp.watch(path.join(paths.css.img.input, '**/*.{gif,jpg,png}'),        ['build:css:img']);
	gulp.watch(path.join(paths.css.svg.input, '**/*.svg'),                  ['build:css:svg']);
	gulp.watch(path.join(paths.img.input,     '**/*.{gif,jpg,png}'),        ['build:img']);
	gulp.watch(path.join(paths.svg.input,     '**/*.svg'),                  ['build:svg']);
	gulp.watch([path.join(paths.js.input,      '**/*.js'), '!**/*.min.js'], ['build:js']);
});
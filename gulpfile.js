'use strict';

// Inspired by Kraken — http://cferdinandi.github.io/kraken/

var
	argv       = require('yargs').argv,
	dcopy      = require('deep-copy'),
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
	cheerio  = require('cheerio'),
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
				symbol: { dest: '.' }
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

	// The destination folder, default to "dist" but can be overriden with the "dist" parameter
	dist = argv.dist || 'dist',

	// Nunjucks environment
	env,

	paths = {
		dist: dist,

		partials:  'src/partials',
		templates: 'src/templates',
		pages:     'src/pages',

		css: {
			input: 'src/css',
			output: path.join(dist, 'css'),
			img: {
				input: 'src/css/img',
				output: path.join(dist, 'css/img')
			},
			svg: {
				input: 'src/css/img',
				output: path.join(dist, 'css/img')
			}
		},
		img: {
			input: 'src/img',
			output: path.join(dist, 'img')
		},
		svg: {
			input: 'src/img',
			output: path.join(dist, 'img')
		},
		js: {
			input: 'src/js',
			output: path.join(dist, 'js')
		}
	},

	root = argv.root || '',

	// A set of tasks to launch on different contexts
	tasks = {
		'copy:img': function(context) {
			return function() {
				return gulp.src(path.join(context.input, '**/*.{ico,gif,jpg,png}'))
					.pipe(gulp.dest(context.output))
					.pipe(livereload());
			}
		},

		'optimize:img': function(context) {
			return function() {
				return gulp.src(path.join(context.input, '**/*.{ico,gif,jpg,png}'))
					//.pipe(image()) // For now, disable image's optimization since sometimes it destroy images
					.pipe(gulp.dest(context.input));
			}
		},

		'copy:svg': function(context) {
			return function() {
				return gulp.src(path.join(context.input, '**/*.svg'))
					.pipe(gulp.dest(context.output))
					.pipe(livereload());
			}
		},

		'optimize:svg': function(context) {
			return function() {
				return gulp.src([path.join(context.input, '**/*.svg'), '!src/img/ui.svg'])
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
env = nunjucks.nunjucks.configure('src', {
	autoescape: false,
	noCache:    true,
	watch:      false
});

env.addFilter('expand', expand);

env.addFilter('human', function(input) {
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

env.addFilter('in', function(input, array) {
	return (array || []).indexOf(input) > -1;
});

env.addFilter('published', function(input, date) {
	if (!input) { return input; }

	var reference = date ? new Date(date).getTime() : new Date().getTime();

	if (input.push) {
		return input.filter(function(resource) {
			return new Date(resource.date).getTime() <= reference;
		});
	} else {
		return new Date(input.date).getTime() <= reference;
	}
});

env.addFilter('removeBR', function(input) {
	return (input || '').replace(/<br\/?>/g, ' ');
});

env.addFilter('slice', function(array, begin, end) {
	return array.slice(begin, end);
});

env.addFilter('split', function(input, separator) {
	return (input || "").split(separator);
});

env.addFilter('startsWith', function(input, pattern) {
	return new RegExp('^' + pattern).test(input || '');
});

env.addFilter('test', function(pattern, input) {
	return pattern.test(input || '');
});

env.addGlobal('countries', JSON.parse(fs.readFileSync(path.join(paths.partials, 'countries.json'))));

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
		'build:visibility',
		'build:assets',
		cb);
});

gulp.task('lint', ['lint:sass', 'lint:css', 'lint:js']);

gulp.task('clean', function() {
	del.sync(paths.dist);
});

gulp.task('build:assets', function(cb) {
	var streams = [];

	streams = streams.concat(
		glob
			.sync(path.join(paths.pages, '**/*.{gif,png,jpg,m4a,webm,mp4,pdf}'))
			.map(function(file) { return assets(file); })
	);

	streams = streams.concat(
		glob
			.sync(path.join(paths.pages, '.htaccess'))
			.map(function(file) { return assets(file); })
	);

	streams = streams.concat(
		gulp
			.src(path.join(paths.css.input, 'fonts/**/*'), { base: paths.css.input })
			.pipe(changed(paths.css.output))
			.pipe(gulp.dest(paths.css.output))
			.pipe(livereload())
	);

	return streams.length ? merge(streams) : null;
});

// Build a visibility.json file containing the scope for each request URI
gulp.task('build:visibility', function() {
	var
		slug, visibility,
		result = {};

	for (slug in cache.episode) {
		visibility = cache.episode[slug].visibility;
		if (!visibility) { continue; }

		result[visibility] = result[visibility] || [];
		result[visibility].push(cache.episode[slug].obsession.replace('obsession:', root + '/obsessions/') + '/' + slug + '/');
	}

	fs.writeFileSync(path.join(paths.dist, 'visibility.json'), JSON.stringify(result, null, "\t"));
});

gulp.task('build:html', function() {
	var streams = glob
		.sync(path.join(paths.pages, '**/*.{html,php,json}'))
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

// Simple utility function to copy an object
function copy(object) {
	var property, result = {};

	for (property in object) {
		if (object.hasOwnProperty(property)) {
			result[property] = object[property];
		}
	}

	return result;
}

// Make a deep copy of data and fetch sub-resources
function expand(data) {
	var
		r = /^(?!http)(\w+):(.+)/, // A regexp to check if we need to go deeper
		result = {},
		property, matches;

	if (!data) { return data; }

	if (typeof data === 'string' || data.push) {
		return expand({ value: data }).value;
	}

	for (property in data) {
		if (typeof data[property] === 'string') {
			matches          = data[property].match(r);
			result[property] = matches ? fetch(matches[1], matches[2]) : data[property];
		} else if (data[property].push) {
			result[property] = data[property].map(function(value) {
				return expand({ value: value }).value; // Small trick to call recursively on every items
			});
		} else if (typeof data[property] === 'object' && data[property] !== null) {
			result[property] = expand(data[property]);
		} else {
			result[property] = data[property];
		}
	}

	return result;
}

// Fetch some metadata
function fetch(kind, fragment) {
	var
		fragments = fragment.split('/'),
		obsession, episode,
		result;

	// Makes sure we have a cache for this kind of resource
	cache[kind] = cache[kind] || {};

	switch (kind) {
		case 'episode':
			if (fragments.length === 2) {
				obsession = fragments[0];
				episode   = fragments[1];

				try {
					cache[kind][episode] = cache[kind][episode] || JSON.parse(fs.readFileSync(path.join(paths.pages, 'obsessions', obsession, episode, episode + '.json')));
				} catch(e) {
					cache[kind][episode] = {};
				}

				// Automatically add a reference to the obsession resource (but do not expand, it will be while necessary in template)
				cache[kind][episode].obsession = 'obsession:' + obsession;

				return cache[kind][episode];
			} else {
				console.log("The fragment " + kind + ":" + fragment + " is invalid");
				return {};
			}

		case 'obsession':
			try {
				cache[kind][fragment] = cache[kind][fragment] = JSON.parse(fs.readFileSync(path.join(paths.pages, kind + 's', fragment, fragment + '.json')));
			} catch(e) {
				cache[kind][fragment] = {};
			}

			return cache[kind][fragment];

		default:
			try {
				cache[kind][fragment] = cache[kind][fragment] || JSON.parse(fs.readFileSync(path.join(paths.pages, kind + 's', fragment + '.json')));
			} catch(e) {
				cache[kind][fragment] = {};
			}

			return cache[kind][fragment];
	}
}

// This function might be called as a gulp's task callback (e.g. gulp.watch)
function html(e) {
	var
		file   = path.relative(paths.pages, e.path || e), // The current file being processed
		parsed = path.parse(file),
		source,  // The source file to use in the final stream (either the file or a template)
		content, // The extracted content (.html file)
		metadata = {}, // The extracted metadata (.json file)
		data     = {}; // The final data to use as context with nunjucks

	// Try to retrieve some associated metadata
	try {
		// Try to ready the metadata without checking the caching since we might be watching some files and need to refresh the data
		metadata = JSON.parse(fs.readFileSync(path.join(paths.pages, parsed.dir, parsed.name + '.json')));

		// Store some informations in a cache ; will surely be re-used
		if (metadata.template) {
			// Inject some data
			if (metadata.template === 'episode') {
				metadata.obsession = 'obsession:' + parsed.dir.split(path.sep)[1];
			}

			// Keep the metadata
			cache[metadata.template] = cache[metadata.template] || {};
			cache[metadata.template][parsed.name] = metadata;

			// Save a cache for files and the template they use (will be used when watching templates files)
			cache.template = cache.template || {};
			cache.template[metadata.template] = cache.template[metadata.template] || [];

			// Push only if the file doesn't exists
			if (cache.template[metadata.template].indexOf(file) === -1) {
				cache.template[metadata.template].push(file);
			}
		}

		// Wrap the data in a prefix if necessary (if the "template" key doesn't exists already)
		if (!metadata[metadata.template]) {
			data[metadata.template] = copy(metadata); // Make a deep copy of the metadata
			delete data[metadata.template].template; // Cleaning just for fun
		} else {
			data = copy(metadata); // Make a deep copy of the metadata
		}

		// Try to retrieve the content
		content = fs.readFileSync(path.join(paths.pages, parsed.dir, parsed.name + '.html'));
		data[metadata.template].content = nunjucks.nunjucks.renderString(content.toString(), data);
		// If metadata.template doesn't exists it will raise an Error (so, no need to check first)
	} catch(e) {
		if (e.code !== 'ENOENT') { console.log(e); }
	}

	// Set the final source final to use as a starting point in the stream
	if (metadata.template) {
		// If there is a "template" property in the metadata use it
		source = path.join(paths.templates, metadata.template + '.html');
	} else if (content) {
		// Otherwise, use the content file (if a content was found)
		source = path.join(paths.pages, parsed.dir, parsed.name + '.html');
	} else {
		// Otherwise, fallback to the current file (shouldn't do anything)
		source = path.join(paths.pages, file);
	}

	var streams = merge();

	streams.add(
		gulp
			.src(source, { base: paths.pages })
			.pipe(nunjucks(data))
			.pipe(rename(path.join(parsed.dir, metadata.template ? 'index.html' : parsed.base)))
			.pipe(replace(/(src|href|action)="\/(\w)/g, '$1="' + root + '/$2'))
			.pipe(replace(/url\(\/(\w)/g,               'url(' + root + '/$1'))
			.pipe(replace('href="/"',                 'href="' + root + '/"'))
			.pipe(replace('\'/img',                       '\'' + root + '/img'))
			.pipe(replace('Location: /',          'Location: ' + root + '/'))
			.pipe(gulp.dest(paths.dist))
			.pipe(livereload())
	);

	// Generate another index.protected.html file with its content sliced for protected episodes
	// Sorry, this part is a bit messy, I do not have time to clean it up
	if (metadata.template === 'episode' && metadata.visibility === 'protected') {
		var _data = dcopy(data); // Make a deep copy of the data and content (otherwise the modification below will be used by the ongoing streams)
		_data.protected = true; // Add a variable in the data so we can identify in the template we are generating the additional page

		// Parse the content (heavy)
		var $ = cheerio.load('<div id="cheerio-wrapper">' + _data.episode.content + "</div>", { decodeEntities: false });
		var stopped = false;

		// Retrieve the children we want (first <p> and its direct siblings, stop we reaching to another <p>)
		$ = $("#cheerio-wrapper").children().filter(function(index, element) {
			if (stopped) { return false; }

			if (index && element.type === "tag" && element.name === "p") {
				stopped = true;
				return false;
			}

			return true;
		});

		_data.episode.content = $.toString();

		// I know, this part should be done with a lazypipe…
		streams.add(
			gulp
				.src(source, { base: paths.pages })
				.pipe(nunjucks(_data))
				.pipe(rename(path.join(parsed.dir, 'index.protected.html')))
				.pipe(replace(/(src|href|action)="\/(\w)/g, '$1="' + root + '/$2'))
				.pipe(replace(/url\(\/(\w)/g,               'url(' + root + '/$1'))
				.pipe(replace('href="/"',                 'href="' + root + '/"'))
				.pipe(replace('\'/img',                       '\'' + root + '/img'))
				.pipe(replace('Location: /',          'Location: ' + root + '/'))
				.pipe(gulp.dest(paths.dist))
				.pipe(livereload())
		);
	}

	return streams;
}

gulp.task('lint:sass', function() {
	return gulp.src([path.join(paths.css.input, '**/*.{scss,sass}'), '!src/css/framework/grid.scss'])
		.pipe(sasslint())
		.pipe(sasslint.format());
});

gulp.task('lint:css', function() {
	return; // Disabled for now since most of the errors are non-sense
	return gulp.src(path.join(paths.css.input, '**/*.css'))
		.pipe(csslint()) // TODO: configure
		.pipe(csslint.reporter());
});

gulp.task('build:css', function() {
	return gulp.src(path.join(paths.css.input, 'global.scss'))
		.pipe(sass({ outputStyle: 'expanded' }))
		.pipe(autoprefixer())
		.pipe(header(banner, context))
		.pipe(gulp.dest(paths.css.output))
		.pipe(rename({ suffix: '.min' }))
		.pipe(minify({ mergeIdents: false }))
		.pipe(gulp.dest(paths.css.output))
		.pipe(livereload());
});

gulp.task('lint:js', function() {
	var files = ['global', 'components/*'];

	return gulp.src(files.map(function(file) { return path.join(paths.js.input, file + '.js') }).concat('!**/*.min.js'))
		.pipe(eslint())
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
		files = ['libs/modernizr', 'libs/svg4everybody', 'libs/svg4everybody.init', 'libs/jquery', 'libs/stickyfill', 'libs/hammer', 'libs/jquery.hammer', 'global', 'components/*', 'libs/analytics'],
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

	streams.add(gulp
		.src(path.join(paths.js.input, 'pages', '*.js'))
		.pipe(header(banner, context))
		.pipe(gulp.dest(path.join(paths.js.output, 'pages')))
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

			finder.find(found.join('|'), paths.pages).then(function(results) {
				for (var page in results) {
					html({ path: page });
				};
			});
		}

		// Find partials recursively (after found in which file a partial is used, search for this new partial)
		function find(partial) {
			finder.find(partial, paths.partials).then(function(results) {
				// Clean the results
				var partials = Object.keys(results).map(function(file) { return path.join('partials', path.relative(paths.partials, file)); });

				// Concatenate the found partials
				found = found.concat(partials);

				// Call recursively if necessary
				partials.length ? find(partials.join('|')) : done();
			});
		}

		found.push(path.join('partials', path.relative(paths.partials, e.path)));
		find(found[0]);
	}

	// The callback function used when a template is changed
	function templates(e) {
		var parsed = path.parse(e.path);

		// Simply look the cache for the pages associated to the given template
		(cache.template[parsed.name] || []).forEach(function(page) {
			html({ path: path.join(paths.pages, page) });
		});
	}

	gulp.watch(path.join(paths.partials,  '**/*.html'),            partials);
	gulp.watch(path.join(paths.templates, '**/*.html'),            templates);
	gulp.watch(path.join(paths.pages,     '**/*.{html,php,json}'), html);

	gulp.watch([
		path.join(paths.pages, '**/*.{gif,png,jpg,m4a,webm,mp4,pdf}'),
		path.join(paths.pages, '.htaccess')
	], assets);

	gulp.watch(path.join(paths.css.input,     '**/*.{css,scss,sass}'),     ['build:css']);
	gulp.watch(path.join(paths.css.img.input, '**/*.{gif,jpg,png}'),       ['build:css:img']);
	gulp.watch(path.join(paths.css.svg.input, '**/*.svg'),                 ['build:css:svg']);
	gulp.watch(path.join(paths.img.input,     '**/*.{gif,jpg,png}'),       ['build:img']);
	gulp.watch(path.join(paths.svg.input,     '**/*.svg'),                 ['build:svg']);
	gulp.watch([path.join(paths.js.input,     '**/*.js'), '!**/*.min.js'], ['build:js']);
});
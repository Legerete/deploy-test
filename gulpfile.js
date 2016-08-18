/**
 * BASE USAGE:
 *
 * gulp [task] --no-production: no minification or js debug calls stripping
 * gulp [task] --no-watch: only disable watching of changes
 *
 */

// load package.json file - for resource serialize
var fs = require('fs'),
	packageJson = JSON.parse(fs.readFileSync('./package.json')),

	sourcePath = 'websrc',
	pathSass = sourcePath + '/stylesheets',
	pathJs = sourcePath + '/scripts',
	pathCoffee = pathJs + '/coffee',
	pathJsLibs = pathJs + '/libs', // for static vendor sources that are not downloaded by bower
	pathImages = sourcePath + '/images',
	pathSpriteSources = pathImages + '/sprite',
	pathFonts = sourcePath + '/fonts',

	targetWwwPath = 'www',
	targetAssetsPath = 'assets',
	targetPath = targetWwwPath + '/' + targetAssetsPath,
	targetPathJs = targetPath + '/js',
	targetPathCss = targetPath + '/css',
	targetPathImages = targetPath + '/images',
	targetPathFonts = targetPath + '/fonts',

    sassMainSource = pathSass + '/main.scss',
	targetSpriteImageFullPath = '/' + targetAssetsPath + '/images/sprite.png',

	gutil = require('gulp-util'),
	gulp = require('gulp'),
	argv = require('yargs').argv,

	// dependencies are lazy loaded only if they are required (because of performance)
	concat = require('gulp-concat'),
	gulpif = require('gulp-if'),
	sass = null,
	sassLint = null,
	rename = null,
	imagemin = null,
	pngquant = null,
	sprite = null,
	gulpBowerFiles = null,
	gulpFilter = null,
	buffer = null,
	uglify = null,
	stripDebug = null,
	cssmin = null,
	autoPrefixer = null,
	wiredep = null,
	changed = null,
	flatten = null,
	coffee = null,
	coffeeLint = null,
	del = null,
	browserSync = null,
	modifyCssUrls = null,
	esLint = null,

	lintedWithError = false;

/**
 * Fonts
 * Get fonts from websrc directory and from bower main file
 */
gulp.task('fonts', function () {
	gulpBowerFiles = gulpBowerFiles || require('gulp-main-bower-files');
	gulpFilter = gulpFilter || require('gulp-filter');
	flatten = flatten || require('gulp-flatten');

	var filterFonts = gulpFilter(['**/*.eot', '**/*.svg', '**/*.ttf', '**/*.woff', '**/*.woff2'], {restore: true});
	gulp.src(pathFonts + '*.*')
		.pipe(gulp.dest(targetPathFonts));

	return gulp.src('./bower.json')
		.pipe(gulpBowerFiles()) //can be overwritten - see documentation
		.pipe(filterFonts)
		.pipe(flatten())
		.pipe(gulp.dest(targetPathFonts));
});

/**
 * Javascript
 * Concatenate vendor dependencies from bower
 * - concatenate to libs/all.vendor.js
 */
gulp.task('bowerJsFiles', function () {
	gulpBowerFiles = gulpBowerFiles || require('gulp-main-bower-files');
	gulpFilter = gulpFilter || require('gulp-filter');
	var filterJs = gulpFilter(['**/*.js', '!**/netteforms.js'], {restore: true}); // remove, if netteforms is required

	return gulp.src('./bower.json')
		.pipe(gulpBowerFiles()) // can be overwritten - see documentation
		.pipe(filterJs)
		.pipe(concat('all.vendor.js'))
		.pipe(gulp.dest(pathJs));
});

/**
 * Javascript
 * Linting of JavaScript source files.
 * Set lintedWithError variable, which is used in js task
 */
gulp.task('jsLint', function () {
	lintedWithError = false;
	esLint = esLint || require('gulp-eslint');

	return gulp
		.src([
			pathJs + '/**/*.js',
			'!' + pathJs + '/**/*.coffee',
			'!' + pathJs + '/all.vendor.js',
			'!' + pathJs + '/all.coffee.js'
		])
		.pipe(esLint({
			'extends': 'standard', // installed by 'eslint-config-standard'
			'rules': {
				'quotes': [2, 'single'], // single quotes
				'semi': [2, 'always'], // force semicolons
				'indent': [2, 'tab'], // tab for indent
				'no-extra-semi': 2,
				'valid-jsdoc': [1, {
					'prefer': {
						'return': 'returns'
					}
				}],
				'array-callback-return': 1
			},
			'env': {
				'browser': true,
				'node': false,
				'jquery': true,
				'es6': false
			}
			/*
			 There is trouble with indent fixing when tabs and spaces mixed, uncomment only if it is
			 ensured, that no such mixing will not happen.

			 ,'fix': true //fix all fixable issues
			 */
		}))
		.pipe(esLint.format())
		// Brick on failure to be super strict
		.pipe(esLint.failAfterError())
		.on('error', handleError)
		.pipe(gulp.dest(pathJs));

});
function handleError() {
	lintedWithError = true;
}

/**
 * Javascript
 * Concatenation and minification
 * - concatenate all.vendor.js add main.js files to target file
 * - run only if not linted with error (is dependent on js-lint)
 * - if in production mode, remove debug calls and minify target file (call with --production option)
 **/
gulp.task('js', ['coffeeLint', 'coffee', 'bowerJsFiles', 'jsLint'], function () {
	if (lintedWithError) {
		return;
	}

	uglify = uglify || require('gulp-uglify');
	stripDebug = stripDebug || require('gulp-strip-debug');

	return gulp.src([
			pathJs + '/all.vendor.js',
			pathJs + '/**/*.js',
			pathJs + '/all.coffee.js'
		])
		.pipe(gulpif(argv.production, stripDebug()))
		.pipe(gulpif(!argv.debug, uglify()))
		.pipe(concat('all.min.js'))
		.pipe(gulp.dest(targetPathJs));
});

/**
 * Sprite
 * Generating sprite and scss for sprite usage
 * - Pick all images from /images/sprite-sources directory and create sprite.png. Sprite then gets minified.
 * - Generate scss file, containing variables and mixins for usage simplification
 *
 * Example
 * If there is my-file.png picture in sprite-sources directory,
 * it can be used in main.scss like so:
 * #myelement {
 *	 .sprite($my-file);
 * }
 */
gulp.task('sprite', function () {
	imagemin = imagemin || require('gulp-imagemin');
	pngquant = pngquant || require('imagemin-pngquant');
	sprite = sprite || require('gulp.spritesmith');
	buffer = buffer || require('vinyl-buffer');

	// source path of the sprite images
	var spriteData = gulp.src(pathSpriteSources + '/**/*')
			.pipe(sprite({
				imgName: 'sprite.png',
				cssName: 'sprite.scss',
				imgPath: targetSpriteImageFullPath,
				padding: 10
			}));

	// output path for the CSS or SASS
	spriteData
		.css
		.pipe(gulp.dest(pathSass));

	spriteData
		.img
		.pipe(buffer())
		.pipe(imagemin({
			progressive: true,
			svgoPlugins: [{removeViewBox: false}],
			use: [pngquant({quality: '75-90', speed: 1})] //causes lossy compression
		}))
		.pipe(gulp.dest(targetPathImages)); // output path for the sprite

});

/**
 * SASS
 * Linter - walk all SCSS files in stylesheets directory and lint them (only libs path ignored)
 */
gulp.task('sassLint', function () {
	sassLint = sassLint || require('gulp-sass-lint');
	var sassSources = [pathSass + '/**/*.scss',
        '!' + pathSass + '/libs/*.*',
        '!' + pathSass + '/sprite.scss'];

	return gulp.src(sassSources)
		.pipe(sassLint({'config': '.sass-lint.yml'}))
		.pipe(sassLint.format())
		.pipe(sassLint.failOnError());
});

/**
 * SASS import
 * Automatically inject Less and Sass Bower dependencies. See
 * Bower dependencies wired into styles
 * https://github.com/taptapship/wiredep
 */
gulp.task('wiredep', function () {
	wiredep = wiredep || require('wiredep').stream;
	changed = changed || require('gulp-changed');

	return gulp.src(sassMainSource)
		.pipe(wiredep())
		.pipe(changed(pathSass, {
			hasChanged: changed.compareSha1Digest
		}))
		.pipe(gulp.dest(pathSass));
});

/**
 * SASS and CSS
 * Concatenation, compilation and minification
 * - pick only main.scss, that includes all necessary dependencies
 * - if in production mode, minify target file
 */
gulp.task('sass', ['sprite', 'sassLint'], function () {
	sass = sass || require('gulp-sass');
	rename = rename || require('gulp-rename');
	cssmin = cssmin || require('gulp-cssmin');
	autoPrefixer = autoPrefixer || require('gulp-autoprefixer');
	modifyCssUrls = modifyCssUrls || require('gulp-modify-css-urls');

	return gulp.src(sassMainSource)
		.pipe(sass())
		.pipe(concat('tmp.css'))
		.pipe(autoPrefixer({
			browsers: [
				'last 2 versions',
				'android 4',
				'opera 12',
				'ie >= 9'
			],
			cascade: false
		}))
		.pipe(modifyCssUrls({
			append: '?v=' + packageJson.version
		}))
		.pipe(gulpif(!argv.debug, cssmin()))
		.pipe(rename('all.min.css'))
		.pipe(gulp.dest(targetPathCss));
});

/**
 * Images
 * Shrink image file sizes and copy minified files into target directory.
 * It can use both lossless either lossy comporession (lossy compression by pngquant)
 */
gulp.task('imagemin', function () {
	imagemin = imagemin || require('gulp-imagemin');
	pngquant = pngquant || require('imagemin-pngquant');

	return gulp.src(pathImages + '/*.*')
		.pipe(imagemin({
			progressive: true,
			svgoPlugins: [{removeViewBox: false}],
			//use: [pngquant({quality: '75-90', speed: 1})] //causes lossy compression
		}))
		.pipe(gulp.dest(targetPathImages));
});

/**
 * Compile CoffeeScript files
 */
gulp.task('coffee', function () {
	coffee = coffee || require('gulp-coffee');

	return gulp.src(pathCoffee + '/**/*.coffee')
		.pipe(coffee({bare: true}).on('error', gutil.log))
		.pipe(concat('all.coffee.js'))
		.pipe(gulp.dest(pathJs));
});

/**
 * Lint CoffeeScript files using Gulp and CoffeeLint
 */
gulp.task('coffeeLint', function () {
	coffeeLint = coffeeLint || require('gulp-coffeelint');

	return gulp.src(pathCoffee + '/**/*.coffee')
		.pipe(coffeeLint())
		.pipe(coffeeLint.reporter());
});

/**
 * BrowserSync
 */
gulp.task('browserSync', function () {
	browserSync = browserSync || require('browser-sync').create();
	browserSync.init(require('./bs-config'));

	gulp.watch('app/**/*.latte').on('change', browserSync.reload);
	gulp.watch(targetPathJs + '/**/*.js').on('change', browserSync.reload);
	gulp.watch(targetPathCss + '/**/*.css').on('change', browserSync.reload);
	gulp.watch(targetPathImages + '/**/*').on('change', browserSync.reload);
	gulp.watch(targetPathFonts + '/**/*').on('change', browserSync.reload);
});

/**
 * Watch changes of source files
 */
gulp.task('watch', function () {
	if (!argv.production && argv.watch !== false) {
        gulp.watch('./bower.json', ['wiredep']);
		gulp.watch(pathSpriteSources + '/**/*', ['sprite']);
		gulp.watch([pathSass + '/**/*.scss', './bower.json'], ['sass']);
		gulp.watch([pathCoffee + '/**/*.coffee'], ['coffeeLint', 'coffee']);
		// for watching imagemin it is necessary to have assets and target directory, or it will end up with infinite loop.
		gulp.watch(pathImages + '/**/*', ['imagemin']);
	}
});

/**
 * Clean all compiled assets
 */
gulp.task('clean', function() {
	del = del || require('del');

	return del([targetPath]);
});

/**
 * Default task
 * Options:
 *  --production: do minification and strip js debug calls. Also disable changes watching
 *  --no-watch: only disable changes watching
 */
gulp.task('default', ['sass', 'js', 'imagemin', 'fonts', 'browserSync', 'watch']);

/**
 * Build task
 *
 * This task compile SASS, CoffeeScripts and linting them.
 */
gulp.task('build', ['sass', 'js', 'imagemin', 'fonts']);


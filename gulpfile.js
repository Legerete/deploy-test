/**
 * BASE USAGE:
 *
 * gulp [task] --no-production: no minification or js debug calls stripping
 * gulp [task] --no-watch: only disable watching of changes
 *
 */

var sourcePath = 'websrc';
var pathSASS = sourcePath+'/stylesheets';
var pathJS = sourcePath+'/scripts';
var pathJSLibs = pathJS+'/libs'; //for static vendor sources that are not downloaded by bower
var pathImages = sourcePath+'/images';
var pathSpriteSources = pathImages+'/sprite';
var pathFonts = sourcePath+'/fonts';

var targetPath = 'www/assets';
var targetPathJS = targetPath+'/js';
var targetPathCSS = targetPath+'/css';
var targetPathImages = targetPath+'/images';
var targetPathFonts = targetPath+'/fonts';


var gulp = require('gulp');
var argv = require('yargs').argv;


//dependencies are lazy loaded only if they are required (because of performance)
var concat = require('gulp-concat');
var gulpif = require('gulp-if');
var eslint = null;
var sass = null;
var sasslint = null;
var rename = null;
var imagemin = null;
var pngquant = null;
var spritesmith = null;
var gulpBowerFiles = null;
var gulpFilter = null;
var buffer = null;
var uglify = null;
var stripDebug = null;
var cssmin = null;
var autoprefixer = null;
var wiredep = null;
var changed = null;
var flatten = null;

/**
 * Fonts
 * Get fonts from websrc directory and from bower main file
 */
gulp.task('fonts', function() {
	gulpBowerFiles = gulpBowerFiles || require('gulp-main-bower-files');
	gulpFilter = gulpFilter || require('gulp-filter');
	flatten = flatten || require('gulp-flatten');

	var filterFonts = gulpFilter(['**/*.eot', '**/*.svg', '**/*.ttf', '**/*.woff', '**/*.woff2'], {restore: true});
	gulp.src(pathFonts+'*.*')
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
 * - concatenate to libs/vendor.js
 */
gulp.task('bower-js-files', function () {
	console.info('Concatenate vendor javascript files');
	gulpBowerFiles = gulpBowerFiles || require('gulp-main-bower-files');
	gulpFilter = gulpFilter || require('gulp-filter');

	var filterJS = gulpFilter(['**/*.js', '!**/netteforms.js'], {restore: true}); //remove, if netteforms is required
	return gulp.src('./bower.json')
		.pipe(gulpBowerFiles()) //can be overwritten - see documentation
		.pipe(filterJS)
		.pipe(concat('vendor.js'))
		.pipe(gulp.dest(targetPathJS));
});

/**
 * Javascript
 * Concatenation and minification
 * - concatenate vendor.js add main.js files to target file
 * - run only if not linted with error (is dependent on js-lint)
 * - if in production mode, remove debug calls and minify target file (call with --production option)
 **/
var jsSources = [
	targetPathJS + '/vendor.js',
	pathJS + '/main.js'
];

var lintedWithError = false;

gulp.task('js', ['bower-js-files', 'js-lint'], function () {
	if (lintedWithError) return;

	uglify = uglify || require('gulp-uglify');
	stripDebug = stripDebug || require('gulp-strip-debug');

	return gulp.src(jsSources)
		.pipe(gulpif(argv.production, stripDebug()))
		.pipe(gulpif(!argv.debug, uglify()))
		.pipe(concat('all.min.js'))
		.pipe(gulp.dest(targetPathJS));
});

/**
 * Javascript
 * Linting of javascript source files.
 * Set lintedWithError variable, which is used in js task
 */
gulp.task('js-lint', function () {
	lintedWithError = false;

	eslint = eslint || require('gulp-eslint');

	return gulp.src(pathJS + '/main.js').pipe(eslint({
		'extends': 'standard', //installed by 'eslint-config-standard'
		'rules': {
			'quotes': [2, 'single'], // single quotes
			'semi': [2, 'always'], //force semicolons
			'indent': [2, 'tab'], //tab for indent
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
	.pipe(eslint.format())
	// Brick on failure to be super strict
	.pipe(eslint.failAfterError())
	.on('error', handleError)
	.pipe(gulp.dest(pathJS));

});

function handleError(err) {
	lintedWithError = true;
}

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
	spritesmith = spritesmith || require('gulp.spritesmith');
	buffer = buffer || require('vinyl-buffer');

	var spriteData =
		gulp.src(pathSpriteSources + '/*.*') // source path of the sprite images
			.pipe(spritesmith({
				imgName: 'sprite.png',
				cssName: 'sprite.scss',
				imgPath: targetPathImages+'/sprite.png',
			}));

	spriteData.css
		.pipe(gulp.dest(targetPathCSS)); // output path for the CSS or SASS
	spriteData.img
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
 * Linter - walk all scss files in stylesheets directory and lint them (only libs path ignored)
 */
gulp.task('sass-lint', function() {
	sasslint = sasslint || require('gulp-sass-lint');
	var sassSources = [pathSASS+'/**/*.scss', '!'+pathSASS+'/libs/*.*'];
	return gulp.src(sassSources)
		.pipe(sasslint())
		.pipe(sasslint.format())
		.pipe(sasslint.failOnError());
});

var sassMainSource = pathSASS + '/main.scss';

/**
 * SASS import
 * Automatically inject Less and Sass Bower dependencies. See
 * Bower dependencies wired into styles
 * https://github.com/taptapship/wiredep
 */
gulp.task('wiredep', function() {
	wiredep = wiredep || require('wiredep').stream;
	changed = changed || require('gulp-changed');
	return gulp.src(sassMainSource)
		.pipe(wiredep())
		.pipe(changed(pathSASS, {
			hasChanged: changed.compareSha1Digest
		}))
		.pipe(gulp.dest(pathSASS));
});

/**
 * SASS and CSS
 * Concatenation, compilation and minification
 * - pick only main.scss, that includes all necessary dependencies
 * - if in production mode, minify target file
 */
//bootstrap is included in main.scss if necessary (takes it directly from bower_dependencies directory)

gulp.task('sass', ['sprite', 'wiredep', 'sass-lint'], function () {
	sass = sass || require('gulp-sass');
	rename = rename || require('gulp-rename');
	cssmin = cssmin || require('gulp-cssmin');
	autoprefixer = autoprefixer || require('gulp-autoprefixer');

	return gulp.src(sassMainSource)
		.pipe(sass())
		.pipe(concat('tmp.css'))
        .pipe(autoprefixer({
            browsers: [
                'last 2 versions',
                'android 4',
                'opera 12',
                'ie >= 9'
            ],
			cascade: false
        }))
		.pipe(gulpif(!argv.debug, cssmin()))
		.pipe(rename('all.min.css'))
		.pipe(gulp.dest(targetPathCSS));
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
 * Watch changes of source files
 */
gulp.task('watch', function () {
	if (!argv.production && argv['watch'] === false) {
		gulp.watch(pathSpriteSources + '/*.*', ['sprite']);
		gulp.watch([pathSASS + '/**/*.scss'], ['sass']);
		gulp.watch([pathJS + '/*.js', '!' + pathJS + '/*.min.js', [pathJSLibs + '/*.js']], ['js']);
		//For watching imagemin it is necessary to have assets and target directory, or it will end up with infinite loop.
		gulp.watch(pathImages + '/*', ['imagemin']);
	}
});

/**
 * Default task
 * Options:
 *  --production: do minification and strip js debug calls. Also disable changes watching
 *  --no-watch: only disable changes watching
 */
gulp.task('default', ['sass', 'js', 'imagemin', 'watch', 'fonts']);

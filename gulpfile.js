// Images related.
var imagesSRC               = './assets/**/*.{png,jpg,gif,svg}'; // Source folder of images which should be optimized.
var imagesDestination       = './assets/img/'; // Destination folder of optimized images. Must be different from the imagesSRC folder.

// Watch files paths.
var styleWatchFiles         = [ './templates/scss/**/*.scss' ]; // Path to all *.scss files inside css folder and inside them.
var projectPHPWatchFiles    = [
  './includes/**/*.php',
  './addons/**/*.php',
  './admin/**/*.php',
  './templates/**/*.php',
  './classes/**/*.php',
  './ajax/**/*.php'
];

// Browsers you care about for autoprefixing.
// Browserlist https        ://github.com/ai/browserslist
const AUTOPREFIXER_BROWSERS = [
  'last 2 version',
  '> 1%',
  'ie >= 9',
  'ie_mob >= 10',
  'ff >= 30',
  'chrome >= 34',
  'safari >= 7',
  'opera >= 23',
  'ios >= 7',
  'android >= 4',
  'bb >= 10'
];

// STOP Editing Project Variables.

/**
* Load Plugins.
*
* Load gulp plugins and passing them semantic names.
*/
var gulp         = require('gulp'); // Gulp of-course

// CSS related plugins.
var sass         = require('gulp-sass'); // Gulp pluign for Sass compilation.
var minifycss    = require('gulp-uglifycss'); // Minifies CSS files.
var autoprefixer = require('gulp-autoprefixer'); // Autoprefixing magic.
var mmq          = require('gulp-merge-media-queries'); // Combine matching media queries into one media query definition.

// JS related plugins.
var concat       = require('gulp-concat'); // Concatenates JS files
var uglify       = require('gulp-uglify'); // Minifies JS files

// Image realted plugins.
var imagemin     = require('gulp-imagemin'); // Minify PNG, JPEG, GIF and SVG images with imagemin.

// Utility related plugins.
var rename       = require('gulp-rename'); // Renames files E.g. style.css -> style.min.css
var lineec       = require('gulp-line-ending-corrector'); // Consistent Line Endings for non UNIX systems. Gulp Plugin for Line Ending Corrector (A utility that makes sure your files have consistent line endings)
var filter       = require('gulp-filter'); // Enables you to work on a subset of the original files by filtering them using globbing.
var sourcemaps   = require('gulp-sourcemaps'); // Maps code in a compressed file (E.g. style.css) back to it’s original position in a source file (E.g. structure.scss, which was later combined with other css files to generate style.css)
var notify       = require('gulp-notify'); // Sends message notification to you
var browserSync  = require('browser-sync').create(); // Reloads browser and injects CSS. Time-saving synchronised browser testing.
var reload       = browserSync.reload; // For manual browser reload.
var wpPot        = require('gulp-wp-pot'); // For generating the .pot file.
var sort         = require('gulp-sort'); // Recommended to prevent unnecessary changes in pot-file.
var wait = require('gulp-wait')


gulp.task( 'browser-sync', function() {
  browserSync.init( {
    proxy: 'anspress.local',
    host: 'anspress.local',
    open: 'external',
    injectChanges: true,
    ghostMode: {
      scroll: false
    }
  } );
});

gulp.task('styles', function () {
  gulp.src( './templates/scss/main.scss' )
  .pipe(wait(50))
  .pipe( sourcemaps.init() )
  .pipe( sass( {
    errLogToConsole: true,
    outputStyle: 'compressed',
    precision: 10
  } ) )
  .on('error', console.error.bind(console))
  .pipe( sourcemaps.write( { includeContent: false } ) )
  .pipe( sourcemaps.init( { loadMaps: true } ) )
  .pipe( autoprefixer( AUTOPREFIXER_BROWSERS ) )

  .pipe( sourcemaps.write ( './templates/css/' ) )
  .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
  .pipe( gulp.dest( './templates/css' ) )

  .pipe( filter( '**/*.css' ) ) // Filtering stream to only css files
  .pipe( mmq( { log: true } ) ) // Merge Media Queries only for .min.css version.

  .pipe( browserSync.stream() ) // Reloads style.css if that is enqueued.

  .pipe( rename( { suffix: '.min' } ) )
  .pipe( minifycss( {
    maxLineLen: 10
  }))
  .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
  .pipe( gulp.dest( './templates/css/maps' ) )

  .pipe( filter( '**/*.css' ) ) // Filtering stream to only css files
  .pipe( browserSync.stream() )// Reloads style.min.css if that is enqueued.
  //.pipe( notify( { message: 'TASK: "styles" Completed! 💯', onLast: true } ) )
});

gulp.task( 'images', function() {
  gulp.src( imagesSRC )
  .pipe( imagemin( {
    progressive: true,
    optimizationLevel: 3, // 0-7 low-high
    interlaced: true,
    svgoPlugins: [{removeViewBox: false}]
  } ) )
  .pipe(gulp.dest( imagesDestination ))
  .pipe( notify( { message: 'TASK: "images" Completed! 💯', onLast: true } ) );
});

gulp.task( 'translate', function () {
  return gulp.src( projectPHPWatchFiles )
  .pipe(sort())
  .pipe(wpPot( {
    domain        : 'anspress-question-answer',
    package       : 'AnsPress',
    bugReport     : 'https://anspress.io/questions/',
    lastTranslator: 'Rahul Aryan <rah12@live.com>'
  } ))
  .pipe(gulp.dest('./languages/anspress-question-answer.pot' ))
  .pipe( notify( { message: 'TASK: "translate" Completed! 💯', onLast: true } ) )

});

gulp.task( 'js', function() {
  gulp.src( [
    './assets/js/common.js',
    './assets/js/ask.js',
    './assets/js/list.js',
    './assets/js/notifications.js',
    './assets/js/question.js',
    './assets/js/tags.js'
  ] )
  .pipe( concat( 'main.js' ) )
  .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
  .pipe( gulp.dest( './assets/js/' ) )
  // .pipe( rename( {
  //   basename: jsVendorFile,
  //   suffix: '.min'
  // }))
  // .pipe( uglify() )
  .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
  .pipe( gulp.dest( './assets/js/' ) )
  //.pipe( notify( { message: 'TASK: "js" Completed! 💯', onLast: true } ) );
});

gulp.task( 'default', ['styles', 'js', 'browser-sync'], function () {
  gulp.watch( projectPHPWatchFiles, reload ); // Reload on PHP file changes.
  gulp.watch( styleWatchFiles, [ 'styles' ] ); // Reload on SCSS file changes.
  gulp.watch( './assets/js/*.js', [ 'js' ] ); // Reload on JS file changes.
});
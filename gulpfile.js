var pkg = require('./package.json');

var gulp = require('gulp'),
    concat = require('gulp-concat'),
    rename = require("gulp-rename"),
    sourcemaps = require('gulp-sourcemaps'),
    jsmin = require('gulp-jsmin'),
    uglify = require('gulp-uglify'),
    sass = require('gulp-sass'),
    browserify = require('browserify'),
    vueify = require('vueify'),
    source = require('vinyl-source-stream'), //https://www.npmjs.com/package/vinyl-source-stream
    buffer = require('vinyl-buffer'), //https://www.npmjs.com/package/vinyl-buffer
    babelify = require('babelify'),
    zip = require('gulp-zip'),
    bower = require('gulp-bower'),
    copy = require('gulp-copy'),
    csso = require('gulp-csso'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    cssnano = require('cssnano'),
    runSequence  = require('run-sequence'),
    wpPot = require('gulp-wp-pot'),
    sort = require('gulp-sort'),
    merge  = require('merge-stream');

var plugin_slug = "waboot-woo-product-importer";

var paths = {
    builddir: "./builds",
    scripts: ['./assets/src/js/**/*.js'],
    admin_mainjs: ['./assets/src/js/dashboard.js'],
    front_mainjs: ['./assets/src/js/frontend.js'],
    admin_pkgjs: ['./assets/dist/js/dashboard.pkg.js'],
    front_pkgjs: ['./assets/dist/js/frontend.pkg.js'],
    mainscss: './assets/src/scss/main.scss',
    maincss: './assets/src/css/main.css',
    build: [
        "**/*", 
        "!.*" ,
        "!gulpfile.js", 
        "!package.json",
        "!yarn.lock",
        "!bower.json",
        "!composer.json",
        "!composer.lock",
        "!{builds,builds/**}",
        "!{node_modules,node_modules/**}",
        "!{bower_components,bower_components/**}",
        "!{vendor,vendor/**}",
    ]
};

/**
 * Browserify magic! Creates bundle.js
 */
gulp.task('browserify', function(){
    var dashboard = browserify(paths.admin_mainjs,{
        insertGlobals : true,
        debug: true
    })
        .transform("babelify", {presets: ["latest"]}).bundle()
        .pipe(source('dashboard.pkg.js'))
        .pipe(buffer()) //This might be not required, it works even if commented
        .pipe(gulp.dest('./assets/dist/js'));

    var frontend = browserify(paths.front_mainjs,{
        insertGlobals : true,
        debug: true
    })
        .transform("babelify", {presets: ["latest"]})
        .transform(vueify)
        .bundle()
        .pipe(source('frontend.pkg.js'))
        .pipe(buffer()) //This might be not required, it works even if commented
        .pipe(gulp.dest('./assets/dist/js'));

    return merge(dashboard,frontend);
});

/**
 * Creates and minimize bundle.js into <pluginslug>.min.js
 */
gulp.task('compile_js', ['browserify'] ,function(){
    var dashboard = gulp.src(paths.admin_pkgjs)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename('dashboard.min.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./assets/dist/js'));

    var frontend = gulp.src(paths.front_pkgjs)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename('frontend.min.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./assets/dist/js'));

    return merge(dashboard,frontend);
});

/**
 * Rerun the task when a file changes
 */
gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['compile_js']);
});

/**
 * Default task
 */
gulp.task('default', function(callback){
    runSequence(['compile_js'], callback);
});
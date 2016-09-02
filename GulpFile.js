var gulp = require('gulp'),
    sass = require('gulp-sass'),
    cleanCSS = require('gulp-clean-css'),
    exec = require('child_process').exec,
    livereload = require('gulp-livereload'),
    concat = require('gulp-concat'),
    useref = require('gulp-useref'),
    gulpif = require('gulp-if'),
    uglify = require('gulp-uglify');

function swallowError (error) {

    console.log(error.toString())

    this.emit('end')
}

gulp.task('sass-dev', function () {

    gulp.src('./web/bundles/app/sass/master.scss')
        .pipe(sass({sourceComments: 'map'}))
        .on('error', swallowError)
        .pipe(gulp.dest('./web/css/'));
});

gulp.task('sass-prod', function () {

    gulp.src('./web/bundles/app/sass/master.scss')
        .pipe(sass({sourceComments: 'map'}))
        .pipe(cleanCSS({compatibility: 'ie8'}))
        .on('error', swallowError)
        .pipe(gulp.dest('./web/css/'));
});

var livereload = require('gulp-livereload');

gulp.task('watch', function () {
    var onChange = function (event) {
        console.log('File '+event.path+' has been '+event.type);

        gulp.task('reload', ['installAssets', 'sass-dev']);

        // Tell LiveReload to reload the window
        livereload.changed(event.path);
    };
    // Starts the server
    livereload.listen();
    gulp.watch('./src/*/Resources/public/sass/**/*.scss', ['sass-dev'])
        .on('change', onChange);
});

gulp.task('installAssets', function () {
    exec('php bin/console assets:install --symlink', function (err, stdout, stderr) {
        console.log(stdout + stderr);
    });
});

gulp.task('default', ['sass-dev']);

gulp.task('dev', ['sass-dev']);

gulp.task('prod', ['sass-prod']);

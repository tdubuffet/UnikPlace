var gulp = require('gulp');


var sass = require('gulp-sass');
var cleanCSS = require('gulp-clean-css');
var exec = require('child_process').exec;
var livereload = require('gulp-livereload');
var concat = require('gulp-concat');

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

gulp.task('scripts', function() {
    gulp.src(['./web/components/jquery/dist/jquery.js', './web/components/bootstrap-sass/assets/javascripts/bootstrap.js', './web/bundles/app/js/*.js'])
        .on('error', swallowError)
        .pipe(concat('master.js'))
        .pipe(gulp.dest('./web/js/'));
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
    gulp.watch('./src/*/Resources/public/js/**/*.js', ['scripts'])
        .on('change', onChange);
});

gulp.task('installAssets', function () {
    exec('php bin/console assets:install --symlink', function (err, stdout, stderr) {
        console.log(stdout + stderr);
    });
});

gulp.task('default', ['sass-dev']);

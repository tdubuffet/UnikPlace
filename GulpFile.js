var gulp = require('gulp');

var sass = sass = require('gulp-sass');

function swallowError (error) {

    // If you want details of the error in the console
    console.log(error.toString())

    this.emit('end')
}

gulp.task('sass', function () {

    gulp.src('./web/bundles/app/sass/master.scss')
        .pipe(sass({sourceComments: 'map'}))
        .on('error', swallowError)
        .pipe(gulp.dest('./web/css/'));
});



var livereload = require('gulp-livereload');

gulp.task('watch', function () {
    var onChange = function (event) {
        console.log('File '+event.path+' has been '+event.type);


        gulp.task('reload', ['installAssets', 'sass']);
        // Tell LiveReload to reload the window
        livereload.changed(event.path);
    };
    // Starts the server
    livereload.listen();
    gulp.watch('./src/*/Resources/public/sass/**/*.scss', ['sass'])
        .on('change', onChange);
    gulp.watch('./src/*/Resources/public/js/**/*.js', ['js'])
        .on('change', onChange);
});

var exec = require('child_process').exec;

gulp.task('installAssets', function () {
    exec('php bin/console assets:install --symlink', logStdOutAndErr);
});

// Without this function exec() will not show any output
var logStdOutAndErr = function (err, stdout, stderr) {
    console.log(stdout + stderr);
};
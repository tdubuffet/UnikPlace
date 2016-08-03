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

gulp.task('scripts-dev', function() {
    gulp.src([
            './web/components/jquery/dist/jquery.js',
            './web/components/bootstrap-sass/assets/javascripts/bootstrap.js',
            './web/components/jquery-validation/dist/jquery.validate.js',
            './web/components/owl.carousel/dist/owl.carousel.js',
            './web/components/jquery-sticky/jquery.sticky.js',
            './web/components/elevatezoom/jquery.elevatezoom.js',
            './web/components/bootbox.js/bootbox.js',
            './web/components/select2/dist/js/select2.js',
            './web/components/select2/dist/js/i18n/fr.js',

            './web/components/blueimp-load-image/js/load-image.all.min.js',
            './web/components/blueimp-file-upload/js/vendor/jquery.ui.widget.js',
            './web/components/blueimp-file-upload/js/jquery.iframe-transport.js',
            './web/components/blueimp-file-upload/js/jquery.fileupload.js',
            './web/components/blueimp-file-upload/js/jquery.fileupload-process.js',
            './web/components/blueimp-file-upload/js/jquery.fileupload-image.js',
            './web/components/html.sortable/dist/html.sortable.js',

            './web/bundles/**/js/*.js'
        ])
        .on('error', swallowError)
        .pipe(concat('master.js'))
        .pipe(gulp.dest('./web/js/'));
});

gulp.task('scripts-prod', function() {
    gulp.src([
            './web/components/jquery/dist/jquery.js',
            './web/components/bootstrap-sass/assets/javascripts/bootstrap.js',
            './web/components/jquery-validation/dist/jquery.validate.js',
            './web/bundles/**/js/*.js'
        ])
        .on('error', swallowError)
        .pipe(useref())
        .pipe(gulpif('*.js', uglify({
            mangle: false
        })))
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
    gulp.watch('./src/*/Resources/public/js/**/*.js', ['scripts-dev'])
        .on('change', onChange);
});

gulp.task('installAssets', function () {
    exec('php bin/console assets:install --symlink', function (err, stdout, stderr) {
        console.log(stdout + stderr);
    });
});

gulp.task('default', ['sass-dev', 'scripts-dev']);

gulp.task('dev', ['sass-dev', 'scripts-dev']);

gulp.task('prod', ['sass-prod', 'scripts-prod']);

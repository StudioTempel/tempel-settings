/**
 *  Set up Gulp and other plugins
 */
import gulp from 'gulp';
import browserSync from 'browser-sync';
import sass from 'gulp-sass';
import sourceMaps from 'gulp-sourcemaps';
import uglify from 'gulp-uglify';
import jshint from 'gulp-jshint';
import stylish from 'jshint-stylish';
import concat from 'gulp-concat';
import imagemin from 'gulp-imagemin';
import autoprefixer from 'gulp-autoprefixer';
import plumber from 'gulp-plumber';
import babel from 'gulp-babel';

const reload = browserSync.reload;
const bourbon = require('bourbon').includePaths;
const proxyUrl = 'http://plugin-testing.local/wp-admin/admin.php?page=tempel-settings';

const paths = {
    scripts: {
        source: 'assets/js/scripts.js',
        destination: 'dist/js/',
        destinationWatcher: 'dist/js/scripts.min.js'
    },
    sass: {
        source: 'assets/sass/styles.scss',
        sourceWatcher: 'assets/sass/**/*.scss',
        destination: 'dist/css',
        destinationWatcher: ''
    },
    php: {
        source: '**/*.php',
        destination: '',
        destinationWatcher: ''
    }
};

export function styles() {
    return gulp.src(paths.sass.source)
        .pipe(plumber())
        .pipe(sourceMaps.init())
        .pipe(sass({
            includePaths: [
                bourbon, // Include Bourbon
            ],
            outputStyle: 'compressed'
        }))
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(gulp.dest(paths.sass.destination))
        .pipe(reload({stream: true}));
}

export function scripts() {
    return gulp.src(paths.scripts.source)
        .pipe(babel())
        .pipe(plumber())
        .pipe(jshint('.jshintrc'))
        .pipe(jshint.reporter(stylish))
        .pipe(uglify())
        .pipe(concat('scripts.min.js'))
        .pipe(gulp.dest(paths.scripts.destination));
}

export function watch() {
    browserSync({
        proxy: proxyUrl,
        ghostMode: false
    });

    gulp.watch(paths.sass.sourceWatcher, styles);
    gulp.watch(paths.scripts.source, scripts);
    gulp.watch([
        paths.php.source,
        paths.scripts.destinationWatcher,
    ]).on('change', reload);
}

const build = gulp.parallel(styles, scripts, watch);

gulp.task('build', build);
gulp.task('default', build);

export default build;

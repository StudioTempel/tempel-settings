import gulp from "gulp";
import browserSync from "browser-sync";
import dartSass from "sass";
import gulpSass from "gulp-sass";
import sourceMaps from "gulp-sourcemaps";
import uglify from "gulp-uglify";
import jshint from "gulp-jshint";
import stylish from "jshint-stylish";
import concat from "gulp-concat";
import plumber from "gulp-plumber";
import babel from "gulp-babel";
import postcss from "gulp-postcss";
import autoprefixer from "gulp-autoprefixer";
import imagemin from "gulp-imagemin";

const reload = browserSync.reload;
const proxyUrl = "http://plugin-testing.local";
const sass = gulpSass(dartSass);

const paths = {
  sass: {
    source: [
      "assets/sass/styles.scss",
      "assets/sass/admin-theme.scss",
      "assets/sass/dashboard-widgets.scss",
      "assets/sass/login-screen.scss",
      "assets/sass/toolbar-theme.scss",
      "assets/sass/widget-settings.scss",
    ],
    sourceWatcher: "assets/sass/**/*.scss",
    destination: "dist/css",
  },
  scripts: {
    source: [
      "assets/js/scripts.js",
      "assets/js/admin-theme.js",
      "assets/js/login-screen.js",
    ],
    destination: "dist/js",
    destinationWatcher: "dist/js/scripts.min.js",
  },
  images: {
    source: "assets/images/**/*",
    destination: "dist/images",
  },
  vendor: {
    source: "assets/vendor/**/*",
    destination: "dist/vendor",
  },
  php: {
    source: "**/*.php",
  },
};

export function styles() {
  return gulp
    .src(paths.sass.source)
    .pipe(
      plumber({
        errorHandler: function (error) {
          console.log(error.message);
          this.emit("end");
        },
      }),
    )
    .pipe(sourceMaps.init())
    .pipe(
      sass({
        outputStyle: "compressed",
      }),
    )
    .pipe(
      autoprefixer({
        cascade: false,
      }),
    )
    .pipe(gulp.dest(paths.sass.destination))
    .pipe(reload({ stream: true }));
}

export function scripts() {
  return gulp
    .src(paths.scripts.source)
    .pipe(babel())
    .pipe(
      plumber({
        errorHandler: function (error) {
          console.log(error.message);
          this.emit("end");
        },
      }),
    )
    .pipe(jshint(".jshintrc"))
    .pipe(jshint.reporter(stylish))
    .pipe(uglify())
    .pipe(concat("scripts.min.js"))
    .pipe(gulp.dest(paths.scripts.destination));
}

export function images() {
  return gulp
    .src(paths.images.source, { encoding: false })
    .pipe(imagemin())
    .pipe(gulp.dest(paths.images.destination));
}

export function vendor() {
  return gulp
    .src(paths.vendor.source)
    .pipe(gulp.dest(paths.vendor.destination));
}

export function watch() {
  browserSync({
    proxy: proxyUrl,
    ghostMode: false,
  });

  gulp.watch(paths.sass.sourceWatcher, styles);
  gulp.watch(paths.scripts.source, scripts);
  gulp.watch(paths.images.source, images);
  gulp
    .watch([paths.php.source, paths.scripts.destinationWatcher])
    .on("change", reload);
}

const build = gulp.parallel(styles, scripts, images, vendor, watch);

gulp.task("build", build);
gulp.task("default", build);

export default build;

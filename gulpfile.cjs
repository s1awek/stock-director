
const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const terser = require('gulp-terser');
const rename = require('gulp-rename');
const sourcemaps = require('gulp-sourcemaps');

// Kompilacja SCSS -> CSS dla Admin
function adminStyles() {
  return gulp.src('src/admin/scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('admin/css'));
}

// Minifikacja JS dla Admin
function adminScripts() {
  return gulp.src('src/admin/js/**/*.js')
    .pipe(sourcemaps.init())
    .pipe(terser())
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('admin/js'));
}

// Kompilacja SCSS -> CSS dla Public
function publicStyles() {
  return gulp.src('src/public/scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('public/css'));
}

// Minifikacja JS dla Public
function publicScripts() {
  return gulp.src('src/public/js/**/*.js')
    .pipe(sourcemaps.init())
    .pipe(terser())
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('public/js'));
}

// Nasłuchiwanie zmian
function watchFiles() {
  gulp.watch('src/admin/scss/**/*.scss', adminStyles);
  gulp.watch('src/admin/js/**/*.js', adminScripts);
  gulp.watch('src/public/scss/**/*.scss', publicStyles);
  gulp.watch('src/public/js/**/*.js', publicScripts);
}

// Zadania Gulp
exports.adminStyles = adminStyles;
exports.adminScripts = adminScripts;
exports.publicStyles = publicStyles;
exports.publicScripts = publicScripts;
exports.watch = watchFiles;

// Domyślne zadanie Gulp, uruchamiające wszystkie zadania
exports.default = gulp.parallel(adminStyles, adminScripts, publicStyles, publicScripts, watchFiles);
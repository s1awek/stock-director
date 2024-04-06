
// All imports at the top of the file
import gulp from 'gulp';
import dartSass from 'sass';
import gulpSass from 'gulp-sass';
const sass = gulpSass(dartSass);
import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import terser from 'gulp-terser';
import rename from 'gulp-rename';
import sourcemaps from 'gulp-sourcemaps';
import { deleteAsync } from 'del';
import zip from 'gulp-zip';

// Function to clean the distribution directory
async function clean() {
  await deleteAsync(['dist/**', '!dist']);
}

// Function to copy necessary files to the distribution directory
function copy() {
  return gulp.src([
    'admin/**/*',
    'public/**/*',
    'languages/**/*',
    'wp-stock-director.php',
    'README.md',
    '!src', // Exclude src directory
    '!node_modules', // Exclude node_modules directory
    '!admin/**/*.map', // Exclude source maps
    '!public/**/*.map', // Exclude source maps
    '!**/*.scss', // Exclude SCSS files
    '!**/*.cjs', // Exclude .cjs files
    // Add other files/directories to exclude here
  ], { base: '.' })
    .pipe(gulp.dest('dist/'));
}

// Function to create a zip archive from the distribution directory
function zipFiles() {
  return gulp.src('dist/**/*')
    .pipe(zip('wp-stock-director.zip'))
    .pipe(gulp.dest('dist'));
}

// Compilation of SCSS -> CSS for Admin
function adminStyles() {
  return gulp.src('src/admin/scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('admin/css'));
}

// Minification of JS for Admin
function adminScripts() {
  return gulp.src('src/admin/js/**/*.js')
    .pipe(sourcemaps.init())
    .pipe(terser({
      compress: {
        drop_console: false,
      }
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('admin/js'));
}

// Compilation of SCSS -> CSS for Public
function publicStyles() {
  return gulp.src('src/public/scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('public/css'));
}

// Minification of JS for Public
function publicScripts() {
  return gulp.src('src/public/js/**/*.js')
    .pipe(sourcemaps.init())
    .pipe(terser({
      compress: {
        drop_console: false, // Zachowuje wywołania console.*
      }
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('public/js'));
}

// Task for building the distribution version
const dist = gulp.series(clean, copy, zipFiles);

// Watching for changes
function watchFiles() {
  gulp.watch('src/admin/scss/**/*.scss', adminStyles);
  gulp.watch('src/admin/js/**/*.js', adminScripts);
  gulp.watch('src/public/scss/**/*.scss', publicStyles);
  gulp.watch('src/public/js/**/*.js', publicScripts);
}

// Exporting functions as individual exports
export { adminStyles, adminScripts, publicStyles, publicScripts, watchFiles, dist };

// Default Gulp task
const defaultTask = gulp.parallel(adminStyles, adminScripts, publicStyles, publicScripts, watchFiles);
export default defaultTask;

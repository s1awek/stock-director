
// Wszystkie importy na samej górze pliku
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

// Funkcja do czyszczenia katalogu dystrybucyjnego
async function clean() {
  await deleteAsync(['dist/**', '!dist']);
}

// Funkcja do kopiowania niezbędnych plików do katalogu dystrybucyjnego
function copy() {
  return gulp.src([
    'admin/**/*',
    'public/**/*',
    'languages/**/*',
    'wp-stock-director.php',
    'README.md',
    '!src', // Wykluczamy katalog src
    '!node_modules', // Wykluczamy katalog node_modules
    '!admin/**/*.map', // Wykluczamy mapy źródłowe
    '!public/**/*.map', // Wykluczamy mapy źródłowe
    '!**/*.scss', // Wykluczamy pliki SCSS
    '!**/*.cjs', // Wykluczamy pliki .cjs
    // Dodaj tutaj inne pliki/katalogi do wykluczenia
  ], { base: '.' })
    .pipe(gulp.dest('dist/'));
}

// Funkcja do tworzenia archiwum zip z katalogu dystrybucyjnego
function zipFiles() {
  return gulp.src('dist/**/*')
    .pipe(zip('wp-stock-director.zip'))
    .pipe(gulp.dest('dist'));
}

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

// Zadanie do budowania wersji do publikacji
const dist = gulp.series(clean, copy, zipFiles);

// Eksportujemy nowe zadanie, aby było dostępne z linii komend


// Nasłuchiwanie zmian
function watchFiles() {
  gulp.watch('src/admin/scss/**/*.scss', adminStyles);
  gulp.watch('src/admin/js/**/*.js', adminScripts);
  gulp.watch('src/public/scss/**/*.scss', publicStyles);
  gulp.watch('src/public/js/**/*.js', publicScripts);
}

// Eksport funkcji jako pojedynczych eksportów
export { adminStyles, adminScripts, publicStyles, publicScripts, watchFiles, dist };

// Domyślne zadanie Gulp
const defaultTask = gulp.parallel(adminStyles, adminScripts, publicStyles, publicScripts, watchFiles);
export default defaultTask;

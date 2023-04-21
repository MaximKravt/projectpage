const {src , dest , watch, parallel} = require('gulp');
const scss         = require('gulp-sass')(require('sass'));
const concat       = require('gulp-concat');
const browserSync  = require('browser-sync').create();
const uglify       = require('gulp-uglify-es').default;




function scripts() {
    return src(['app/js/script.js','!app/js/main.min.js'])
    .pipe(concat('script.min.js'))
    .pipe(uglify())
    .pipe(dest('app/dist/js'))
    .pipe(browserSync.stream());
}


function styles(){
    return src('app/scss/style.scss')
    //.pipe(autoprefixer({overrideBrowserslist:['last 10 version']}))
    .pipe(concat('style.min.css'))
    .pipe(scss({outputStyle: 'compressed'}))
    .pipe(dest('app/dist/css'))
    .pipe(browserSync.stream());

}


function watching(){
    watch(['app/scss/style.scss'],styles)
    watch(['app/js/script.js'],scripts)
    watch("app/*.html").on('change', browserSync.reload);
}


function browsersync() {
    browserSync.init({
        server: {
            baseDir: "app/"
        }
    });
}


exports.styles = styles; 
exports.scripts = scripts;
exports.watching = watching;
exports.browsersync = browsersync;
exports.default = parallel(styles,scripts,browsersync,watching);
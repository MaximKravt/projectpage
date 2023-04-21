const { src, dest , parallel ,watch} = require('gulp');
const concat                          = require('gulp-concat');  
const browserSync                     = require('browser-sync', 'reload').create();
var uglify                            = require('gulp-uglify');


function browsersync()  {
    browserSync.init({
        server: {
            baseDir: "./"
        },
        notify: true,
        online: true
    })
}



function scripts() {

    return src([  'src/js/*.js'])
     uglify()
    .pipe(concat('app.min.js'))
    .pipe(dest('scripts'))
    .pipe(browserSync.stream())

}


function styles(){

return src(['src/css/*.css'])
.pipe(concat('min.css'))
.pipe(dest('style'))
.pipe(browserSync.stream())
}




function startwatch() {
    watch(['style/*.min.css'], styles);
    watch(['scripts/*.min.js'] , scripts);
}


exports.browsersync = browsersync;
exports.scripts     = scripts;
exports.styles      = styles;


exports.default     = parallel(scripts, styles ,browsersync,startwatch);
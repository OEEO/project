/**
 * Created by yuhuajie on 2017/8/21.
 */
const gulp = require('gulp');
const cssmin = require('gulp-cssmin');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const autoprefixer = require('gulp-autoprefixer');
const uglify = require('gulp-uglify');
const argv = require('yargs').argv;
const replace = require('gulp-replace');
const less = require('gulp-less');
const postcss = require('gulp-postcss');
const sourcemaps = require('gulp-sourcemaps');
const base64 = require('gulp-base64');

let prodDomain = '//mat1.gtimg.com/sports/sportAppWeb/douyuthirdparty';
let prodPath = `${prodDomain}/scripts`;
let devPath = 'http://localhost:8080/tmp/app/js';
let preProdPath = '.';

function args() {
    let argvs = process.argv;

    let result = {};
    let lastArgv = '';

    for (let i = 0, num = argvs.length; i < num; i++) {

        let reg = argvs[i].match(/^--([a-zA-Z0-9]+)/);
        if (reg) {
            result[reg[1]] = null;
            lastArgv = reg[1];
        } else if (lastArgv !== '') {
            result[lastArgv] = argvs[i];
        }
    }

    return result;
}

gulp.task('gen:js', function () {
    let argv = args();
    let page = argv.page;

    if (!page) {
        console.error('\x1B[31m%s\x1B[39m', '请填入页面名字，例如："gulp gen:html --page schedule"');
    } else {
        return gulp.src('./src/script/js/_template.js')
            .pipe(replace('{page}', page))
            .pipe(rename(`${page}.js`))
            .pipe(gulp.dest(`./src/script/js`));
    }
});

gulp.task('gen:html', function () {
    let argv = args();
    let page = argv.page;

    if (!page) {
        console.error('\x1B[31m%s\x1B[39m', '请填入页面名字，例如："gulp gen:html --page schedule"');
    } else {
        return gulp.src('./template.html')
            .pipe(replace('{page}', page))
            .pipe(rename(`${page}.html`))
            .pipe(gulp.dest('./cover'));
    }
});

gulp.task('gen', ['gen:html', 'gen:js']);

gulp.task('uglify', function () {
    return gulp.src('./src/script/**/*.js')
        .pipe(uglify())
        .pipe(gulp.dest('./dist/source/script'));
});

gulp.task('style-dev', function () {
    return gulp.src('./src/less/**/*.less')
        .pipe(less())
        .pipe(autoprefixer({
            browsers: 'last 100 versions',
            cascade: false, //是否美化属性值 默认：true 像这样：
            remove: false
        }))
        .pipe(cssmin())
        .pipe(base64())
        .pipe(concat('main.css'))
        .pipe(replace('display:-webkit-flex;display:-webkit-box;', 'display:-webkit-box;display:-webkit-flex;'))
        .pipe(gulp.dest('./tmp/style'));
});

gulp.task('style', function () {

    let DIST = process.env.NODE_ENV === 'production'
        ? 'dist'
        : 'pre';

    return gulp.src('./src/less/**/*.less')
        .pipe(less())
        .pipe(autoprefixer({
            browsers: 'last 100 versions',
            cascade: false, //是否美化属性值 默认：true 像这样：
            remove: false
        }))
        .pipe(cssmin())
        .pipe(base64())
        .pipe(concat('main.css'))
        .pipe(replace('display:-webkit-flex;display:-webkit-box;', 'display:-webkit-box;display:-webkit-flex;'))
        .pipe(gulp.dest(`./${DIST}/kog/src/style`))
        .pipe(gulp.dest(`./${DIST}/lol/src/style`));
});

gulp.task('prod:js', function () {
    return gulp.src('./src/script/js/*.js')
        .pipe(uglify({  
            ie8: true
        }))
        .pipe(gulp.dest('./dist/src/script'));
});

gulp.task('prod', ['prod:style']);

gulp.task('watch', function () {
    gulp.watch('./src/less/**/*.less', ['style-dev']);
});

gulp.task('dev', ['style-dev', 'watch']);
gulp.task('default', ['dev']);
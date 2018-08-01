/**
 * Created by yuhuajie on 2017/8/21.
 */
const gulp = require('gulp');
const cssmin = require('gulp-cssmin');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const autoprefixer = require('gulp-autoprefixer');
// const autoprefixer = require('autoprefixer');
const uglify = require('gulp-uglify');
const argv = require('yargs').argv;
const replace = require('gulp-replace');
const less = require('gulp-less');
const postcss = require('gulp-postcss');
const sourcemaps = require('gulp-sourcemaps');

// let prodPath = 'http://static.gameday.ren/esport_game_app/dist/app';
let prodDomain = '//mat1.gtimg.com/sports/sportAppWeb/douyuthirdparty';
let prodPath = `${prodDomain}/scripts`;
let devPath = 'http://localhost:8080/tmp/app/js';
let preProdPath = '.';

// 生产环境下，修改时间戳和文件路径
gulp.task('replace-path-prod', function () {
    return gulp.src('./index.html')
        .pipe(replace(devPath, prodPath))
        .pipe(replace(/\?t=([^'])*/g, `?t=${Date.now()}`))
        .pipe(gulp.dest('./'));
});

// 开发环境下，修改时间戳和文件路径
gulp.task('replace-path-dev', function () {
    return gulp.src('./index2.html')
        .pipe(replace(prodPath, devPath))
        .pipe(gulp.dest('./'));
});

gulp.task('replace-test-domain', function () {
    return gulp.src('./*.html')
        .pipe(replace(prodDomain, '.'))
        .pipe(gulp.dest('./'));
});

gulp.task('style', function () {
    return gulp.src('./src/style/**/*.less')
        .pipe(less())
        .pipe(autoprefixer())
        .pipe(concat('main.css'))
        .pipe(cssmin())
        .pipe(gulp.dest('./style'));
});

gulp.task('global', function () {
    return gulp.src('./src/style/global.css')
        .pipe(autoprefixer())
        .pipe(cssmin())
        .pipe(gulp.dest('./dist/style'))
        .pipe(gulp.dest('./pre/style'));
});

gulp.task('watch', function () {
    gulp.watch('src/style/**/*.less', ['style']);
});

gulp.task('uglify', function () {
    return gulp.src('./scripts/adaptive.js')
        .pipe(uglify())
        .pipe(gulp.dest('./scripts'));
});

gulp.task('style-prod', function () {
    return gulp.src('./dist/style/**/*.css')
        .pipe(autoprefixer({
            browsers: 'last 100 versions',
            cascade: false, //是否美化属性值 默认：true 像这样：
            remove: false
        }))
        .pipe(cssmin())
        .pipe(replace('display:-webkit-flex;display:-webkit-box;', 'display:-webkit-box;display:-webkit-flex;'))
        .pipe(gulp.dest('./dist/style'));
});

gulp.task('style-pre', function () {
    return gulp.src('./pre/style/**/*.css')
        .pipe(autoprefixer({
            browsers: 'last 100 versions',
            cascade: false, //是否美化属性值 默认：true 像这样：
            remove: false
        }))
        .pipe(cssmin())
        .pipe(replace('display:-webkit-flex;display:-webkit-box;', 'display:-webkit-box;display:-webkit-flex;'))
        .pipe(gulp.dest('./pre/style'));
});

gulp.task('change-adaptive', function () {
    let tmpAdaptivePath = 'http://static.gameday.ren/esport_game_web/scripts/adaptive.js';
    let prodAdaptivePath = '//mat1.gtimg.com/sports/sportAppWeb/douyuthirdparty/scripts/adaptive-version2.js';

    return gulp.src('./dist/*.html')
        .pipe(replace(tmpAdaptivePath, prodAdaptivePath))
        .pipe(gulp.dest('./dist'));
});

gulp.task('prod', ['style-prod', 'change-adaptive']);
gulp.task('pre', ['style-pre']);
gulp.task('dev', ['style', 'global', 'replace-path-dev', 'watch']);
gulp.task('default', ['style', 'global']);
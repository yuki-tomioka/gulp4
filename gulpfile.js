/*===================================================
          読み込み
===================================================*/
const gulp = require('gulp')

// sass関連読み込み
const sass = require('gulp-sass')
const postcss = require('gulp-postcss')
const autoprefixer = require('autoprefixer')
const flexBugsFixes = require('postcss-flexbugs-fixes')
const cssWring = require('csswring')
const sourcemaps = require('gulp-sourcemaps')
const cssMqpacker = require('css-mqpacker')
const concat = require('gulp-concat')

// 画像圧縮関連読み込み
const imagemin = require('gulp-imagemin')
const imageminPngquant = require('imagemin-pngquant')
const imageminMozjpeg = require('imagemin-mozjpeg')
const del = require('del');

// ブラウザ関連読み込み
const browserSync = require('browser-sync').create()

/*===================================================
          設定
===================================================*/
// sass関連設定
const autoprefixerOption = {
  grid: true
}

const postcssOption = [
  flexBugsFixes,
  autoprefixer(autoprefixerOption),
  cssMqpacker,
  cssWring
]

// 画像圧縮関連設定
const imageminOption = [
  imageminPngquant({ quality: '65-80' }),
  imageminMozjpeg({ quality: '80' }),
  imagemin.gifsicle(),
  imagemin.jpegtran(),
  imagemin.optipng(),
  imagemin.svgo()
]

// ブラウザ関連設定
const browserSyncOption = {
  server: './dist'
}
/*===================================================
          タスク定義
===================================================*/
// sassコンパイル
gulp.task('sass', () => {
  return gulp
    .src('./src/assets/sass/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(postcss(postcssOption))
    .pipe(concat('style.css'))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./dist/assets/css/'))
})

// 画像圧縮
gulp.task('imagemin', () => {
  return gulp
    .src('./src/assets/images/*')
    .pipe(imagemin(imageminOption))
    .pipe(gulp.dest('./dist/assets/images/'))
})

gulp.task('clean', (done) => {
  del(['./dist/assets/images/*'])
  done()
});

// ブラウザ自動更新
gulp.task('serve', (done) => {
  browserSync.init(browserSyncOption)
  done()
})

// wpのstyle.cssのコピー
gulp.task('style', () => {
  return gulp
   .src('./src/style.css')
   .pipe(gulp.dest('./dist/'))
})
/*===================================================
          watchタスク
===================================================*/
// watch実行
gulp.task('watch', (done) => {
  const browserReload = (done) => {
    browserSync.reload()
    done()
  }
  gulp.watch('./src/assets/sass/**/*.scss', gulp.series('sass'))
  gulp.watch('./src/**/images/*', gulp.series('clean', 'imagemin'))
  gulp.watch('./src/style.css', gulp.series('style'))
  gulp.watch('./dist/**/*', browserReload)
})

gulp.task('default', gulp.series('serve', 'watch'))
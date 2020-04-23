var fs = require('fs');
var gulp = require('gulp');
var clean = require('gulp-clean');
var concat = require('gulp-concat');
var jshint = require('gulp-jshint');
var sourcemaps = require('gulp-sourcemaps');
var stylish = require('jshint-stylish');
var uglify = require('gulp-uglify');
var cleanCss = require('gulp-clean-css');
var htmlclean = require('gulp-htmlclean');
var templateCache = require('gulp-angular-templatecache');
var saveLicense = require('uglify-save-license');
var gettextParser = require("gettext-parser");
var po2json = require('po2json');
var foreach = require('gulp-foreach');
var sortObj = require('sort-object');

var path = {
  lintJs: [
    'frontend/src/appAddThisWordPress.js',
    'frontend/src/**/*.js'
  ],
  addThisJs: [
    'frontend/src/appAddThisWordPress.js',
    'frontend/src/**/*.js',
    'frontend/build/templates.js'
  ],
  adminJs: [
    'frontend/admin/**/*.js'
  ],
  addThisPublicCss: [
    'frontend/src/css/public/*.css'
  ],
  addThisAdminCss: [
    'frontend/src/css/admin/*.css'
  ],
  vendorJs: [
    'node_modules/angular/angular.js',
    'node_modules/angular-ui-router/release/angular-ui-router.js',
    'node_modules/angular-hotkeys/build/hotkeys.js',
    'node_modules/angular-aria/angular-aria.js',
    'node_modules/angular-translate/dist/angular-translate.js',
    'node_modules/angular-translate-loader-static-files/angular-translate-loader-static-files.js'
  ],
  vendorCss: [
    //'node_modules/angular-hotkeys/build/hotkeys.css'
  ],
  l10n_src: 'frontend/src/l10n/',
  l10n_po: [
    'frontend/src/l10n/*.po',
    'frontend/build/l10n/*.po'
  ],
  l10n_frontend_po: [
    'frontend/src/l10n/addthis-frontend-*.po',
    'frontend/build/l10n/addthis-frontend-*.po'
  ],
  l10n_build: 'frontend/build/l10n',
  addThisTemplates: [
    'frontend/src/**/*.html',
    'frontend/src/**/*.svg'
  ],
  zipFilesAll: [
    'bootstrap.php',
    'COPYING',
    'readme.txt',
    'backend/*',
    'frontend/build/*',
    'frontend/src/images/*.png'
  ],
  bootstrapMap: {
    followbuttons: 'boostrap.php.followbuttons',
    sharingbuttons: 'boostrap.php.sharingbuttons',
    maximum: 'boostrap.php.maximum',
    minimum: 'boostrap.php.minimum'
  },
  readmeMap: {
    followbuttons: 'readme.txt.followbuttons',
    sharingbuttons: 'readme.txt.sharingbuttons',
    maximum: 'readme.txt.maximum',
    minimum: 'readme.txt.minimum'
  },
  documentation: {
    followbuttons: [],
    sharingbuttons: [],
    maximum: [
      'documentation.*.md'
    ],
    minimum: [
      'documentation.filters.md'
    ]
  },
  buildRoot: 'frontend/build',
  minifiedJsFileName: 'addthis_wordpress.min.js',
  minifiedAdminJsFileName: 'addthis_wordpress_admin.min.js',
  minifiedPublicCssFileName: 'addthis_wordpress_public.min.css',
  minifiedAdminCssFileName: 'addthis_wordpress_admin.min.css',
  minifiedVendorJsFileName: 'vendor.min.js',
  minifiedVendorCssFileName: 'vendor.min.css'
};

gulp.task('clean-minify-vendor-js', function() {
  var file = path.buildRoot+'/'+path.minifiedVendorJsFileName;
  return gulp.src(file, {read: false})
    .pipe(clean());
});

gulp.task('minify-vendor-js', ['clean-minify-vendor-js'], function() {
  return gulp.src(path.vendorJs)
    //.pipe(sourcemaps.init())
    .pipe(concat(path.minifiedVendorJsFileName))
    .pipe(uglify({
      mangle: false,
      output: {
        comments: saveLicense
      }
    }))    //.pipe(sourcemaps.write('../../' + path.buildRoot))
    .pipe(gulp.dest(path.buildRoot));
});

gulp.task('clean-minify-addthis-js', function() {
  var file = path.buildRoot+'/'+path.minifiedJsFileName;
  return gulp.src(file, {read: false})
    .pipe(clean());
});

gulp.task('minify-addthis-js', ['clean-minify-addthis-js', 'concat-templates'], function() {
  return gulp.src(path.addThisJs)
    .pipe(sourcemaps.init())
    .pipe(concat(path.minifiedJsFileName))
    .pipe(uglify({
      mangle: false,
    }))
    .pipe(sourcemaps.write('../../' + path.buildRoot))
    .pipe(gulp.dest(path.buildRoot));
});

gulp.task('clean-minify-admin-js', function() {
  var file = path.buildRoot + '/' + path.minifiedAdminJsFileName;
  return gulp.src(file, { read: false })
    .pipe(clean());
});

gulp.task('minify-admin-js', ['clean-minify-admin-js'], function() {
  return gulp.src(path.adminJs)
    .pipe(sourcemaps.init())
    .pipe(concat(path.minifiedAdminJsFileName))
    .pipe(uglify({
      mangle: false,
    }))
    .pipe(sourcemaps.write('../../' + path.buildRoot))
    .pipe(gulp.dest(path.buildRoot));
});

gulp.task('clean-minify-vendor-css', function() {
  var file = path.buildRoot+'/'+path.minifiedVendorCssFileName;
  return gulp.src(file, {read: false})
    .pipe(clean());
});

gulp.task('minify-vendor-css', ['clean-minify-vendor-css'], function() {
  return gulp.src(path.vendorCss)
    .pipe(sourcemaps.init())
    .pipe(concat(path.minifiedVendorCssFileName))
    .pipe(cleanCss({
      relativeTo: 'node_modules',
      target: 'build'
    }))
    .pipe(sourcemaps.write('../../' + path.buildRoot))
    .pipe(gulp.dest(path.buildRoot));
});

gulp.task('clean-minify-addthis-public-css', function() {
  var file = path.buildRoot+'/'+path.minifiedPublicCssFileName;
  return gulp.src(file, {read: false})
    .pipe(clean());
});

gulp.task('minify-addthis-public-css', ['clean-minify-addthis-public-css'], function() {
  return gulp.src(path.addThisPublicCss)
    .pipe(sourcemaps.init())
    .pipe(concat(path.minifiedPublicCssFileName))
    .pipe(cleanCss({
      relativeTo: 'src',
      target: 'build'
    }))
    .pipe(sourcemaps.write('../../' + path.buildRoot))
    .pipe(gulp.dest(path.buildRoot));
});

gulp.task('clean-minify-addthis-admin-css', function() {
  var file = path.buildRoot+'/'+path.minifiedAdminCssFileName;
  return gulp.src(file, {read: false})
    .pipe(clean());
});

gulp.task('minify-addthis-admin-css', ['clean-minify-addthis-admin-css'], function() {
  return gulp.src(path.addThisAdminCss)
    .pipe(sourcemaps.init())
    .pipe(concat(path.minifiedAdminCssFileName))
    .pipe(cleanCss({
      relativeTo: 'src',
      target: 'build'
    }))
    .pipe(sourcemaps.write('../../' + path.buildRoot))
    .pipe(gulp.dest(path.buildRoot));
});

gulp.task('l10n-compile-mo', ['l10n-make-backend-po'], function() {
  return gulp.src(path.l10n_po)
    .pipe(foreach(function(stream, file){
      moFilename = file.relative.slice(0, -3) + '.mo';
      var po = gettextParser.po.parse(file.contents.toString());
      var mo = gettextParser.mo.compile(po);

      fs.writeFileSync(path.l10n_build + '/' + moFilename, mo);
      return stream;
    }));
});

// reorders strings in the po files to be alphabetical by msgid, and copies #,
// and #. comments from en.po into the others
gulp.task('po_cleanup', function() {
  var getAuthoritativePo = function(next) {
    return gulp.src('frontend/src/l10n/addthis-frontend-en_US.po')
    .pipe(foreach(function(stream, poFile) {
      var po = gettextParser.po.parse(poFile.contents.toString());
      return next(po);
    }));
  };

  return getAuthoritativePo(function(authPo) {
    var authComments = {};
    Object.keys(authPo.translations['']).forEach(function(msgid, key) {
      var comments = authPo.translations[''][msgid].comments;
      authComments[msgid] = comments;
    });

    return gulp.src('frontend/src/l10n/addthis-frontend-*.po')
    .pipe(foreach(function(stream, poFile) {
      var strings = gettextParser.po.parse(poFile.contents.toString());
      strings.translations[''] = sortObj(strings.translations['']);

      Object.keys(strings.translations['']).forEach(function(msgid, key) {
        if (authComments[msgid]) {
          var comments = strings.translations[''][msgid].comments;
          comments.extracted = authComments[msgid].extracted;
          comments.flag = authComments[msgid].flag;
        }
      });

      newPo = gettextParser.po.compile(strings);
      fs.writeFileSync(poFile.path, newPo);
      return stream;
    }));
  });
});

var createBackendPo = function (file, backendPoLookups) {
  var oldPo = gettextParser.po.parse(file.contents.toString());
  var newPo = gettextParser.po.parse(file.contents.toString());
  newPo.translations[''] = {'': oldPo.translations['']['']};

  Object.keys(oldPo.translations['']).forEach(function(frontendMsgid, key, _array) {
    if (typeof backendPoLookups[frontendMsgid] !== 'undefined') {
      var translation = oldPo.translations[''][frontendMsgid];
      var frontendMsgid = translation['msgid'];
      var backendMsgid = backendPoLookups[frontendMsgid]['msgid'];
      var map = backendPoLookups[frontendMsgid]['map'];

      // swap out angular variables for sprintf placeholders in comments
      if (((typeof oldPo.translations[''][frontendMsgid]['comments']) !== 'undefined')) {
        Object.keys(translation.comments).forEach(function(comment, key, _array) {
          translation.comments[comment] = angularToSprintfPlaceholders(translation.comments[comment], map);
        });
      }

      // swap out angular variables for sprintf placeholders in msgstr
      if (((typeof oldPo.translations[''][frontendMsgid]['msgstr']) !== 'undefined')) {
        Object.keys(translation.msgstr).forEach(function(element, key, _array) {
          translation.msgstr[element] = angularToSprintfPlaceholders(translation.msgstr[element], map);
        });
      }

      newPo.translations[''][backendMsgid] = translation;
      newPo.translations[''][backendMsgid]['msgid'] = backendMsgid;
    }
  });

  return newPo;
};

var angularToSprintfPlaceholders = function(msgstr, variableMapping) {
  Object.keys(variableMapping).forEach(function(placeholder, key, _array) {
    var regex = new RegExp(placeholder,'g');
    msgstr = msgstr.replace(regex, variableMapping[placeholder]);
  });

  return msgstr;
};

gulp.task('l10n-make-backend-po', function() {
  return gulp.src(path.l10n_src + 'addthis-frontend-en_US.po')
    .pipe(foreach(function(stream, file){
      var authoritativePo = gettextParser.po.parse(file.contents.toString());

      // create skeleton backend & frontend po for debug
      var debugFrontendPo = gettextParser.po.parse(file.contents.toString());

      var backendPoLookups = {};
      Object.keys(authoritativePo.translations['']).forEach(function(element, key, _array) {
        var translation = authoritativePo.translations[''][element];
        var frontendMsgid = translation['msgid'];

        debugFrontendPo.translations[''][element]['msgstr'][0] = frontendMsgid;

        var backendFlag = 'include in addthis-wordpress-plugin-backend domain';
        if (((typeof authoritativePo.translations[''][element]['comments']) !== 'undefined') &&
          ((typeof authoritativePo.translations[''][element]['comments']['flag']) !== 'undefined') &&
          (authoritativePo.translations[''][element]['comments']['flag']).indexOf(backendFlag) !== -1
        ) {
          // EEEEWWWW WordPress, why are you making me do this?
          var backendMsgid = translation['msgstr'][0];

          var i = 0;
          var map = {};
          var backendMsgid = backendMsgid.replace(/({{.+?}})/g, function(match, p1, offset, string) {
            i++;
            var replace = '%'+i+'$s';
            map[match] = replace;
            return replace;
          });

          backendPoLookups[frontendMsgid] = { 'msgid': backendMsgid, 'map': map };
        }
      });

      debugFrontendPo = gettextParser.po.compile(debugFrontendPo);
      fs.writeFileSync(path.l10n_build + '/addthis-frontend-debug.po', debugFrontendPo);

      //foreach frontend po, make a backend po
      return gulp.src(path.l10n_frontend_po)
        .pipe(foreach(function(stream, file){
          // create backend po filename
          var re = /^addthis-frontend-/;
          var backendPoFilename = file.relative.replace(re, 'addthis-backend-');
          var backendPo = createBackendPo(file, backendPoLookups);
          var backendPo = gettextParser.po.compile(backendPo);
          fs.writeFileSync(path.l10n_build + '/' + backendPoFilename, backendPo);

          return stream;
        }));
    }));
});


gulp.task('l10n-to-do-list', function() {
  return gulp.src(path.l10n_src + 'addthis-frontend-en_US.po')
    .pipe(foreach(function(stream, authoritativeFile){
      var authoritativePo = gettextParser.po.parse(authoritativeFile.contents.toString());

      return gulp.src(path.l10n_frontend_po)
        .pipe(foreach(function(stream, testFile){
        var testPo = gettextParser.po.parse(testFile.contents.toString());
        console.log('Checking ' + testFile.relative);

        var msgidsToTranslate = [];
        var wordsToTranslate = 0;
        var fullWordCount = 0;

        Object.keys(authoritativePo.translations['']).forEach(function(element, key, _array) {
          var translation = authoritativePo.translations[''][element];
          var msgid = translation['msgid'];
          var englishMsgstr = translation['msgstr'][0];


          if (typeof testPo.translations[''][element] === 'undefined') {
            msgidsToTranslate.push(msgid);
            wordsToTranslate += englishMsgstr.split(' ').length;
          } else {
            var testLanguageMsgstr = testPo.translations[''][element]['msgstr'][0];
            fullWordCount += testLanguageMsgstr.split(' ').length;
          }
        });

        if (fullWordCount > 0) {
          console.log('Current word count in file: ' + fullWordCount);
        }

        if (msgidsToTranslate.length > 0) {
          console.log('Estimated word count for phrases that need translating: ' + wordsToTranslate);
          console.log('The following phrases are missing from this file: ' + msgidsToTranslate.join(', '));
        } else {
          console.log('No missing phrases');
        }

        console.log();

        return stream;
      }));
    }));
});

gulp.task('l10n-make-frontend-json', function() {
  return gulp.src(path.l10n_frontend_po)
    .pipe(foreach(function(stream, file){
      var jsonFilename = file.relative.slice(0, -3) + '.json';

      var options = {
        'format': 'mf',
        'stringify': true,
        'pretty': true
      };

      var jsonTranslation = po2json.parse(file.contents.toString(), options);
      fs.writeFileSync(path.l10n_build + '/'+ jsonFilename, jsonTranslation);
    return stream;
  }));
});

gulp.task('l10n-check', function() {
  return gulp.src(path.l10n_src + '*.pot')
    .pipe(foreach(function(stream, file){
      var translationDomain =file.relative.slice(0, -4);
      console.log('starting check for text domain ' + translationDomain + '...');
      var pot = gettextParser.po.parse(file.contents.toString());

      return gulp.src(path.l10n_src + translationDomain + '-*.po')
        .pipe(foreach(function(stream, file){
          console.log('checking ' + file.relative + '...');
          var po = gettextParser.po.parse(file.contents.toString());
          Object.keys(pot.translations['']).forEach(function(element, key, _array) {
            if (typeof po.translations[''][element] === 'undefined') {
              console.log(file.relative + ' does not define msgid "' + element + '"');
            } else if (po.translations[''][element]['msgstr'].length === 0) {
              console.log(file.relative + ' lists but does not define msgid "' + element + '"');
            } else if (po.translations[''][element]['msgstr'].length === 1 && po.translations[''][element]['msgstr'][0] === '') {
              console.log(file.relative + ' includes but does not defined msgid "' + element + '"');
            }
          });

          return stream;
        }));
    }));
});

gulp.task('concat-templates', function () {
  return gulp.src(path.addThisTemplates)
    .pipe(htmlclean())
    .pipe(templateCache({
      root: '/',
      module: 'appAddThisWordPress'
    }))
    .pipe(gulp.dest(path.buildRoot));
});

gulp.task('make-folders', function () {
  var folders = [path.buildRoot, path.l10n_build];
  folders.forEach(function(folder) {
    try {
        fs.mkdirSync(folder);
    }
    catch(err) {
        if (err.code !== 'EEXIST') {
            console.warn(err);
        }
    }
  });
});

gulp.task('lint-js', function() {
  return gulp.src(path.lintJs)
    .pipe(jshint())
    .pipe(jshint.reporter('jshint-stylish'))
    .pipe(jshint.reporter('fail'));
});

gulp.task('build', ['lint-js', 'make-folders'], function(){
  return gulp.start(
    'minify-addthis-js',
    'minify-admin-js',
    'minify-addthis-public-css',
    'minify-addthis-admin-css',
    'minify-vendor-js',
    'minify-vendor-css',
    'l10n-compile-mo',
    'l10n-make-frontend-json'
  );
});

gulp.task('watch', ['build'], function() {
  gulp.watch(path.addThisJs, ['minify-addthis-js']);
  gulp.watch(path.adminJs, ['minify-admin-js']);
  gulp.watch(path.addThisPublicCss, ['minify-addthis-public-css']);
  gulp.watch(path.addThisAdminCss, ['minify-addthis-admin-css']);
  gulp.watch(path.vendorJs, ['minify-vendor-js']);
  gulp.watch(path.vendorCss, ['minify-vendor-css']);
  gulp.watch(path.addThisTemplates, ['concat-templates']);
  gulp.watch(path.l10n_src + '*.po', ['l10n-compile-mo', 'l10n-make-frontend-json']);
});

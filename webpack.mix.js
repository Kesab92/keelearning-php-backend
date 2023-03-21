const mix = require('laravel-mix')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/vue/app.js', 'public/js/vue-app.js')
  .vue()
  .disableSuccessNotifications()
  .extract([
    'axios',
    'date-fns/format',
    'date-fns/parse',
    'vue',
    'vuetify',
  ])
  .version()

// LEGACY CODE

mix
  .sass('resources/assets/sass/app.scss', 'public/css/app.css', {
    sassOptions: {
      outputStyle: 'compressed',
    },
  })
  .sass('resources/assets/sass/login.scss', 'public/css/login.css', {
    sassOptions: {
      outputStyle: 'compressed',
    },
  })
mix.copy('resources/assets/js/access-logs.js', 'public/js/access-logs.js')
mix.copy('resources/assets/js/competitions.js', 'public/js/competitions.js')
mix.copy('resources/assets/js/faq.js', 'public/js/faq.js')
mix.copy('resources/assets/js/global.js', 'public/js/global.js')
mix.copy('resources/assets/js/login.js', 'public/js/login.js')
mix.copy('resources/assets/js/mails.js', 'public/js/mails.js')
mix.copy('resources/assets/js/tags.js', 'public/js/tags.js')
mix.copy('resources/assets/js/tests.js', 'public/js/tests.js')

mix.copy('node_modules/blueimp-file-upload/js/jquery.fileupload.js', 'public/js/vendor/jquery.fileupload.js')
mix.copy('node_modules/blueimp-file-upload/js/jquery.iframe-transport.js', 'public/js/vendor/jquery.iframe-transport.js')
mix.copy('node_modules/blueimp-file-upload/js/vendor/jquery.ui.widget.js', 'public/js/vendor/jquery.ui.widget.js')
mix.copy('node_modules/handlebars/dist/handlebars.min.js', 'public/js/vendor/handlebars.min.js')
mix.copy('node_modules/interactjs/dist/interact.min.js', 'public/js/vendor/interact.min.js')
mix.copy('node_modules/medium-editor-insert-plugin/dist/js/medium-editor-insert-plugin.min.js', 'public/js/vendor/medium-editor-insert-plugin.min.js')
mix.copy('node_modules/medium-editor/dist/js/medium-editor.min.js', 'public/js/vendor/medium-editor.min.js')

mix.copy('node_modules/medium-editor-insert-plugin/dist/css/medium-editor-insert-plugin.min.css', 'public/css/vendor/medium-editor-insert-plugin.min.css')
mix.copy('node_modules/semantic-ui-calendar/dist/calendar.css', 'public/css/vendor/calendar.css')
mix.copy('resources/assets/css/vendor/dropzone.css', 'public/css/vendor/dropzone.css')
mix.copy('resources/assets/css/vendor/ink.css', 'public/css/vendor/ink.css')
mix.copy('resources/assets/css/vendor/spectrum.css', 'public/css/vendor/spectrum.css')

mix.scripts([
  "resources/assets/js/vendor/jquery.address.js",
  "resources/assets/js/tablesort.js",
  "resources/assets/js/stats.js"
], 'public/js/stats.js')

mix.scripts([
  "resources/assets/js/vendor/Chart-v2.min.js",
  "resources/assets/js/dashboard.js"
], 'public/js/dashboard.js')

mix.scripts([
  "resources/assets/js/vendor/dropzone.js",
  "resources/assets/js/indexcards.js"
], 'public/js/indexcards.js')

mix.scripts([
  "resources/assets/js/vendor/URI.min.js",
  "resources/assets/js/global.js"
], 'public/js/global.js')

mix.scripts([
  "node_modules/semantic-ui-calendar/dist/calendar.js",
  "resources/assets/js/competitions.js"
], 'public/js/competitions.js')

mix.version([
  'css/app.css',
  'css/login.css',
  'css/vendor/dropzone.css',
  'css/vendor/spectrum.css',
  'js/competitions.js',
  'js/dashboard.js',
  'js/faq.js',
  'js/global.js',
  'js/indexcards.js',
  'js/login.js',
  'js/stats.js',
  'js/tags.js',
  'js/tests.js',
])

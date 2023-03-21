require('./bootstrap')

import Vue from 'vue'
import Vuetify from 'vuetify'

window.Vue = Vue

window.Vue.use(Vuetify)

import VueRouter from 'vue-router'

import 'tinymce/tinymce'

// A theme is also required
import 'tinymce/themes/silver'

// Any plugins you want to use has to be imported
import 'tinymce/plugins/image'
import 'tinymce/plugins/media'
import 'tinymce/plugins/textcolor'
import 'tinymce/plugins/link'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/paste'
import 'tinymce/plugins/table'

import { sync } from 'vuex-router-sync'

import constants from './logic/constants'
import routes from './routes'
import rules from './logic/rules'
import store from './store'

// We need to make sure to only activate the vue router on pages where we actually use it, otherwise it clashes with our custom location hash usage
function hasVueRouter() {
  let routesWithRouting = [
    '/courses',
    '/course-statistics/',
    '/appswitcher',
    '/learningmaterials',
    '/advertisements',
    '/users',
    '/news',
    '/tests',
    '/keywords',
    '/stats/users',
    '/pages',
    '/settings',
    '/comments',
    '/tags',
    '/vouchers',
    '/suggested-questions',
    '/stats/quiz/reporting',
    '/stats/quiz',
    '/questions',
    '/quiz-teams',
    '/reports',
    '/appointments',
    '/forms',
    '/superadmin/user-activity',
  ]
  for(let i = 0;i < routesWithRouting.length;i++) {
    if(window.location.pathname.indexOf(routesWithRouting[i]) === 0) {
      return true
    }
  }
  return false
}
let router = null

if(hasVueRouter()) {
  router = new VueRouter({
    routes,
  })
  Vue.use(VueRouter)

  router.beforeEach((to, from, next) => {
    next()
    if (window !== window.parent) {
      window.parent.postMessage({
        type: 'keelearning-iframe-navigation',
        path: to.path,
        appId: window.VUEX_STATE.appId,
      }, '*')
    }
  })

  sync(store, router)
}

Vue.component('azure-video-progress', require('./components/partials/AzureVideoProgress.vue').default)
Vue.component('capped-input', require('./components/CappedInput.vue').default)
Vue.component('categories', require('./components/Categories.vue').default)
Vue.component('test-certificate', require('./components/TestCertificate.vue').default)
Vue.component('confirm-modal', require('./components/ConfirmModal').default)
Vue.component('delete-users', require('./components/partials/imports/DeleteUsers.vue').default)
Vue.component('faq', require('./components/Faq.vue').default)
Vue.component('help-result-entry', require('./components/HelpResultEntry.vue').default)
Vue.component('help-result-modal', require('./components/HelpResultModal.vue').default)
Vue.component('help', require('./components/Help.vue').default)
Vue.component('image-cropper', require('./components/ImageCropper.vue').default)
Vue.component('import-cards', require('./components/partials/imports/ImportCards.vue').default)
Vue.component('import-questions', require('./components/partials/imports/ImportQuestions.vue').default)
Vue.component('import-users', require('./components/partials/imports/ImportUsers.vue').default)
Vue.component('import', require('./components/Import.vue').default)
Vue.component('jobs', require('./components/Jobs.vue').default)
Vue.component('knowledge-base', require('./components/KnowledgeBase.vue').default)
Vue.component('knowledge-submenu', require('./components/partials/knowledge-modal/Knowledge-Submenu.vue').default)
Vue.component('page-editor', require('./components/PageEditor.vue').default)
Vue.component('page-modal', require('./components/partials/knowledge-modal/PageModal.vue').default)
Vue.component('ratings', require('./components/ratings/Ratings').default)
Vue.component('reminders', require('./components/tests/Reminders').default)
Vue.component('stats-wbt', require('./components/StatsWbt').default)
Vue.component('tag-group-management', require('./components/tag-groups/TagGroupManagement').default)
Vue.component('tag-group-modal', require('./components/tag-groups/modals/TagGroupModal').default)
Vue.component('test-editor', require('./components/TestEditor').default)
Vue.component('test-result-history-entry', require('./components/tests/results/TestResultHistoryEntry').default)
Vue.component('test-results', require('./components/tests/results/TestResults').default)
Vue.component('test-results', require('./components/tests/results/TestResults').default)
Vue.component('users', require('./components/users/Index').default)
Vue.component('vuetify-wrapper', require('./components/VuetifyWrapper.vue').default)
Vue.component('webinars', require('./components/Webinars').default)
Vue.component('course', require('./components/courses/views/Course.vue').default)
Vue.component('course-statistics', require('./components/CourseStatistics.vue').default)

Vue.component('warnings', require('./components/partials/global/Warnings.vue').default)
Vue.component('routed', require('./components/Routed.vue').default)
Vue.component('details-sidebar', require('./components/partials/global/DetailsSidebar.vue').default)
Vue.component('details-sidebar-toolbar', require('./components/partials/global/DetailsSidebarToolbar.vue').default)
Vue.component('translated-input', require('./components/partials/global/TranslatedInput.vue').default)
Vue.component('Toggle', require('./components/partials/global/Toggle.vue').default)
Vue.component('ContentCategorySelect', require('./components/partials/content-categories/ContentCategorySelect.vue').default)
Vue.component('ContentCategoryList', require('./components/partials/content-categories/ContentCategoryList.vue').default)
Vue.component('Dashboard', require('./components/dashboard/Dashboard.vue').default)

require('./logic/filters')

Vue.mixin({
  computed: {
    '$constants': () => {
      return constants
    },
    '$rules': () => {
      return rules
    },
  },
})

if (document.getElementById('app')) {
  const app = new Vue({
    mode: 'history',
    router,
    store,
  }).$mount('#app')

  axios.interceptors.response.use(response => response, (error) => {
    // CSRF token expired
    if (error.response && error.response.status == 419) {
      alert('Ihr Session-Token ist abgelaufen, bitte laden Sie die Seite neu!')
    }
    return Promise.reject(error)
  });

  // TODO: replace this with a proper init file once we have more than one method to call here
  store.dispatch('app/updateAppConfig')
}

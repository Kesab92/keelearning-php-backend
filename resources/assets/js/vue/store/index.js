import Vue from 'vue'
import Vuex from 'vuex'
import languages from './languages'
import learningmaterials from './learningmaterials'
import advertisements from './advertisements'
import users from './users'
import tags from './tags'
import app from './app'
import contentCategories from './contentCategories'
import news from './news'
import tests from './tests'
import keywords from './keywords'
import pages from './pages'
import comments  from './comments'
import vouchers from './vouchers'
import suggestedQuestions from './suggestedQuestions'
import reportings from './reportings'
import categories from './categories'
import userRoles from './userRoles'
import courses from './courses'
import snackbar from './snackbar'
import stats from './stats'
import templateInheritance from './templateInheritance'
import quizTeams from './quizTeams'
import questions from './questions'
import appointments from './appointments'
import forms from "./forms"

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    advertisements,
    languages,
    learningmaterials,
    users,
    tags,
    app,
    contentCategories,
    news,
    tests,
    keywords,
    pages,
    comments,
    vouchers,
    suggestedQuestions,
    reportings,
    categories,
    userRoles,
    courses,
    snackbar,
    stats,
    templateInheritance,
    quizTeams,
    questions,
    appointments,
    forms,
  },
})

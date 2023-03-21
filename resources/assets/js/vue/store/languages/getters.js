export default {
  languages(state) {
    return state.languages
  },
  defaultLanguage(state) {
    return state.languages[0]
  },
  activeLanguage(state) {
    return state.activeLanguage
  },
  isPrimaryLanguage(state) {
    return state.languages[0] === state.activeLanguage
  },
}

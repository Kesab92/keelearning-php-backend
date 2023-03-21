const defaultState = {
  activeLanguage: null,
  languages: [],
}

if(typeof window.VUEX_STATE !== 'undefined' && typeof window.VUEX_STATE.languages !== 'undefined') {
  defaultState.activeLanguage = window.VUEX_STATE.languages.activeLanguage
  defaultState.languages = window.VUEX_STATE.languages.languages
}

export default defaultState

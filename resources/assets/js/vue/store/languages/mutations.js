import Vue from 'vue'

export default {
  setConfig(state, config) {
    state.languages = config.languages
    state.activeLanguage = config.activeLanguage
  },
}

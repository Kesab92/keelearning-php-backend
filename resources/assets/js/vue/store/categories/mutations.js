import Vue from 'vue'

export default {
  setCategories(state, categories) {
    Vue.set(state, 'categories', categories)
  },
}

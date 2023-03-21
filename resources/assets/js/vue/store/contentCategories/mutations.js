import Vue from 'vue'

export default {
  setCategories(state, {categories, type}) {
    Vue.set(state.categories, type, categories)
  },
  setCategory(state, category) {
    Vue.set(state.categoryDetails, category.id, category)
  },
  setListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setSearch(state, search) {
    state.search = search
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
}

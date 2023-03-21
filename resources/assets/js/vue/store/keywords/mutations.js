import Vue from 'vue'

export default {
  setKeyword(state, keyword) {
    Vue.set(state.keywordDetails, keyword.id, keyword)
  },
  deleteKeyword(state, id) {
    Vue.delete(state.keywordDetails, id)
  },
  setKeywordsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setKeywordsCount(state, count) {
    state.keywordsCount = count
  },
  setKeywords(state, keywords) {
    state.keywords = keywords
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setSearch(state, search) {
    state.search = search
  },
  setCategories(state, categories) {
    state.categories = categories
  },
}

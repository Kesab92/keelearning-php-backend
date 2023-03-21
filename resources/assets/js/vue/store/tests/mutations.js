import Vue from 'vue'

export default {
  setTestsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setTestsCount(state, count) {
    state.testsCount = count
  },
  setTests(state, tests) {
    state.tests = tests
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setSearch(state, search) {
    state.search = search
  },
  setFilter(state, filter) {
    state.filter = filter
  },
  setTags(state, tags) {
    state.tags = tags
  }
  ,
}

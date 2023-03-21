import Vue from 'vue'

export default {
  setCommentsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setCommentsListForEntryLoading(state, isLoading) {
    state.listForEntryIsLoading = isLoading
  },
  setCommentsCount(state, count) {
    state.commentsCount = count
  },
  setComments(state, comments) {
    state.comments = comments
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setSearch(state, search) {
    state.search = search
  },
  setFilters(state, filters) {
    state.filters = filters
  },
  setTags(state, tags) {
    state.tags = tags
  },
  setCommentsForEntry(state, commentsForEntry) {
    state.commentsForEntry = commentsForEntry
  },
}

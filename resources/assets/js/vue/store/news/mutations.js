import Vue from 'vue'

export default {
  setNewsEntry(state, newsEntry) {
    Vue.set(state.newsDetails, newsEntry.id, newsEntry)
  },
  deleteNewsEntry(state, id) {
    Vue.delete(state.newsDetails, id)
  },
  setNewsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setNewsCount(state, count) {
    state.newsCount = count
  },
  setNews(state, news) {
    state.news = news
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
  },
}

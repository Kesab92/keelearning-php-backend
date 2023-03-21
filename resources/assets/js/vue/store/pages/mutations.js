import Vue from 'vue'

export default {
  setPage(state, page) {
    Vue.set(state.pageDetails, page.id, page)
  },
  deletePage(state, id) {
    Vue.delete(state.pageDetails, id)
  },
  setPagesListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setPagesCount(state, count) {
    state.pagesCount = count
  },
  setPages(state, pages) {
    state.pages = pages
  },
  setSearch(state, search) {
    state.search = search
  },
  setTags(state, tags) {
    state.tags = tags
  },
  setMainPages(state, mainPages) {
    Vue.set(state, 'mainPages', mainPages)
  },
  setSubPages(state, subPages) {
    Vue.set(state, 'subPages', subPages)
  },
}

import Vue from 'vue'

export default {
  setTag(state, tag) {
    Vue.set(state.tagDetails, tag.id, tag)
  },
  setTags(state, tags) {
    Vue.set(state, 'tags', tags)
  },
  setTagsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setTagsList(state, tags) {
    Vue.set(state, 'tagsList', tags)
  },
  setTagsCount(state, count) {
    state.tagsCount = count
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
  setContentCategories(state, contentcategories) {
    state.contentcategories = contentcategories
  },
}

import Vue from 'vue'

export default {
  setAdvertisement(state, advertisement) {
    Vue.set(state.advertisementDetails, advertisement.id, advertisement)
  },
  deleteAdvertisement(state, id) {
    Vue.delete(state.advertisementDetails, id)
  },
  setAdvertisementsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setAdvertisementsCount(state, count) {
    state.advertisementsCount = count
  },
  setAdvertisements(state, advertisements) {
    state.advertisements = advertisements
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setSearch(state, search) {
    state.search = search
  },
  setTags(state, tags) {
    state.tags = tags
  },
}

import Vue from 'vue'

export default {
  setVoucher(state, voucher) {
    Vue.set(state.voucherDetails, voucher.id, voucher)
  },
  deleteVoucher(state, id) {
    Vue.delete(state.voucherDetails, id)
  },
  setVouchersListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setVouchersCount(state, count) {
    state.vouchersCount = count
  },
  setVouchers(state, vouchers) {
    state.vouchers = vouchers
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
  setTagsWithoutGroup(state, tagsWithoutGroup) {
    state.tagsWithoutGroup = tagsWithoutGroup
  },
  setTagGroups(state, tagGroups) {
    state.tagGroups = tagGroups
  },
  setTagsRequired(state, tagsRequired) {
    state.tagsRequired = tagsRequired
  },
}

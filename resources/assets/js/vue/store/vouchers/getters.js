export default {
  vouchers: (state) => state.vouchers,
  voucher(state) {
    return (id) => state.voucherDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  vouchersCount: (state) => state.vouchersCount,
  tagGroups: (state) => state.tagGroups,
  tagsWithoutGroup: (state) => state.tagsWithoutGroup,
  tagsRequired: (state) => state.tagsRequired,
}

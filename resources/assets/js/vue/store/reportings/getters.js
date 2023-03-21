export default {
  reportings: (state) => state.reportings,
  reportingCount: (state) => state.reportingCount,
  reporting(state) {
    return (id) => state.reportingDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
}

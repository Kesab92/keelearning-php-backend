export default {
  advertisement(state) {
    return (id) => state.advertisementDetails[id]
  },
  advertisementsCount: (state) => state.advertisementsCount,
  advertisements: (state) => state.advertisements,
  listIsLoading: (state) => state.listIsLoading,
}

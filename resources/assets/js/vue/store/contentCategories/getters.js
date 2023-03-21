export default {
  getCategories(state) {
    return (type) => state.categories[type]
  },
  category(state) {
    return (id) => state.categoryDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
}

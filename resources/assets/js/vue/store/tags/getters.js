export default {
  tags(state) {
    return state.tags
  },
  tagsList: (state) => state.tagsList,
  tag(state) {
    return (id) => state.tagDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  tagsCount: (state) => state.tagsCount,
}

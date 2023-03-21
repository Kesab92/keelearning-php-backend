export default {
  keywords: (state) => state.keywords,
  keyword(state) {
    return (id) => state.keywordDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  keywordsCount: (state) => state.keywordsCount,
}

export default {
  news: (state) => state.news,
  newsEntry(state) {
    return (id) => state.newsDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  newsCount: (state) => state.newsCount,
}

export default {
  pages: (state) => state.pages,
  page(state) {
    return (id) => state.pageDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  pagesCount: (state) => state.pagesCount,
  mainPages: (state) => state.mainPages,
  subPages: (state) => state.subPages,
}

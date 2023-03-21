const defaultState = {
  keywordDetails: {},
  listIsLoading: false,
  search: null,
  categories: [],
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
    totalItems: 7,
  },
  keywords: [],
  keywordsCount: 0,
}

export default defaultState

const defaultState = {
  advertisementDetails: {},
  listIsLoading: false,
  search: null,
  tags: [],
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
  },
  advertisements: [],
  advertisementsCount: 0,
}

export default defaultState

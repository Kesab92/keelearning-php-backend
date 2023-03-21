import constants from "../../logic/constants"

const defaultState = {
  newsDetails: {},
  listIsLoading: false,
  tags: [],
  search: null,
  filter: constants.NEWS.FILTER_ACTIVE,
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
    totalItems: 7,
  },
  news: [],
  newsCount: 0,
}

export default defaultState

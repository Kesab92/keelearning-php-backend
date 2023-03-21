import constants from "../../logic/constants"

const defaultState = {
  listIsLoading: false,
  tags: [],
  search: null,
  filter: constants.TEST.FILTER_VISIBLE,
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
    totalItems: 7,
  },
  tests: [],
  testsCount: 0,
}

export default defaultState

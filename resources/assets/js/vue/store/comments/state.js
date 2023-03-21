import constants from "../../logic/constants";

const defaultState = {
  listIsLoading: false,
  listForEntryIsLoading: false,
  tags: [],
  search: null,
  filters: [],
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
    totalItems: 7,
  },
  comments: [],
  commentsCount: 0,
  commentsForEntry: [],
}

export default defaultState

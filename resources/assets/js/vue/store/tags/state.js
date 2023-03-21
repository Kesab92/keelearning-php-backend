import constants from "../../logic/constants";

const defaultState = {
  tags: [],
  tagsList: [],
  tagDetails: {},
  listIsLoading: false,
  search: null,
  filter: constants.TAGS.FILTERS.FILTER_ALL.value,
  contentcategories: [],
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
  },
  tagsCount: 0,
}

export default defaultState

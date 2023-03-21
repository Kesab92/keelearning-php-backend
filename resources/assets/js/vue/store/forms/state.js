import constants from "../../logic/constants"

const defaultState = {
  formDetails: {},
  tags: [],
  categories: [],
  filter: constants.FORMS.FILTERS.ACTIVE,
  search: null,
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
  },
  listIsLoading: false,
  allForms: [],
  forms: [],
  formCount: 0,
  isSaving: false,
}

export default defaultState

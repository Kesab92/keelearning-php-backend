const defaultState = {
  questionDetails: {},
  listIsLoading: false,
  query: null,
  category: null,
  filters: ['visibility_1'],
  pagination: {
    sortBy: 'updated_at',
    descending: true,
    page: 1,
    rowsPerPage: 50,
    totalItems: 7,
  },
  questions: [],
  questionCount: 0,
  missingTranslations: {},
}

export default defaultState

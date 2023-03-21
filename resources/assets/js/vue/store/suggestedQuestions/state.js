const defaultState = {
  suggestedQuestionDetails: {},
  listIsLoading: false,
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
  },
  suggestedQuestions: [],
  suggestedQuestionsCount: 0,
}

export default defaultState

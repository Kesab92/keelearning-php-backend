export default {
  suggestedQuestions: (state) => state.suggestedQuestions,
  suggestedQuestion(state) {
    return (id) => state.suggestedQuestionDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  suggestedQuestionsCount: (state) => state.suggestedQuestionsCount,
}

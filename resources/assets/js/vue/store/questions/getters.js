export default {
  questions: (state) => state.questions,
  question(state) {
    return (id) => state.questionDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  questionCount: (state) => state.questionCount,
  missingTranslations: (state) => state.missingTranslations,
}

import Vue from 'vue'

export default {
  setQuestion(state, question) {
    Vue.set(state.questionDetails, question.id, question)
  },
  deleteQuestion(state, id) {
    Vue.delete(state.questionDetails, id)
  },
  setQuestionsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setQuestionCount(state, count) {
    state.questionCount = count
  },
  setQuestions(state, questions) {
    state.questions = questions
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setQuery(state, query) {
    state.query = query
  },
  setCategory(state, category) {
    state.category = category
  },
  setFilters(state, filters) {
    Vue.set(state, 'filters', filters)
  },
  setMissingTranslations(state, missingTranslations) {
    Vue.set(state, 'missingTranslations', missingTranslations)
  },
}

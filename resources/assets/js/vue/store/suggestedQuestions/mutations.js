import Vue from 'vue'

export default {
  setSuggestedQuestion(state,  suggestedQuestion) {
    Vue.set(state. suggestedQuestionDetails,  suggestedQuestion.id,  suggestedQuestion)
  },
  deleteSuggestedQuestion(state, id) {
    Vue.delete(state. suggestedQuestionDetails, id)
  },
  setSuggestedQuestionsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setSuggestedQuestionsCount(state, count) {
    state. suggestedQuestionsCount = count
  },
  setSuggestedQuestions(state,  suggestedQuestions) {
    state. suggestedQuestions =  suggestedQuestions
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
}

let loadSuggestedQuestionCancel = null

export default {
  loadSuggestedQuestions({state, commit}) {
    if (loadSuggestedQuestionCancel) {
      loadSuggestedQuestionCancel()
    }
    commit('setSuggestedQuestionsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadSuggestedQuestionCancel = c
    })
    axios.get("/backend/api/v1/suggested-questions", {
      cancelToken,
      params: {
        ...state.pagination,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setSuggestedQuestionsCount', response.data.count)
      commit('setSuggestedQuestions', response.data.suggestedQuestions)
      commit('setSuggestedQuestionsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadSuggestedQuestion({state, commit}, {suggestedQuestionId}) {
    return axios.get('/backend/api/v1/suggested-questions/' + suggestedQuestionId).then((response) => {
      const suggestedQuestion = response.data.suggestedQuestion
      commit('setSuggestedQuestion', suggestedQuestion)
      return state.suggestedQuestionDetails[suggestedQuestion.id]
    })
  },
  acceptSuggestedQuestion({state, commit, dispatch}, data) {
    return axios.get(`/backend/api/v1/suggested-questions/${data.id}/accept`)
  },
}

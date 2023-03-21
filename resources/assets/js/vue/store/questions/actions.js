let loadQuestionCancel = null

export default {
  loadQuestions({state, commit}) {
    if (loadQuestionCancel) {
      loadQuestionCancel()
    }
    commit('setQuestionsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadQuestionCancel = c
    })
    axios.get('/backend/api/v1/questions/list', {
      cancelToken,
      params: {
        ...state.pagination,
        selectedFilters: state.filters,
        query: state.query,
        category: state.category,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setQuestionCount', response.data.count)
      commit('setQuestions', response.data.questions)
      commit('setMissingTranslations', response.data.missingTranslations)
      commit('setQuestionsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadQuestion({state, commit}, {questionId}) {
    return axios.get('/backend/api/v1/questions/' + questionId).then((response) => {
      const question = response.data.question
      commit('setQuestion', question)
      return state.questionDetails[question.id]
    })
  },
  saveQuestion({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/questions/' + data.id, data).then((response) => {
      const question = response.data.question
      commit('setQuestion', question)
      return state.questionDetails[question.id]
    })
  },
  removeQuestionAttachment({ state, commit }, { questionId }) {
    return axios.post('/backend/api/v1/questions/' + questionId + '/delete-attachment').then((response) => {
      const question = response.data.question
      commit('setQuestion', question)
      return state.questionDetails[question.id]
    })
  },
}

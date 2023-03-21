let loadQuizTeamCancel = null

export default {
  loadQuizTeams({state, commit}) {
    if (loadQuizTeamCancel) {
      loadQuizTeamCancel()
    }
    commit('setQuizTeamsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadQuizTeamCancel = c
    })
    axios.get("/backend/api/v1/quiz-teams", {
      cancelToken,
      params: {
        ...state.pagination,
        search: state.search,
        categories: state.categories,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setQuizTeamCount', response.data.count)
      commit('setQuizTeams', response.data.quizTeams)
      commit('setQuizTeamsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadQuizTeam({state, commit}, {quizTeamId}) {
    return axios.get('/backend/api/v1/quiz-teams/' + quizTeamId).then((response) => {
      const quizTeam = response.data.quizTeam
      commit('setQuizTeam', quizTeam)
      return state.quizTeamDetails[quizTeam.id]
    })
  },
  saveQuizTeam({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/quiz-teams/' + data.id, data).then((response) => {
      const quizTeam = response.data.quizTeam
      commit('setQuizTeam', quizTeam)
      dispatch('loadQuizTeams')
      return state.quizTeamDetails[quizTeam.id]
    })
  },
}

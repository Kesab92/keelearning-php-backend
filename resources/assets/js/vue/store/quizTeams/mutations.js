import Vue from 'vue'

export default {
  setQuizTeam(state, quizTeam) {
    Vue.set(state.quizTeamDetails, quizTeam.id, quizTeam)
  },
  deleteQuizTeam(state, id) {
    Vue.delete(state.quizTeamDetails, id)
  },
  setQuizTeamsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setQuizTeamCount(state, count) {
    state.quizTeamCount = count
  },
  setQuizTeams(state, quizTeams) {
    state.quizTeams = quizTeams
  },
  setSearch(state, search) {
    state.search = search
  },
}

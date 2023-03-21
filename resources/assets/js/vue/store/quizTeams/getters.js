export default {
  quizTeams: (state) => state.quizTeams,
  quizTeam(state) {
    return (id) => state.quizTeamDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  quizTeamCount: (state) => state.quizTeamCount,
}

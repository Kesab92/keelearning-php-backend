export default {
  loadUser({ state, commit }, { userId }) {
    return axios.get('/backend/api/v1/users/' + userId).then((response) => {
      const user = response.data.user
      commit('setUser', user)
      commit('setAvailableMailNotifications', response.data.availableMailNotifications)
      commit('setMetaFields', response.data.metaFields)
      commit('setUserRole', response.data.userRole)
      return state.userDetails[user.id]
    })
  },
  saveUser({ state, commit, dispatch }, data) {
    return axios.post('/backend/api/v1/users/' + data.id, data).then((response) => {
      const user = response.data.user
      commit('setUser', user)
      commit('setUserRole', response.data.userRole)
      return state.userDetails[user.id]
    })
  },
  updateAdmins({ commit, getters }) {
    if(Object.keys(getters.admins).length > 0) {
      return Promise.resolve()
    } else {
      return axios.get('/backend/api/v1/users/admins').then(response => {
        commit('setAdmins', response.data.admins)
      })
    }
  },
}

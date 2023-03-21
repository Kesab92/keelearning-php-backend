let loadUserRolesCancel = null
let loadUserRoleCancel = null

export default {
  loadUserRoles({ commit }) {
    if (loadUserRolesCancel) {
      loadUserRolesCancel()
    }
    commit('setIsLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadUserRolesCancel = c
    })
    return axios.get('/backend/api/v1/user-roles', { cancelToken }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setUserRoles', response.data.userRoles)
      commit('setIsLoading', false)
    })
  },
  loadUserRole({ commit }, { userRoleId }) {
    if (loadUserRoleCancel) {
      loadUserRoleCancel()
    }
    let cancelToken = new axios.CancelToken(c => {
      loadUserRoleCancel = c
    })
    return axios.get(`/backend/api/v1/user-roles/${userRoleId}`, { cancelToken }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setUserRole', response.data.userRole)
    })
  },
  saveUserRole({ commit, dispatch, state }, data) {
    return axios.post(`/backend/api/v1/user-roles/${data.id}`, data).then((response) => {
      commit('setUserRole', response.data.userRole)
      dispatch('loadUserRoles')
      return state.userRoleDetails[response.data.userRole.id]
    })
  },
}

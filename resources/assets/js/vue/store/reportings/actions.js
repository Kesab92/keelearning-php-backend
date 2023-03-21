let loadReportingsCancel = null

export default {
  updateReportings({ commit, getters, state }, type) {
    if (loadReportingsCancel) {
      loadReportingsCancel()
    }
    commit('setListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadReportingsCancel = c
    })
    return axios.get('/backend/api/v1/reportings', {
      cancelToken,
      params: {
        ...state.pagination,
        type: type,
      }
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setReportings', response.data.reportings)
      commit('setListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadReporting({ state, commit }, { reportingId }) {
    return axios.get('/backend/api/v1/reportings/' + reportingId).then((response) => {
      const reporting = response.data.reporting
      commit('setReporting', reporting)
      return state.reportingDetails[reporting.id]
    })
  },
  saveReporting({ state, commit, dispatch }, data) {
    return axios.post('/backend/api/v1/reportings/' + data.id, data).then((response) => {
      const reporting = response.data.reporting
      commit('setReporting', reporting)
      dispatch('updateReportings', reporting.type)
      return state.reportingDetails[reporting.id]
    })
  },
}

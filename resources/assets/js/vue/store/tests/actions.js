let loadTestCancel = null

export default {
  loadTests({state, commit}) {
    if (loadTestCancel) {
      loadTestCancel()
    }
    commit('setTestsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadTestCancel = c
    })
    axios.get("/backend/api/v1/tests", {
      cancelToken,
      params: {
        ...state.pagination,
        filter: state.filter,
        search: state.search,
        tags: state.tags,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setTestsCount', response.data.count)
      commit('setTests', response.data.tests)
      commit('setTestsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
}

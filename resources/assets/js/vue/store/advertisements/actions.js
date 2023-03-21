var loadAdvertisementsCancel = null

export default {
  loadAdvertisements({ state, commit }) {
    if (loadAdvertisementsCancel) {
      loadAdvertisementsCancel()
    }
    commit('setAdvertisementsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadAdvertisementsCancel = c
    })
    axios.get("/backend/api/v1/advertisements", {
      cancelToken,
      params: {
        ...state.pagination,
        search: state.search,
        tags: state.tags,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setAdvertisementsCount', response.data.count)
      commit('setAdvertisements', response.data.advertisements)
      commit('setAdvertisementsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadAdvertisement({ state, commit }, { advertisementId }) {
    return axios.get('/backend/api/v1/advertisements/' + advertisementId).then((response) => {
      const advertisement = response.data.advertisement
      commit('setAdvertisement', advertisement)
      return state.advertisementDetails[advertisement.id]
    })
  },
  saveAdvertisement({ state, commit, dispatch }, data) {
    return axios.post('/backend/api/v1/advertisements/' + data.id, data).then((response) => {
      const advertisement = response.data.advertisement
      commit('setAdvertisement', advertisement)
      dispatch('loadAdvertisements')
      return state.advertisementDetails[advertisement.id]
    })
  },
}

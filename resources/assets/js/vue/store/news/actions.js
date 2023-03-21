let loadNewsCancel = null

export default {
  loadNews({state, commit}) {
    if (loadNewsCancel) {
      loadNewsCancel()
    }
    commit('setNewsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadNewsCancel = c
    })
    axios.get("/backend/api/v1/news", {
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

      commit('setNewsCount', response.data.count)
      commit('setNews', response.data.news)
      commit('setNewsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadNewsEntry({state, commit}, {newsId}) {
    return axios.get('/backend/api/v1/news/' + newsId).then((response) => {
      const newsEntry = response.data.newsEntry
      commit('setNewsEntry', newsEntry)
      return state.newsDetails[newsEntry.id]
    })
  },
  saveNewsEntry({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/news/' + data.id, data).then((response) => {
      const newsEntry = response.data.newsEntry
      commit('setNewsEntry', newsEntry)
      dispatch('loadNews')
      return state.newsDetails[newsEntry.id]
    })
  },
  notify({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/news/' + data.id + '/notify', data).then((response) => {
      dispatch('loadNewsEntry', {newsId: data.id})
      return response.data.success
    })
  },
}

let loadKeywordCancel = null

export default {
  loadKeywords({state, commit}) {
    if (loadKeywordCancel) {
      loadKeywordCancel()
    }
    commit('setKeywordsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadKeywordCancel = c
    })
    axios.get("/backend/api/v1/keywords", {
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

      commit('setKeywordsCount', response.data.count)
      commit('setKeywords', response.data.keywords)
      commit('setKeywordsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadKeyword({state, commit}, {keywordId}) {
    return axios.get('/backend/api/v1/keywords/' + keywordId).then((response) => {
      const keyword = response.data.keyword
      commit('setKeyword', keyword)
      return state.keywordDetails[keyword.id]
    })
  },
  saveKeyword({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/keywords/' + data.id, data).then((response) => {
      const keyword = response.data.keyword
      commit('setKeyword', keyword)
      dispatch('loadKeywords')
      return state.keywordDetails[keyword.id]
    })
  },
}

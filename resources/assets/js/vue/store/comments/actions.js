let loadCommentsCancel = null
let loadCommentsForEntryCancel = null

export default {
  loadComments({state, commit}) {
    if (loadCommentsCancel) {
      loadCommentsCancel()
    }
    commit('setCommentsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadCommentsCancel = c
    })
    axios.get('/backend/api/v1/comments', {
      cancelToken,
      params: {
        ...state.pagination,
        filters: state.filters,
        search: state.search,
        tags: state.tags,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setCommentsCount', response.data.count)
      commit('setComments', response.data.comments)
      commit('setCommentsListLoading', false)
    })
  },
  loadCommentsForEntry({state, commit}, {foreignType, foreignId}) {
    if (loadCommentsForEntryCancel) {
      loadCommentsForEntryCancel()
    }
    commit('setCommentsListForEntryLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadCommentsForEntryCancel = c
    })
    return axios.get(`/backend/api/v1/comments/${foreignType}/${foreignId}`, {
      cancelToken
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setCommentsForEntry', response.data.comments)
      commit('setCommentsListForEntryLoading', false)
    })
  },
  submitReply({ state, commit}, {commentId, body}) {
    return axios.post(`/backend/api/v1/comments/${commentId}/reply`, {
      body,
    })
  },
}

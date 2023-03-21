const tagCacheLifetimeSeconds = 5

let loadTagCancel = null
let lastTagUpdate = null

export default {
  updateTags({ dispatch }) {
    if (lastTagUpdate > Date.now() - tagCacheLifetimeSeconds * 1000) {
      return
    }
    lastTagUpdate = Date.now()
    dispatch('forceUpdateTags')
  },
  forceUpdateTags({ commit }) {
    return axios.get('/backend/api/v1/tags/get-tags').then(response => {
      commit('setTags', response.data.tags)
    })
  },
  loadTags({state, commit}) {
    if (loadTagCancel) {
      loadTagCancel()
    }
    commit('setTagsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadTagCancel = c
    })
    axios.get("/backend/api/v1/tags", {
      cancelToken,
      params: {
        ...state.pagination,
        search: state.search,
        filter: state.filter,
        contentcategories: state.contentcategories,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setTagsCount', response.data.count)
      commit('setTagsList', response.data.tags)
      commit('setTagsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadTag({state, commit}, {tagId}) {
    return axios.get('/backend/api/v1/tags/' + tagId).then((response) => {
      const tag = response.data.tag
      commit('setTag', tag)
      return state.tagDetails[tag.id]
    })
  },
  saveTag({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/tags/' + data.id, data).then((response) => {
      const tag = response.data.tag
      commit('setTag', tag)
      dispatch('loadTags')
      return state.tagDetails[tag.id]
    })
  },
}

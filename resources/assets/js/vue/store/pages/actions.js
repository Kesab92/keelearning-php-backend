let loadPagesCancel = null

export default {
  loadPages({state, commit}) {
    if (loadPagesCancel) {
      loadPagesCancel()
    }
    commit('setPagesListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadPagesCancel = c
    })
    axios.get("/backend/api/v1/pages", {
      cancelToken,
      params: {
        search: state.search,
        tags: state.tags,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      const pages = response.data.pages.sort((a,b) => a.parent_id - b.parent_id)

      let allPages = pages.filter(x => x.parent_id === null)
      if(allPages.length > 0) {
        allPages = allPages.map(obj=> ({ ...obj, hasSubpages:false }))
      }

      const subPages = pages.filter(x => x.parent_id !== null)

      for(const subPage of subPages){
        const index = allPages.findIndex(obj => obj.id === subPage.parent_id)
        if(index > -1) {
          allPages[index].hasSubpages = true
          allPages.splice(index+1, 0, subPage)
        } else {
          allPages.push(subPage)
        }
      }

      commit('setPagesCount', response.data.count)
      commit('setPages', allPages)
      commit('setPagesListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadPage({state, commit}, {pageId}) {
    return axios.get('/backend/api/v1/pages/' + pageId).then((response) => {
      const page = response.data.page
      commit('setPage', page)
      return state.pageDetails[page.id]
    })
  },
  updateMainPages({ commit, getters }) {
    return axios.get('/backend/api/v1/pages/main-pages').then(response => {
      commit('setMainPages', response.data.pages)
    })
  },
  updateSubPages({ commit, getters }) {
    return axios.get('/backend/api/v1/pages/sub-pages').then(response => {
      commit('setSubPages', response.data.pages)
    })
  },
  savePage({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/pages/' + data.id, data).then((response) => {
      const page = response.data.page
      commit('setPage', page)
      dispatch('loadPages')
      return state.pageDetails[page.id]
    })
  },
}

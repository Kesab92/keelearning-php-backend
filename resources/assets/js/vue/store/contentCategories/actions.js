let loadCategoriesCancel = null

export default {
  updateCategories({ commit, getters, state }, type) {
    if (loadCategoriesCancel) {
      loadCategoriesCancel()
    }
    commit('setListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadCategoriesCancel = c
    })
    return axios.get('/backend/api/v1/content-categories', {
      cancelToken,
      params: {
        type: type,
        search: state.search,
      }
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setCategories', {
        type,
        categories: response.data.categories,
      })
      commit('setListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadCategory({ state, commit }, { categoryId }) {
    return axios.get('/backend/api/v1/content-categories/' + categoryId).then((response) => {
      const category = response.data.category
      commit('setCategory', category)
      return state.categoryDetails[category.id]
    })
  },
  saveCategory({ state, commit, dispatch }, data) {
    return axios.post('/backend/api/v1/content-categories/' + data.id, data).then((response) => {
      const category = response.data.category
      commit('setCategory', category)
      dispatch('updateCategories', category.type)
      return state.categoryDetails[category.id]
    })
  },
}

export default {
  updateCategories({ commit, getters }) {
    if(getters.categories.length > 0) {
      return Promise.resolve()
    } else {
      return axios.get('/backend/api/v1/question-categories').then(response => {
        commit('setCategories', response.data.categories)
      })
    }
  },
}

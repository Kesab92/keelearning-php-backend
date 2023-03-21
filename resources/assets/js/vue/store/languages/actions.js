export default {
  updateConfig({ commit }) {
    return axios.get('/backend/api/v1/language/config').then(response => {
      commit('setConfig', response.data)
    })
  },
}

export default {
  updateAppConfig({ commit }) {
    axios.get("/backend/api/v1/app/configuration").then(response => {
      commit('setAppConfiguration', response.data)
    }).catch(e => {
      console.log(e)
    })
  },
}

let loadChildAppsCancel = null

export default {
  loadChildApps({commit}) {
    if (loadChildAppsCancel) {
      loadChildAppsCancel()
    }
    let cancelToken = new axios.CancelToken(c => {
      loadChildAppsCancel = c
    })
    axios.get("/backend/api/v1/template-inheritance/get-child-apps", {
      cancelToken,
    }).then(response => {
      commit('setChildApps', response.data.apps)
    }).catch(e => {
      console.log(e)
    })
  },
}

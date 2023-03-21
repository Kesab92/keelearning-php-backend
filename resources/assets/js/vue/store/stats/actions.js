let loadStatsCancel = {}

export default {
  loadStats({commit}, {key}) {
    if (loadStatsCancel[key]) {
      loadStatsCancel[key]()
    }
    let cancelToken = new axios.CancelToken(c => {
      loadStatsCancel[key] = c
    })
    axios.get(`/backend/api/v1/stats/${key}`, {
      cancelToken,
    }).then(response => {
      commit('setStatsData', {
        key,
        data: response.data,
      })
    }).catch(error => {
      console.error(error)
      alert('Es gab einen Fehler beim Laden der Statistik.')
    })
  },
}

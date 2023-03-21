export default {
  updateLearningmaterials({ commit }) {
    commit('setIsLoading',true)
    return axios.get('/backend/api/v1/learningmaterials').then(response => {
      commit('setFolders', response.data.folders)
      commit('setMaterials', response.data.materials)
      commit('setIsLoading',false)
    })
  },
  loadLearningmaterial({ state, commit }, { learningmaterialId }) {
    return axios.get('/backend/api/v1/learningmaterials/' + learningmaterialId).then((response) => {
      const material = response.data.material
      commit('setMaterial', material)
      return state.materialDetails[material.id]
    })
  },
  saveLearningmaterial({ state, commit }, data) {
    return axios.post('/backend/api/v1/learningmaterials/' + data.id, data).then((response) => {
      const material = response.data.material
      commit('setMaterial', material)
      return state.materialDetails[material.id]
    })
  },
  resetLearningmaterial({ state, commit }, { learningmaterialId }) {
    return axios.post('/backend/api/v1/learningmaterials/' + learningmaterialId + '/reset').then((response) => {
      const material = response.data.material
      commit('setMaterial', material)
      return state.materialDetails[material.id]
    })
  },
  createMaterial({ state, commit }, data) {
    return axios.post('/backend/api/v1/learningmaterials', data)
      .then((response) => {
        const material = response.data.material
        commit('setMaterialListEntry', material)
        commit('setMaterial', material)
        return state.materialDetails[material.id]
      })
  },
  activateNotification({ state, commit }, data) {
    return axios.post('/backend/api/v1/learningmaterials/' + data.id + '/notify', data).then((response) => {
      const material = response.data.material
      commit('setMaterial', material)
      return state.materialDetails[material.id]
    })
  },
  saveFolder({ state, commit }, data) {
    return axios.post('/backend/api/v1/learningmaterialfolders/' + data.id, data).then((response) => {
      const folder = response.data.folder
      commit('setFolder', folder)
      return folder
    })
  },
  createFolder({ state, commit }, data) {
    return axios.post('/backend/api/v1/learningmaterialfolders', data)
      .then((response) => {
        const folder = response.data.folder
        commit('setFolder', folder)
        return folder
      })
  },
}

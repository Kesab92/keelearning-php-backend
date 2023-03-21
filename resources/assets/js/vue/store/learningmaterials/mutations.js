import Vue from 'vue'

export default {
  setFolders(state, folders) {
    Vue.set(state, 'folders', folders)
  },
  setFolder(state, folder) {
    Vue.set(state.folders, folder.id, folder)
  },
  setMaterials(state, materials) {
    Vue.set(state, 'materials', materials)
  },
  setMaterial(state, material) {
    Vue.set(state.materialDetails, material.id, material)
    Vue.set(state.materials, material.id, material)
  },
  setMaterialListEntry(state, material) {
    Vue.set(state.materials, material.id, material)
  },
  deleteMaterial(state, id) {
    Vue.delete(state.materialDetails, id)
    Vue.delete(state.materials, id)
  },
  deleteFolder(state, id) {
    Vue.delete(state.folders, id)
  },
  setSearch(state, search) {
    state.search = search
  },
  setIsLoading(state, isLoading) {
    state.isLoading = isLoading
  },
}

export default {
  search(state) {
    return state.search
  },
  folders(state) {
    return state.folders
  },
  folderId(state, getters, rootState) {
    let parentId = rootState.route.params.folderId
    if(parentId) {
      return parseInt(parentId, 10)
    }
    return null
  },
  folderContents(state, getters) {
    const folders = Object.values(state.folders).filter(folder => folder.parent_id === getters.folderId)
    let materials = getters.materialsByFolder[getters.folderId]
    if(!materials) {
      materials = []
    }
    return {
      folders,
      materials,
    }
  },
  searchResult(state, getters) {
    if(!getters.search) {
      return []
    }
    const search = getters.search.toLocaleLowerCase()
    let materials = Object.values(getters.materials).filter(material => material.title.toLocaleLowerCase().search(search) > -1)
    materials.forEach(material => material.path = getters.materialPath(material.learning_material_folder_id))
    if(!materials) {
      materials = []
    }
    return materials
  },
  materialsByFolder(state) {
    return Object.values(state.materials).reduce((folders, material) => {
      if(typeof folders[material.learning_material_folder_id] === 'undefined') {
        folders[material.learning_material_folder_id] = []
      }
      folders[material.learning_material_folder_id].push(material)
      return folders
    }, {})
  },
  foldersByParent(state) {
    return Object.values(state.folders).reduce((folders, folder) => {
      if(typeof folders[folder.parent_id] === 'undefined') {
        folders[folder.parent_id] = []
      }
      folders[folder.parent_id].push(folder)
      return folders
    }, {})
  },
  path(state, getters) {
    let currentFolder = getters.folderId
    const path = []
    while(currentFolder) {
      let folder = state.folders[currentFolder]
      if(folder) {
        path.push(folder)
        currentFolder = folder.parent_id
      } else {
        currentFolder = null
      }
    }
    return path.reverse()
  },
  material(state) {
    return (id) => state.materialDetails[id]
  },
  materials(state) {
    return state.materials
  },
  folder(state) {
    return (id) => {
      id = parseInt(id, 10)
      return state.folders[id]
    }
  },
  materialPath(state) {
    return (folderId) => {
      let currentFolderId = folderId
      const path = []
      while(currentFolderId) {
        let folder = state.folders[currentFolderId]
        if(folder) {
          path.push(folder.name)
          currentFolderId = folder.parent_id
        } else {
          currentFolderId = null
        }
      }
      return path.reverse().join('/')+'/'
    }
  },
  isLoading(state) {
    return state.isLoading
  },
}

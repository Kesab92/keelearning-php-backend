let loadFormCancel = null

export default {
  loadAllForms({commit}) {
    axios.get("/backend/api/v1/forms/all").then(response => {
      commit('setAllForms', response.data.forms)
    }).catch(e => {
      console.log(e)
    })
  },
  loadForms({state, commit}) {
    if (loadFormCancel) {
      loadFormCancel()
    }
    commit('setFormsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadFormCancel = c
    })
    axios.get("/backend/api/v1/forms", {
      cancelToken,
      params: {
        ...state.pagination,
        search: state.search,
        filter: state.filter,
        tags: state.tags,
        categories: state.categories,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setFormCount', response.data.count)
      commit('setForms', response.data.forms)
      commit('setFormsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadForm({state, commit}, {formId}) {
    return axios.get('/backend/api/v1/forms/' + formId).then((response) => {
      const form = response.data.form
      commit('setForm', form)
      return state.formDetails[form.id]
    })
  },
  saveForm({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/forms/' + data.id, data).then((response) => {
      const form = response.data.form
      commit('setForm', form)
      dispatch('loadForms')
      return state.formDetails[form.id]
    })
  },
  convertFormToDraft({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/forms/${data.id}/convert-to-draft`, data).then((response) => {
      const form = response.data.form
      commit('setForm', form)
      dispatch('loadForms')
      return state.formDetails[form.id]
    })
  },
  archiveForm({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/forms/${data.id}/archive`).then((response) => {
      const form = response.data.form
      commit('setForm', form)
      dispatch('loadForms')
      return state.formDetails[form.id]
    })
  },
  unarchiveForm({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/forms/${data.id}/unarchive`).then((response) => {
      const form = response.data.form
      commit('setForm', form)
      dispatch('loadForms')
      return state.formDetails[form.id]
    })
  },
}

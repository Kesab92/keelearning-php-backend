import Vue from 'vue'

export default {
  deleteForm(state, id) {
    Vue.delete(state.formDetails, id)
  },
  setAllForms(state, allForms) {
    state.allForms = allForms
  },
  setCategories(state, categories) {
    state.categories = categories
  },
  setForm(state, form) {
    Vue.set(state.formDetails, form.id, form)
  },
  setFormCount(state, count) {
    state.formCount = count
  },
  setForms(state, forms) {
    state.forms = forms
  },
  setFormsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setFilter(state, filter) {
    state.filter = filter
  },
  setIsSaving(state, isSaving) {
    state.isSaving = isSaving
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setSearch(state, search) {
    state.search = search
  },
  setTags(state, tags) {
    state.tags = tags
  },
}

export default {
  allForms: (state) => state.allForms,
  forms: (state) => state.forms,
  form(state) {
    return (id) => state.formDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  formCount: (state) => state.formCount,
}

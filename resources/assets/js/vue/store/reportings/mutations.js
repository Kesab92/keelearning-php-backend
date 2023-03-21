import Vue from 'vue'

export default {
  setReportings(state, reportings) {
    state.reportings = reportings
  },
  setReporting(state, reporting) {
    Vue.set(state.reportingDetails, reporting.id, reporting)
  },
  setListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setReportingCount(state, count) {
    state.reportingCount = count
  },
}

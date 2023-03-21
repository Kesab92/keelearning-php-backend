import Vue from 'vue'

export default {
  setAppointment(state, appointment) {
    Vue.set(state.appointmentDetails, appointment.id, appointment)
  },
  deleteAppointment(state, id) {
    Vue.delete(state.appointmentDetails, id)
  },
  setAppointmentsListLoading(state, isLoading) {
    state.listIsLoading = isLoading
  },
  setAppointmentCount(state, count) {
    state.appointmentCount = count
  },
  setAllAppointments(state, allAppointments) {
    state.allAppointments = allAppointments
  },
  setAppointments(state, appointments) {
    state.appointments = appointments
  },
  setPagination(state, pagination) {
    Vue.set(state, 'pagination', pagination)
  },
  setSearch(state, search) {
    state.search = search
  },
  setFilter(state, filter) {
    state.filter = filter
  },
  setTags(state, tags) {
    state.tags = tags
  },
}

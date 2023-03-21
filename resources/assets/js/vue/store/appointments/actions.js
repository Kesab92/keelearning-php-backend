let loadAppointmentCancel = null

export default {
  loadAllAppointments({commit}) {
    axios.get("/backend/api/v1/appointments/all").then(response => {
      commit('setAllAppointments', response.data.appointments)
    }).catch(e => {
      console.log(e)
    })
  },
  loadAppointments({state, commit}) {
    if (loadAppointmentCancel) {
      loadAppointmentCancel()
    }
    commit('setAppointmentsListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadAppointmentCancel = c
    })
    axios.get("/backend/api/v1/appointments", {
      cancelToken,
      params: {
        ...state.pagination,
        search: state.search,
        filter: state.filter,
        tags: state.tags,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setAppointmentCount', response.data.count)
      commit('setAppointments', response.data.appointments)
      commit('setAppointmentsListLoading', false)
    }).catch(e => {
      console.log(e)
    })
  },
  loadAppointment({state, commit}, {appointmentId}) {
    return axios.get('/backend/api/v1/appointments/' + appointmentId).then((response) => {
      const appointment = response.data.appointment
      commit('setAppointment', appointment)
      return state.appointmentDetails[appointment.id]
    })
  },
  saveAppointment({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/appointments/' + data.id, data).then((response) => {
      const appointment = response.data.appointment
      commit('setAppointment', appointment)
      dispatch('loadAppointments')
      return state.appointmentDetails[appointment.id]
    })
  },
  convertAppointmentToDraft({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/appointments/${data.id}/convert-to-draft`, data).then((response) => {
      const appointment = response.data.appointment
      commit('setAppointment', appointment)
      dispatch('loadAppointments')
      return state.appointmentDetails[appointment.id]
    })
  },
  cancelAppointment({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/appointments/${data.id}/cancel`, data).then((response) => {
      const appointment = response.data.appointment
      commit('setAppointment', appointment)
      dispatch('loadAppointments')
      return state.appointmentDetails[appointment.id]
    })
  },
  notifyAboutAppointment({state, commit, dispatch}, data) {
    return axios.post(`/backend/api/v1/appointments/${data.id}/notify`, data).then((response) => {
      const appointment = response.data.appointment
      commit('setAppointment', appointment)
      return state.appointmentDetails[appointment.id]
    })
  },
}

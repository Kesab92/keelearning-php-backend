export default {
  allAppointments: (state) => state.allAppointments,
  appointments: (state) => state.appointments,
  appointment(state) {
    return (id) => state.appointmentDetails[id]
  },
  listIsLoading: (state) => state.listIsLoading,
  appointmentCount: (state) => state.appointmentCount,
}

import constants from "../../logic/constants"

const defaultState = {
  appointmentDetails: {},
  tags: [],
  filter: constants.APPOINTMENTS.FILTER_ACTIVE,
  search: null,
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
  },
  listIsLoading: false,
  allAppointments: [],
  appointments: [],
  appointmentCount: 0,
}

export default defaultState

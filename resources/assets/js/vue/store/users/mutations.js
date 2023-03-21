import Vue from 'vue'

export default {
  setUser(state, user) {
    Vue.set(state.userDetails, user.id, user)
  },
  setAvailableMailNotifications(state, availableMailNotifications) {
    state.availableMailNotifications = availableMailNotifications
  },
  setMetaFields(state, metaFields) {
    state.metaFields = metaFields
  },
  setAdmins(state, admins) {
    state.admins = admins
  },
  setUserRole(state, userRole) {
    state.userRole = userRole
  },
}

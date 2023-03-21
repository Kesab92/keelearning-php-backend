export default {
  admins: state => state.admins,
  user(state) {
    return (id) => state.userDetails[id]
  },
  availableMailNotifications: state => state.availableMailNotifications,
  metaFields: state => state.metaFields,
  userRole: state => state.userRole,
}

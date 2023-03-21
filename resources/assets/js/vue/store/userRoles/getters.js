export default {
  isLoading: (state) => state.isLoading,
  userRole (state) {
    return (id) => state.userRoleDetails[id]
  },
  userRoles: (state) => state.userRoles,
}

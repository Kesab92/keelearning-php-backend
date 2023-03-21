export default {
  setIsLoading(state, isLoading) {
    state.isLoading = isLoading
  },
  setUserRole(state, userRole) {
    Vue.set(state.userRoleDetails, userRole.id, userRole)
  },
  setUserRoles(state, userRoles) {
    state.userRoles = userRoles
  },
}

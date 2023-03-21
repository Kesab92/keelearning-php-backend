export default {
  showMessage({ commit }, data) {
    commit('setColor', data.color ? data.color : null)
    commit('setMessage', data.message)
    commit('setVisible', true)
  },
  setVisible({ commit }, visible) {
    commit('setVisible', visible)
  },
}

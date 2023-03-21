let loadVoucherCancel = null

export default {
  loadVouchers({state, commit}) {
    if (loadVoucherCancel) {
      loadVoucherCancel()
    }
    commit('setVouchersListLoading', true)
    let cancelToken = new axios.CancelToken(c => {
      loadVoucherCancel = c
    })
    return axios.get("/backend/api/v1/vouchers", {
      cancelToken,
      params: {
        ...state.pagination,
        filter: state.filter,
        search: state.search,
      },
    }).then(response => {
      if(response instanceof axios.Cancel) {
        return
      }

      commit('setVouchersCount', response.data.data.count)
      commit('setVouchers', response.data.data.vouchers)
      commit('setVouchersListLoading', false)
      commit('setTagGroups', response.data.data.tagGroups)
      commit('setTagsWithoutGroup', response.data.data.tagsWithoutGroup)
      commit('setTagsRequired', response.data.data.tagsRequired)
    }).catch(e => {
      console.log(e)
    })
  },
  loadVoucher({state, commit}, {voucherId}) {
    return axios.get('/backend/api/v1/vouchers/' + voucherId).then((response) => {
      const voucher = response.data.voucher
      commit('setVoucher', voucher)
      commit('setTagGroups', response.data.tagGroups)
      commit('setTagsWithoutGroup', response.data.tagsWithoutGroup)
      commit('setTagsRequired', response.data.tagsRequired)
      return state.voucherDetails[voucher.id]
    })
  },
  saveVoucher({state, commit, dispatch}, data) {
    return axios.post('/backend/api/v1/vouchers/' + data.id, data).then((response) => {
      const voucher = response.data.voucher
      commit('setVoucher', voucher)
      commit('setTagGroups', response.data.tagGroups)
      commit('setTagsWithoutGroup', response.data.tagsWithoutGroup)
      commit('setTagsRequired', response.data.tagsRequired)
      dispatch('loadVouchers')
      return state.voucherDetails[voucher.id]
    }).catch(e => {
      if(e.response.data.errors) {
        for (const [key, inputErrors] of Object.entries(e.response.data.errors)) {
          inputErrors.forEach(error => {
            alert(error);
          })
        }
      }
      if(e.response.data.message) {
        alert(e.response.data.message)
      }
      console.log(e)
    })
  },
  archiveVoucher({state, commit, dispatch}, voucherId) {
    return axios.post(`/backend/api/v1/vouchers/${voucherId}/archive`).then((response) => {
      return dispatch('loadVoucher', {voucherId: voucherId})
    }).catch(e => {
      console.log(e)
    })
  },
  unarchiveVoucher({state, commit, dispatch}, voucherId) {
    return axios.post(`/backend/api/v1/vouchers/${voucherId}/unarchive`).then((response) => {
      return dispatch('loadVoucher', {voucherId: voucherId})
    }).catch(e => {
      console.log(e)
    })
  },
}

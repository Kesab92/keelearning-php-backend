import constants from "./constants"

export default {
  formatValidityDuration(voucher) {
    if (!voucher.validity_duration) {
      return 'endlos'
    }
    if (voucher.validity_interval === constants.VOUCHERS.INTERVAL_MONTHS) {
      return voucher.validity_duration === 1 ? (voucher.validity_duration + ' Monat') : (voucher.validity_duration + ' Monate')
    }
    if (voucher.validity_interval === constants.VOUCHERS.INTERVAL_DAYS) {
      return voucher.validity_duration === 1 ? (voucher.validity_duration + ' Tag') : (voucher.validity_duration + ' Tage')
    }
  },
}

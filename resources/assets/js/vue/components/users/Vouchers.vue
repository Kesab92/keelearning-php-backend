<template>
  <div>
    <v-alert
      v-if="longestVoucher"
      type="info"
      outline
      :value="isMainAdmin && (user.expires_at || (!unlimitedVoucherExists && limitedVoucherExists)) ">
      Dieser User wird nicht automatisch gelöscht, da er ein Hauptadmin ist.
    </v-alert>
    <v-alert
      v-if="user.expires_at"
      type="info"
      outline
      :value="!isMainAdmin">
      Benutzer wird am {{ user.expires_at | dateTime }} automatisch gelöscht (Admin-Löschdatum)
    </v-alert>
    <template v-else>
      <v-alert
        type="info"
        outline
        :value="!isMainAdmin && unlimitedVoucherExists && limitedVoucherExists">
        Benutzer hat einen unbegrenzten Voucher eingelöst - kein Voucher-Löschdatum vorhanden.
      </v-alert>
      <v-alert
        v-if="longestVoucher"
        class="my-3"
        type="info"
        outline
        :value="!isMainAdmin && !unlimitedVoucherExists && limitedVoucherExists">
        Benutzer wird am {{ longestVoucher.validUntil | dateTime }} durch den Voucher {{ longestVoucher.name }} automatisch gelöscht
      </v-alert>
    </template>
    <v-data-table
      :headers="headers"
      :items="user.vouchers"
      :rows-per-page-items="[50, 100, 200]"
      :total-items="user.vouchers.length"
      class="elevation-1 my-3">
      <!-- Items -->
      <tr
        @click="editVoucher(props.item.id)"
        class="clickable"
        slot="items"
        slot-scope="props">
        <td>
          {{ props.item.name }}
        </td>
        <td>
          {{ props.item.voucherCode.cash_in_date | dateTime }}
        </td>
        <td>
          {{ formatValidityDuration(props.item) }}
        </td>
        <td>
          {{ props.item.voucherCode.code }}
        </td>
      </tr>
      <template slot="no-data">
        <v-alert
          v-show="!user.vouchers || user.vouchers.length === 0"
          :value="true"
          type="info">
          Es sind keine Vouchers vorhanden.
        </v-alert>
      </template>
    </v-data-table>
  </div>
</template>

<script>
import { addMonths, addDays } from 'date-fns'
import voucherHelpers from "../../logic/voucherHelpers"

export default {
  props: ["user", "userRole"],
  data() {
    return {
      headers: [
        {text: 'Voucher-Name', value: 'name', sortable: false,},
        {text: 'Einlösedatum', value: 'voucherCode.cash_in_date', sortable: false,},
        {text: 'Dauer', value: 'validity_duration', sortable: false,},
        {text: 'Voucher-Code', value: 'voucherCode.code', sortable: false,},
      ],
    }
  },
  computed: {
    isMainAdmin() {
      if(!this.userRole) {
        return false
      }
      return this.userRole.is_main_admin
    },
    limitedVoucherExists() {
      const limitedVouchers = this.user.vouchers.filter(voucher => voucher.validity_duration)
      return limitedVouchers.length > 0
    },
    unlimitedVoucherExists() {
      const unlimitedVouchers = this.user.vouchers.filter(voucher => !voucher.validity_duration)
      return unlimitedVouchers.length > 0
    },
    longestVoucher() {
      if(!this.user.vouchers) {
        return null
      }

      let vouchers = JSON.parse(JSON.stringify(this.user.vouchers))

      const foreverVouchers = Object.values(vouchers).filter(voucher => !voucher.validity_duration)

      if(foreverVouchers.length) {
        return foreverVouchers[0]
      }

      vouchers = Object.values(vouchers).map(voucher => {
        if (!voucher.voucherCode.cash_in_date) {
          voucher.validUntil = null
          return voucher
        }
        if (voucher.validity_interval === this.$constants.VOUCHERS.INTERVAL_MONTHS) {
          voucher.validUntil = addMonths(new Date(voucher.voucherCode.cash_in_date), voucher.validity_duration)
        }
        if (voucher.validity_interval === this.$constants.VOUCHERS.INTERVAL_DAYS) {
          voucher.validUntil = addDays(new Date(voucher.voucherCode.cash_in_date), voucher.validity_duration)
        }
        return voucher
      })

      vouchers.sort((a,b) => {
        return new Date(b.validUntil) - new Date(a.validUntil);
      })

      return vouchers[0]
    },
  },
  methods: {
    editVoucher(voucherId) {
      this.$router.push({
        name: 'users.vouchers.edit.general',
        params: {
          voucherId: voucherId,
          userId: this.user.id,
        },
      })
    },
    formatValidityDuration(voucher) {
      return voucherHelpers.formatValidityDuration(voucher)
    },
  },
}
</script>

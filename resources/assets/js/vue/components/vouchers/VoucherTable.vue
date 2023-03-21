<template>
  <v-data-table
    :loading="isLoading"
    :headers="headers"
    :items="vouchers"
    :pagination.sync="pagination"
    :rows-per-page-items="[50, 100, 200]"
    :total-items="vouchersCount"
    class="elevation-1">
    <!-- Items -->
    <tr
      @click="editVoucher(props.item.id)"
      class="clickable"
      slot="items"
      slot-scope="props">
      <td>
        <v-tooltip
          v-if="hasBrokenTags(props.item)"
          top>
          <v-icon
            slot="activator"
            color="warning"
            style="cursor: help">warning
          </v-icon>
          <span>
            Diese Voucher haben eine fehlerhafte Tag-Konfiguration.
          </span>
        </v-tooltip>
        {{ props.item.name }}
      </td>
      <td>{{ formatValidityDuration(props.item) }}</td>
      <td>{{ props.item.amount - props.item.amount_used }} / {{ props.item.amount }}</td>
      <td>{{ props.item.type | typeFilter }}</td>
      <td>{{ props.item.created_at | dateTime }}</td>
      <td>
        <v-chip
          v-if="props.item.archived"
          disabled
          small
          color="gray">
          Archiviert
        </v-chip>
      </td>
    </tr>
    <template slot="no-data">
      <v-alert
        v-show="(!vouchers || vouchers.length === 0) && !isLoading"
        :value="true"
        type="info">
        Es sind keine Vouchers vorhanden.
      </v-alert>
    </template>
  </v-data-table>
</template>

<script>
import {mapGetters} from "vuex"
import helpers from "../../logic/helpers"
import voucherHelpers from "../../logic/voucherHelpers"

export default {
  data() {
    return {
      headers: [
        {text: 'Name', value: 'name'},
        {text: 'Gültigkeitsdauer', value: 'validity_duration'},
        {text: 'Codes verfügbar', value: 'amount'},
        {text: 'Typ', value: 'type'},
        {text: 'Erstellt am', value: 'created_at'},
        {text: 'Status', value: 'archived', sortable: false,},
      ],
    }
  },
  computed: {
    ...mapGetters({
      tagGroups: 'vouchers/tagGroups',
      tagsWithoutGroup: 'vouchers/tagsWithoutGroup',
      tagsRequired: 'vouchers/tagsRequired',
      vouchers: 'vouchers/vouchers',
      vouchersCount: 'vouchers/vouchersCount',
      isLoading: 'vouchers/listIsLoading',
    }),
    pagination: {
      get() {
        return this.$store.state.vouchers.pagination
      },
      set(data) {
        this.$store.commit('vouchers/setPagination', data)
      },
    },
  },
  watch: {
    pagination: {
      handler() {
        this.loadData()
      },
      deep: true,
    },
  },
  methods: {
    editVoucher(voucherId) {
      this.$router.push({
        name: 'vouchers.edit.general',
        params: {
          voucherId: voucherId,
        },
      })
    },
    async loadData() {
      await this.$store.dispatch('vouchers/loadVouchers')
      let autoRefresh = false
      Object.values(this.vouchers).forEach(voucher => {
        if (voucher.amount_generated < voucher.amount) {
          autoRefresh = true
        }
      })
      if (autoRefresh) {
        window.setTimeout(await this.$store.dispatch('vouchers/loadVouchers'), 3000)
      }
    },
    hasBrokenTags(voucher) {
      return helpers.hasBrokenTags(voucher, this.tagGroups)
    },
    formatValidityDuration(voucher) {
      return voucherHelpers.formatValidityDuration(voucher)
    }
  },
}
</script>

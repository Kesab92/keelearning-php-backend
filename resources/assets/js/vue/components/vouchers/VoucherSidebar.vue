<template>
  <details-sidebar
    :root-url="{ name: rootUrl }"
    :drawer-open="typeof $route.params.voucherId !== 'undefined'"
    data-action="vouchers/loadVoucher"
    :data-getter="(params) => $store.getters['vouchers/voucher'](params.voucherId)"
    :data-params="{voucherId: $route.params.voucherId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: voucher, refresh }">
      <router-view
        :voucher="voucher"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: voucher }">
      {{ voucher.name }}
    </template>
    <template v-slot:headerExtension="{ data: voucher }">
      Erstellt am {{ voucher.created_at | date }}
    </template>

  </details-sidebar>
</template>

<script>
export default {
  props: {
    rootUrl: {
      default: 'vouchers.index',
      required: false,
      type: String,
    },
    routePrefix: {
      default: '',
      required: false,
      type: String,
    },
  },
  methods: {
    getLinks(voucher) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: `${this.routePrefix}vouchers.edit.general`,
            params: {
              voucherId: voucher.id,
            },
          },
        },
      ]
    }
  }
}
</script>

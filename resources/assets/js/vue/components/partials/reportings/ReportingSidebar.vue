<template>
  <details-sidebar
    :root-url="{
      name: this.rootRouteName,
    }"
    :drawer-open="typeof $route.params.reportingId !== 'undefined'"
    data-action="reportings/loadReporting"
    :data-getter="(params) => $store.getters['reportings/reporting'](params.reportingId)"
    :data-params="{reportingId: $route.params.reportingId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: reporting, refresh }">
      <router-view
        :reporting="reporting"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: reporting }">
      Reporting
    </template>
    <template v-slot:headerExtension="{ data: reporting }">
      Erstellt am {{ reporting.created_at | date }}
    </template>
  </details-sidebar>
</template>

<script>
export default {
  props: {
    editRouteName: {
      type: String,
      required: true,
    },
    rootRouteName: {
      type: String,
      required: true,
    },
  },
  methods: {
    getLinks(reporting) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: this.editRouteName,
            params: {
              reportingId: reporting.id,
            },
          },
        },
      ]
    }
  }
}
</script>

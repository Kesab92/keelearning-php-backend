<template>
  <details-sidebar
    :root-url="{
      name: 'stats.users',
    }"
    :drawer-open="typeof $route.params.reportType !== 'undefined'"
    :get-links="getLinks"
  >
    <template v-slot:default="{}">
      <router-view />
    </template>
    <template v-slot:headerTitle="{}">
      {{ reportName }}
    </template>

  </details-sidebar>
</template>

<script>
export default {
  computed: {
    // TODO To add selecting of a report name. It can depend on url
    reportName() {
      return 'Benutzer Statistik exportieren'
    },
  },
  methods: {
    getLinks() {
      return [
      ]
    }
  }
}
</script>

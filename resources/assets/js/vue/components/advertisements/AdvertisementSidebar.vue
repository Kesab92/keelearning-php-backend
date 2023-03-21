<template>
  <details-sidebar
    :root-url="{
      name: 'advertisements.index',
    }"
    :drawer-open="typeof $route.params.advertisementId !== 'undefined'"
    data-action="advertisements/loadAdvertisement"
    :data-getter="(params) => $store.getters['advertisements/advertisement'](params.advertisementId)"
    :data-params="{advertisementId: $route.params.advertisementId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: advertisement, refresh }">
      <router-view
        :advertisement="advertisement"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: advertisement }">
      {{ advertisement.name }}
    </template>
    <template v-slot:headerExtension="{ data: advertisement }">
      Erstellt am {{ advertisement.created_at | date }}
    </template>
  </details-sidebar>
</template>

<script>
export default {
  methods: {
    getLinks(advertisement) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: 'advertisements.edit.general',
            params: {
              advertisementId: advertisement.id,
            },
          },
        },
      ]
    }
  }
}
</script>

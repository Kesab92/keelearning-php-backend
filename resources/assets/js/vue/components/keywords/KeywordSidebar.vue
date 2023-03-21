<template>
  <details-sidebar
    :root-url="{
      name: 'keywords.index',
    }"
    :drawer-open="typeof $route.params.keywordId !== 'undefined'"
    data-action="keywords/loadKeyword"
    :data-getter="(params) => $store.getters['keywords/keyword'](params.keywordId)"
    :data-params="{keywordId: $route.params.keywordId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: keyword, refresh }">
      <router-view
        :keyword="keyword"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: keyword }">
      {{ keyword.name }}
    </template>
    <template v-slot:headerExtension="{ data: keyword }">
      Erstellt am {{ keyword.created_at | date }}
    </template>

  </details-sidebar>
</template>

<script>
export default {
  methods: {
    getLinks(keyword) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: 'keywords.edit.general',
            params: {
              keywordId: keyword.id,
            },
          },
        },
      ]
    }
  }
}
</script>

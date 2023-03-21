<template>
  <details-sidebar
    :root-url="{
      name: 'tags.index',
    }"
    :drawer-open="typeof $route.params.tagId !== 'undefined'"
    data-action="tags/loadTag"
    :data-getter="(params) => $store.getters['tags/tag'](params.tagId)"
    :data-params="{tagId: $route.params.tagId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: tag, refresh }">
      <router-view
        :tag="tag"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: tag }">
      {{ tag.label }}
    </template>
    <template v-slot:headerExtension="{ data: tag }">
      Verändert am {{ tag.updated_at | date }}
    </template>

  </details-sidebar>
</template>

<script>
export default {
  methods: {
    getLinks(tag) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: 'tags.edit.general',
            params: {
              tagId: tag.id,
            },
          },
        },
      ]
    }
  }
}
</script>

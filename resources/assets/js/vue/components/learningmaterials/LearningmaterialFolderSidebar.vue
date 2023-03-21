<template>
  <details-sidebar
    :root-url="{
      name: 'learningmaterials.index',
      params: {
        folderId: this.$route.params.folderId,
      }
    }"
    :drawer-open="['learningmaterials.folder.edit.general'].includes($route.name)"
    :data-getter="(params) => $store.getters['learningmaterials/folder'](params.folderId)"
    :data-params="{folderId: $route.params.folderId}"
    :get-links="(data) => getLinks(data)"
  >
    <template v-slot:headerTitle="{ data: folder }">
      {{ folder.name }}
    </template>
    <template v-slot:headerExtension="{ data: folder }">
      Erstellt am {{ folder.created_at | date }}
    </template>
    <template v-slot:default="{ data: folder, refresh }">
      <router-view
        :folder="folder"
        @refresh="refresh"
      />
    </template>
  </details-sidebar>
</template>


<script>
export default {
  methods: {
    getLinks(folder) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: 'learningmaterials.folder.edit.general',
            params: {
              folderId: folder.id,
            },
          },
        },
      ]
    },
  },
}
</script>

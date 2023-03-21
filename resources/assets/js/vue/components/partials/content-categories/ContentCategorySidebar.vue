<template>
  <details-sidebar
    :root-url="{
      name: this.rootRouteName,
    }"
    :drawer-open="typeof $route.params.categoryId !== 'undefined'"
    data-action="contentCategories/loadCategory"
    :data-getter="(params) => $store.getters['contentCategories/category'](params.categoryId)"
    :data-params="{categoryId: $route.params.categoryId}"
    :get-links="getLinks"
  >
    <template v-slot:default="{ data: category, refresh }">
      <router-view
        :category="category"
        :type="type"
        :read-only="readOnly"
        @refresh="refresh"
      />
    </template>
    <template v-slot:headerTitle="{ data: category }">
      {{ category.name }}
    </template>
    <template v-slot:headerExtension="{ data: category }">
      Erstellt am {{ category.created_at | date }}
    </template>
  </details-sidebar>
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      required: true,
    },
    editRouteName: {
      type: String,
      required: true,
    },
    rootRouteName: {
      type: String,
      required: true,
    },
    readOnly: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  methods: {
    getLinks(course) {
      return [
        {
          label: 'Allgemein',
          to: {
            name: this.editRouteName,
            params: {
              courseId: course.id,
            },
          },
        },
      ]
    }
  }
}
</script>

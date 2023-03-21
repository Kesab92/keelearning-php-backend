<template>
  <div>
    <v-list
      v-if="categories && categories.length"
      two-line
      class="pt-0"
    >
      <CategoriesTableEntry
        v-for="category in categories"
        :key="category.id"
        :category="category"
        :tag-groups="tagGroups"
        :category-groups="categoryGroups"
        @update="updateCategory" />
    </v-list>
    <v-alert
      v-else
      type="info"
      :value="true"
    >
      Diese Oberkategorie hat noch keine Unterkategorien.
    </v-alert>
  </div>
</template>

<script>
import CategoriesTableEntry from './CategoriesTableEntry'

export default {
  props: {
    categories: {
      required: true,
      type: Array,
    },
    categoryGroups: {
      required: true,
      type: Array,
    },
    tagGroups: {
      required: true,
      type: Array,
    },
  },
  methods: {
    updateCategory(category) {
      this.$emit('update', category)
    },
  },
  components: {
    CategoriesTableEntry,
  },
}
</script>

<style lang="scss" scoped>
.inactive ::v-deep {
  .v-list__tile__avatar,
  .v-list__tile__title,
  .v-list__tile__sub-title {
    opacity: 0.5;
  }
}

</style>

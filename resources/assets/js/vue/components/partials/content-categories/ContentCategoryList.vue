<template>
  <div>
    <v-chip
      :key="category.id"
      outline
      label
      small
      color="black"
      v-for="category in visibleCategories">
      {{ category.name }}
    </v-chip>
  </div>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  props: {
    type: {
      type: String,
      required: true,
    },
    categories: {
      type: [Array, Number],
      default: null,
      required: true,
    },
  },
  created() {
    if(!this.getCategories(this.type) && !this.isLoading) {
      this.$store.dispatch("contentCategories/updateCategories", this.type)
    }
  },
  computed: {
    ...mapGetters({
      getCategories: "contentCategories/getCategories",
      isLoading: "contentCategories/listIsLoading",
    }),
    visibleCategories() {
      let allCategories = this.getCategories(this.type)
      if(!allCategories) {
        return []
      }
      let selectedCategories = this.categories
      if(!selectedCategories) {
        selectedCategories = []
      }
      if(!Array.isArray(selectedCategories)) {
        selectedCategories = [selectedCategories]
      }
      return allCategories
        .filter(category => selectedCategories.includes(category.id))
        .sort((a, b) => {
          return a.name.localeCompare(b.name)
        })
    },
  },
}
</script>

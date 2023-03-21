<template>
  <p
    class="mb-0 grey--text"
    v-if="!isLoading && items.length === 0">Es wurden noch keine Lernkategorien angelegt.</p>
  <v-autocomplete
    v-else
    :items="visibleItems"
    :deletable-chips="multiple"
    dense
    item-text="name"
    item-value="id"
    :label="label"
    :multiple="multiple"
    :small-chips="multiple"
    :placeholder="placeholderText"
    :hint="hint"
    :outline="outline"
    :persistent-hint="persistentHint"
    :allow-overflow="false"
    :disabled="disabled"
    :clearable="!multiple && clearable"
    no-data-text="Keine Lernkategorien gefunden"
    v-model="selectedCategories"/>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  props: {
    value: {
      type: [Array, Number, null],
      default: null,
      required: false,
    },
    label: {
      type: String,
      default: 'Lernkategorien',
      required: false,
    },
    placeholder: {
      type: String,
      default: null,
      required: false,
    },
    hint: {
      type: String,
      default: null,
      required: false,
    },
    persistentHint: {
      type: Boolean,
      default: false,
      required: false,
    },
    multiple: {
      type: Boolean,
      default: false,
      required: false,
    },
    outline: {
      type: Boolean,
      default: false,
      required: false,
    },
    disabled: {
      type: Boolean,
      default: false,
      required: false,
    },
    clearable: {
      type: Boolean,
      default: true,
      required: false,
    },
    limitToTagRights: {
      type: Boolean,
      default: false,
      required: false,
    },
    showLimitedCategories: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  data() {
    return {
      isLoading: false,
    }
  },
  async created() {
    this.isLoading = true
    await this.$store.dispatch('categories/updateCategories')
    this.isLoading = false
  },
  computed: {
    ...mapGetters({
      categories: 'categories/categories',
      isFullAdmin: 'app/isFullAdmin',
      tagRights: 'app/tagRights',
    }),
    selectedCategories: {
      get() {
        return this.value
      },
      set(categories) {
        if(typeof categories === "undefined") {
          categories = null
        }
        this.$emit('input', categories)
      },
    },
    items() {
      if(!this.categories.length) {
        return []
      }

      let categories = [...this.categories].sort((a, b) => {
        return a.name.localeCompare(b.name)
      })

      if(this.limitToTagRights && this.showLimitedCategories && !this.isFullAdmin) {
        if(this.tagRights.length > 0) {
          categories.forEach(category => {
            category.disabled = !this.hasAccessToCategory(category)
          })
        }
      }

      return categories
    },
    visibleItems() {
      if(this.items.length <= 0) {
        return []
      }

      if(!this.limitToTagRights || this.isFullAdmin || (this.showLimitedCategories && this.limitToTagRights)) {
        return this.items
      }

      return this.items.filter(item => this.hasAccessToCategory(item))
    },
    placeholderText() {
      if(this.isLoading) {
        return 'Lade...'
      }
      return this.placeholder
    },
  },
  methods: {
    hasAccessToCategory(category) {
      if(!category.tags.length) {
        return true
      }

      return category.tags.some(tag => {
        return this.tagRights.includes(tag.id)
      })
    },
  }
}
</script>

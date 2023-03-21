<template>
  <p
    class="mb-0 grey--text"
    v-if="!isLoading && allPages.length === 0">Es wurden noch keine Seiten angelegt.</p>
  <v-autocomplete
    v-else
    :items="items"
    :deletable-chips="multiple"
    dense
    item-text="title"
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
    :clearable="!multiple"
    no-data-text="Keine Seiten gefunden"
    v-model="selectedPages"/>
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
      default: 'Seiten',
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
    showOnlyVisible: {
      type: Boolean,
      default: false,
      required: false,
    },
    invisibleItems: {
      type: Array,
      default: function () {
        return []
      },
      required: false,
    },
    forTags: {
      type: Array,
      default() {
        return []
      },
      required: false,
    },
  },
  data() {
    return {
      isLoading: false,
    }
  },
  created() {
    this.isLoading = true
    Promise.all([
      this.$store.dispatch('pages/updateMainPages'),
      this.$store.dispatch('pages/updateSubPages'),
    ]).then(() => {
      this.isLoading = false
    })
  },
  computed: {
    ...mapGetters({
      allPages: 'pages/mainPages',
      subPages: 'pages/subPages',
    }),
    availablePages() {
      if(!this.showOnlyVisible) {
        return this.allPages
      }
      return this.allPages.filter(p => p.visible === 1)
    },
    selectedPages: {
      get() {
        return this.value
      },
      set(pages) {
        if(typeof pages === "undefined") {
          pages = null
        }
        this.$emit('input', pages)
      },
    },
    items() {
      if(this.availablePages.length <= 0) {
        return []
      }
      let pages = this.availablePages.filter(page => !this.invisibleItems.includes(page.id))
      if(this.forTags.length) {
        pages = pages.map(page => {
          // See if we've got any subpages for the given "forTAGs" array
          // If we do, replace the title of the page with the title of the subpage
          let subPage = this.subPages.find(subPage => {
            if(subPage.parent_id !== page.id) {
              return false
            }
            return subPage.tags.some(tag => {
              return this.forTags.includes(tag.id)
            })
          })
          if(subPage) {
            page.title = subPage.title
          }
          return page
        })
      }
      pages = [...pages].sort((a, b) => {
        return a.title.localeCompare(b.title)
      })
      return pages
    },
    placeholderText() {
      if(this.isLoading) {
        return 'Lade...'
      }
      return this.placeholder
    },
  },
}
</script>

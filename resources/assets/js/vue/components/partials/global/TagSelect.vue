<template>
  <p
    class="mb-0 grey--text"
    v-if="!isLoading && allTags.length === 0">Es wurden noch keine TAGs angelegt.</p>
  <v-autocomplete
    v-else
    :items="visibleItems"
    deletable-chips
    dense
    item-text="label"
    item-value="id"
    :label="label"
    :multiple="multiple"
    small-chips
    :placeholder="placeholderText"
    :hint="hint"
    :outline="outline"
    :persistent-hint="persistentHint"
    :allow-overflow="false"
    :disabled="disabled"
    :rules="tagRule()"
    v-model="selectedTags"
    :search-input.sync="searchInput">
    <template v-slot:selection="data">
      <v-chip
        v-if="disabledTagIds.includes(data.item.id) || disabled"
        v-bind="data.attrs"
        :input-value="data.selected"
        disabled
      >
        {{ data.item.label }}
      </v-chip>
      <v-chip
        v-else
        v-bind="data.attrs"
        :input-value="data.selected"
        close
        @input="remove(data.item)"
      >
        {{ data.item.label }}
      </v-chip>
    </template>
    <template slot="append">
      <slot name="append"></slot>
    </template>
   <template slot="no-data">
      <div class="s-noTagFound"> Der TAG <strong>{{searchInput}}</strong> wurde nicht gefunden</div>
    </template>
  </v-autocomplete>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  props: {
    value: {
      type: [Array, Number],
      required: true,
    },
    label: {
      type: String,
      default: 'TAGs',
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
    extendItems: {
      type: Function,
      default: null,
      required: false,
    },
    invisibleItems: {
      type: Array,
      default: function () {
        return []
      },
      required: false,
    },
    limitToTagRights: {
      type: Boolean,
      default: false,
      required: false,
    },
    showLimitedTags: {
      type: Boolean,
      default: false,
      required: false,
    },
    required: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  data() {
    return {
      isLoading: false,
      searchInput:"",
    }
  },
  created() {
    this.isLoading = true
    this.$store.dispatch('tags/updateTags').then(() => {
      this.isLoading = false
    })
  },
  computed: {
    ...mapGetters({
      allTags: 'tags/tags',
      isFullAdmin: 'app/isFullAdmin',
      tagRights: 'app/tagRights',
    }),
    selectedTags: {
      get() {
        return this.value
      },
      set(tags) {
        this.$emit('input', tags)
      },
    },
    items() {
      if(this.allTags.length <= 0) {
        return []
      }
      let tags = this.allTags.filter(tag => !this.invisibleItems.includes(tag.id))
      if(this.extendItems) {
        tags = this.extendItems(tags)
      }
      tags = [...tags].sort((a, b) => {
        if(a.id === -1) {
          return -1
        }
        if(b.id === -1) {
          return 1
        }
        return a.label.localeCompare(b.label)
      })
      if(this.limitToTagRights && this.showLimitedTags && !this.isFullAdmin) {
        if(this.tagRights.length > 0) {
          tags.forEach(tag => tag.disabled = !this.tagRights.includes(tag.id))
        }
      }
      return tags
    },
    visibleItems() {
      if(this.items.length <= 0) {
        return []
      }
      if(!this.limitToTagRights || this.isFullAdmin || (this.showLimitedTags && this.limitToTagRights)) {
        return this.items
      }
      return this.items.filter(item => this.tagRights.includes(item.id))
    },
    disabledTagIds() {
      if(this.items.length <= 0) {
        return []
      }
      if(!this.limitToTagRights || this.isFullAdmin) {
        return []
      }
      return this.items.filter(item => !this.tagRights.includes(item.id)).map(item => item.id)
    },
    placeholderText() {
      if(this.isLoading) {
        return 'Lade...'
      } else {
        return this.placeholder
      }
    },
  },
  methods: {
    remove (item) {
      const index = this.selectedTags.indexOf(item.id)
      if (index >= 0) this.selectedTags.splice(index, 1)
    },

    tagRule() {
      let validateStatus = true

      if(!this.isFullAdmin && !this.selectedTags.length) {
        validateStatus = 'Bitte geben Sie einen TAG an'
      }

      if(!this.required) {
        validateStatus = true
      }

      return [validateStatus]
    },
  },
}
</script>
<style lang="scss" scoped>
  #app .s-noTagFound{
    padding:16px;
  }
</style>

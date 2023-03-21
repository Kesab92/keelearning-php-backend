<template>
  <v-list-tile
    avatar
    @click="modalOpen = !modalOpen"
    :class="{
      inactive: !category.active,
    }"
    class="overwrite-default-height"
  >
    <v-list-tile-avatar>
      <img
        v-if="category.icon_url"
        :src="category.icon_url"
      >
      <v-icon
        v-else
        class="grey lighten-1 white--text"
      >
        collections
      </v-icon>
    </v-list-tile-avatar>

    <v-list-tile-content style="height: 50px">
      <v-list-tile-title>
        {{ category.name }}
      </v-list-tile-title>
      <v-list-tile-sub-title
        v-if="tagNames"
        class="tag-names"
      >
        {{ tagNames }}
      </v-list-tile-sub-title>
      <v-chip
        v-if="!category.active"
        outline
        class="inactive-badge"
      >
        inaktiv
      </v-chip>
    </v-list-tile-content>

    <v-list-tile-action>
      <category-modal
        :open="modalOpen"
        :category-data="category"
        :category-groups="categoryGroups"
        :tag-groups="tagGroups"
        @setOpen="setModalOpen"
        @update="updateCategory"
      />
    </v-list-tile-action>
  </v-list-tile>
</template>

<script>
  import CategoryModal from './CategoryModal'
  import {mapGetters} from "vuex"

  export default {
    props: {
      category: {
        required: true,
        type: Object,
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
    data() {
      return {
        modalOpen: false,
      }
    },
    created() {
      let query = window.location.hash.substr(1)
      query.split('&').forEach(query => {
        let queryParts = query.split('=')
        let key = decodeURIComponent(queryParts[0])
        let value = decodeURIComponent(queryParts[1])
        if(key === 'categoryId' && parseInt(value, 10) === this.category.id) {
          this.$nextTick(() => {
            this.modalOpen = true
            history.pushState("", document.title, window.location.pathname + window.location.search)
          })
        }
      })
    },
    methods: {
      updateCategory(category) {
        this.$emit('update', category)
      },
      setModalOpen(open) {
        this.modalOpen = open
      },
    },
    computed: {
      ...mapGetters({
        tags: 'tags/tags',
      }),
      tagNames() {
        return this.tags.filter(tag => {
          return this.category.tags.indexOf(tag.id) > -1
        }).map(tag => tag.label)
          .join(', ')
      }
    },
    components: {
      CategoryModal,
    },
  }
</script>

<style lang="scss">
#app .overwrite-default-height {
  .v-list__tile {
    height: auto !important;
    padding-bottom: 10px;
    padding-top: 10px;
  }

  .v-list__tile__content {
    height: auto !important;
  }

  .tag-names {
    overflow: visible !important;
    white-space: normal !important;
  }

  .inactive-badge {
    margin-top: -16px;
    position: absolute;
    right: 0;
    top: 50%;
  }
}
</style>

<template>
  <v-data-table
    :headers="headers"
    :items="items"
    :custom-sort="sort"
    item-key="key"
    hide-actions
    class="mb-4"
  >
    <tr
      class="s-contentRow clickable -material"
      :class="{
        '-untranslated': !props.item.translated,
      }"
      slot="items"
      @click="openContent(props.item, $event)"
      slot-scope="props">
      <td class="pl-2 py-2 pr-0">
        <v-layout
          row
          align-center>
          <div class="s-iconWrapper mr-2">
            <v-badge
              overlap
              bottom
              color="white">
              <template
                v-slot:badge>
                <v-icon small>insert_drive_file</v-icon>
              </template>
              <v-avatar
                tile
                :size="40"
              >
                <img
                  v-if="props.item.icon"
                  :src="props.item.icon">
                <img
                  v-else
                  class="s-placeholder"
                  src="/img/placeholder.png">
              </v-avatar>
            </v-badge>
          </div>
          <template>
            <div>
              {{ props.item.name }}
              <div
                v-if="props.item.path"
                class="grey--text"
              >{{ props.item.path }}</div>
            </div>
          </template>
        </v-layout>
      </td>
      <td>
        <template v-if="props.item.tags">
          <v-chip
            :key="tag"
            disabled
            small
            v-for="tag in props.item.tags">
            {{ tagLabel(tag) }}
          </v-chip>
        </template>
      </td>
      <td>
        {{ props.item.material.viewcount_total }}
      </td>
      <td>
        {{ props.item.created_at | date }}
      </td>
    </tr>
    <template slot="no-data">
      <v-alert
        :value="true"
        outline
        type="info">
        Keine Dateien gefunden.
      </v-alert>
    </template>
  </v-data-table>
</template>

<script>
import {mapGetters} from "vuex"
import helpers from "../../logic/helpers"

export default {
  data() {
    return {
      headers: [
        {
          text: "Name",
          value: "name",
          sortable: true,
        },
        {
          text: "TAGs",
          sortable: false,
        },
        {
          text: "Aufrufe",
          value: "material.viewcount_total",
          sortable: true,
        },
        {
          text: "Erstellt am",
          value: "created_at",
          sortable: true,
        },
      ],
    }
  },
  computed: {
    ...mapGetters({
      availableTags: 'tags/tags',
      activeLanguage: 'languages/activeLanguage',
      defaultLanguage: 'languages/defaultLanguage',
    }),
    items() {
      const materials = this.$store.getters['learningmaterials/searchResult']
      const items = []
      materials.forEach(material => {
        items.push({
          id: material.id,
          type: 'material',
          key: 'material-' + material.id,
          name: material.title,
          created_at: material.created_at,
          tags: material.tags,
          material: material,
          icon: material.cover_image_url,
          translated: this.isTranslated(material.translations),
          path: material.path,
        })
      })
      return items
    }
  },
  methods: {
    isTranslated(translations) {
      return !!translations.find(translation => translation.language === this.activeLanguage)
    },
    openContent(content, $event) {
      if ($event.target.closest('a')) {
        // Don't do anything if an actual link is clicked
        return
      }
      this.$router.push({
        name: 'learningmaterials.edit.general',
        params: {
          folderId: content.material.learning_material_folder_id,
          learningmaterialId: content.id,
        }
      })
    },
    tagLabel(tagId) {
      const tag = this.availableTags.find(tag => tag.id === tagId)
      if (!tag) {
        return ''
      }
      return tag.label
    },
    sort(items, index, isDescending) {
      if (index === null) return items

      return items.sort((a, b) => {
        let sortA = helpers.getObjectValueByPath(a, index)
        let sortB = helpers.getObjectValueByPath(b, index)

        if (isDescending) {
          [sortA, sortB] = [sortB, sortA]
        }

        // Check if both are numbers
        if (!isNaN(sortA) && !isNaN(sortB)) {
          return sortA - sortB
        }

        // Check if both cannot be evaluated
        if (sortA === null && sortB === null) {
          return 0
        }

        [sortA, sortB] = [sortA, sortB]
          .map(s => (
            (s || '').toString().toLocaleLowerCase()
          ))

        if (sortA > sortB) return 1
        if (sortA < sortB) return -1

        return 0
      })
    },
  },
}
</script>

<style lang="scss" scoped>
.s-iconWrapper {
  width: 40px;
  height: 40px;
  text-align: center;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  vertical-align: middle;
}

.s-placeholder {
  filter: grayscale(1) opacity(0.2) contrast(0.5);
}
</style>

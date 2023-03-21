<template>
  <div v-if="isLoading" class="s-spinner" >
      <v-progress-circular
        indeterminate
        color="purple"
        :size="50"
      ></v-progress-circular>
  </div>
  <v-data-table
    v-else
    :headers="headers"
    :items="items"
    :custom-sort="sort"
    item-key="key"
    hide-actions
    class="mb-4"
  >
    <tr
      class="s-contentRow clickable"
      :class="{
        '-untranslated': !props.item.translated,
        '-folder': props.item.type === 'folder',
        '-hidden': props.item.type === 'material' && !props.item.visible,
        '-material': props.item.type === 'material',
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
                v-if="props.item.type === 'material'"
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
          <template v-if="props.item.type === 'folder'">
            <div>
              <div>
                <strong>{{ props.item.name }}</strong>
              </div>
              <div class="grey--text">
                <v-icon small>insert_drive_file</v-icon> {{ fileCount(props.item.folder.id) }}
                <v-icon
                  small
                  class="ml-2">folder_open</v-icon> {{ folderCount(props.item.folder.id) }}
              </div>
            </div>
          </template>
          <template v-if="props.item.type === 'material'">
            <div>
              <v-icon v-if="!props.item.visible" small>visibility_off</v-icon>
              {{ props.item.name }}
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
        <template v-if="props.item.type === 'material'">
          {{ props.item.material.viewcount_total }}
        </template>
      </td>
      <td>
        <template v-if="props.item.type === 'material'">
          {{ props.item.likes }}
        </template>
      </td>
      <td>
        {{ props.item.created_at | date }}
      </td>
      <td
        class="pa-0"
        style="width: 72px">
        <router-link
          class="pa-4"
          v-if="props.item.type === 'folder'"
          :to="{name: 'learningmaterials.folder.edit.general', params: {folderId: props.item.id}}">
          <v-icon>more_vert</v-icon>
        </router-link>
      </td>
    </tr>
    <template slot="no-data">
      <v-alert
        :value="true"
        outline
        type="info">
        Dieser Ordner hat noch keine Inhalte.
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
          text: "Likes",
          value: "likes",
          sortable: false,
        },
        {
          text: "Erstellt am",
          value: "created_at",
          sortable: true,
        },
        {
          text: "",
          value: "id",
          sortable: false,
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
      const contents = this.$store.getters['learningmaterials/folderContents']
      const items = []
      contents.folders.forEach(folder => {
        items.push({
          id: folder.id,
          type: 'folder',
          key: 'folder-' + folder.id,
          name: folder.name,
          tags: folder.tags,
          created_at: folder.created_at,
          folder: folder,
          icon: folder.folder_icon_url,
          translated: this.isTranslated(folder.translations),
        })
      })
      contents.materials.forEach(material => {
        items.push({
          id: material.id,
          type: 'material',
          key: 'material-' + material.id,
          name: material.title,
          created_at: material.created_at,
          tags: material.tags,
          material: material,
          visible: material.visible,
          icon: material.cover_image_url,
          translated: this.isTranslated(material.translations),
          likes: material.likes,
        })
      })
      return items
    },
    isLoading(){
      return this.$store.getters['learningmaterials/isLoading']
    }
  },
  methods: {
    isTranslated(translations) {
      return !!translations.find(translation => translation.language === this.activeLanguage)
    },
    openContent(content, $event) {
      if($event.target.closest('a')) {
        // Don't do anything if an actual link is clicked
        return
      }
      if(content.type === 'folder') {
        this.$router.push({
          name: 'learningmaterials.index',
          params: {
            folderId: content.id,
          }
        })
      }
      if(content.type === 'material') {
        this.$router.push({
          name: 'learningmaterials.edit.general',
          params: {
            folderId: content.material.learning_material_folder_id,
            learningmaterialId: content.id,
          }
        })
      }
    },
    tagLabel(tagId) {
      const tag = this.availableTags.find(tag => tag.id === tagId)
      if(!tag) {
        return ''
      }
      return tag.label
    },
    fileCount(folderId) {
      let folderMaterials = this.$store.getters['learningmaterials/materialsByFolder'][folderId]
      if(!folderMaterials) {
        return 0
      }
      return folderMaterials.length
    },
    folderCount(folderId) {
      let subfolders = this.$store.getters['learningmaterials/foldersByParent'][folderId]
      if(!subfolders) {
        return 0
      }
      return subfolders.length
    },
    sort(items, index, isDescending) {
      if (index === null) return items

      // Make sure folders are always above files. The rest of the sort method is vuetify's default.
      return items.sort((a, b) => {
        if(a.type === 'folder' && b.type !== 'folder') {
          return -1
        }
        if(a.type !== 'folder' && b.type === 'folder') {
          return 1
        }
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
::v-deep .s-contentRow {
  &.-folder {
    background: #f9f9f9;
  }
  &.-hidden {
    opacity: 0.5;
  }
}
#app .s-spinner{
    display: flex;
    justify-content: center;
    margin-top:40px;
  }
</style>

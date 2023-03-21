<template>
  <div>
    <v-dialog
      v-model="active"
      width="80%"
    >
      <v-card>
        <v-toolbar>
          <v-toolbar-title>
            Neuen Ordner wählen
          </v-toolbar-title>
        </v-toolbar>
        <v-card-text>
          <v-text-field
            v-model="search"
            ref="search"
            outline
            label="Ordner suchen"
          />
          <v-list
            class="s-folderList"
            dense>
            <template
              v-for="folder in folders">
              <v-list-tile
                @click="setFolder(folder)"
                :key="folder.id"
                :class="{
                  'grey--text': disabledFolderIds.includes(folder.id),
                  'primary': folder.id === selectedFolder,
                  'white--text': folder.id === selectedFolder,
              }">
                <v-list-tile-avatar
                  size="30"
                  tile>
                  <img
                    v-if="folder.icon_url"
                    :src="folder.icon_url">
                  <v-icon
                    :dark="folder.id === selectedFolder"
                    v-else
                    large>folder_open</v-icon>
                </v-list-tile-avatar>

                <v-list-tile-content>
                  <v-list-tile-title>{{ folder.path }}</v-list-tile-title>
                </v-list-tile-content>
              </v-list-tile>
              <v-divider :key="`divider-${folder.id}`" />
            </template>
          </v-list>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            color="primary"
            @click="selectLocation"
          >
            Verschieben
          </v-btn>
          <v-spacer/>
          <v-btn
            flat
            @click="active = false"
          >
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  props: [
    'allowRoot',
    'callback',
    'disabledFolder',
    'preselectedFolder',
    'value',
  ],
  data() {
    return {
      search: null,
      selectedFolder: null,
    }
  },
  computed: {
    active: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
    folders() {
      const folders = []
      if(this.allowRoot) {
        folders.push({
          id: null,
          path: '/'
        })
      }
      Object.values(this.$store.getters['learningmaterials/folders']).forEach(folder => {
        let path = this.getPathName(folder)
        if(this.search && path.indexOf(this.search) === -1) {
          return
        }
        folders.push({
          id: folder.id,
          icon_url: folder.folder_icon_url,
          path: path
        })
      })
      return folders
    },
    disabledFolderIds() {
      if(!this.disabledFolder) {
        return []
      }
      const disabledFolderIds = []
      // Disable the disabledFolder and recursively all folders inside of it
      const disableFolderWithChildren = (parentId) => {
        disabledFolderIds.push(parentId)
        Object.values(this.$store.getters['learningmaterials/folders']).forEach(folder => {
          if(folder.parent_id === parentId) {
            disableFolderWithChildren(folder.id)
          }
        })
      }
      disableFolderWithChildren(this.disabledFolder)
      return disabledFolderIds
    },
  },
  watch: {
    active() {
      if (this.active) {
        this.search = null
        this.$nextTick(() => this.$refs.search.$refs.input.select())
      }
    },
    preselectedFolder: {
      handler() {
        this.selectedFolder = this.preselectedFolder
      },
      immediate: true,
    },
  },
  methods: {
    getPathName(folder) {
      let name = '/' + folder.name
      if(folder.parent_id) {
        name = this.getPathName(this.$store.getters['learningmaterials/folders'][folder.parent_id]) + name
      }
      return name
    },
    setFolder(folder) {
      if (this.disabledFolderIds.includes(folder.id)) {
        return
      }
      this.selectedFolder = folder.id
    },
    selectLocation() {
      if(!this.folders.find(folder => folder.id === this.selectedFolder)) {
        // Only execute the move command if the selected folder is currently visible
        alert('Bitte wählen Sie einen Ordner')
        return
      }
      this.callback(this.selectedFolder).then(() => {
        this.active = false
      })
    }
  },
}
</script>

<style lang="scss" scoped>
.s-folderList {
  max-height: calc(100vh - 350px);
  overflow-y: scroll;
}
</style>

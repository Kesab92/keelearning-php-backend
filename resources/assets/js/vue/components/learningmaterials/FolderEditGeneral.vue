<template>
  <div v-if="folderData">
    <details-sidebar-toolbar>
      <template v-slot:default>
        <v-btn
          color="primary"
          @click="save"
        >
          Speichern
        </v-btn>
        <v-spacer />
        <v-btn
          color="red"
          outline
          @click="remove"
          :loading="isSaving"
        >
          Löschen
        </v-btn>
      </template>
      <template v-slot:alerts>
        <reusable-clone-warning v-if="folder.is_reusable_clone" />
      </template>
    </details-sidebar-toolbar>

    <div class="pa-4">
      <v-layout
        row
        align-center
        class="mb-4">
        <v-flex grow>
          <translated-input
            v-model="folderData.name"
            label="Name"
            :translations="folderData.translations"
            attribute="name" />
        </v-flex>
        <v-btn
          outline
          color="secondary"
          @click="locationSelectModalOpen = true"
        >
          <v-icon left>input</v-icon>
          Verschieben
        </v-btn>
      </v-layout>

      <div class="subheading">Sichtbar für folgende User</div>

      <tag-select
        multiple
        label="Sichtbar für folgende User"
        placeholder="Alle"
        color="blue-grey lighten-2"
        outline
        v-model="folderData.tags"
      />

      <DeleteDialog
        v-model="deleteDialogOpen"
        :dependency-url="`/backend/api/v1/learningmaterialfolders/${folderData.id}/delete-information`"
        :deletion-url="`/backend/api/v1/learningmaterialfolders/${folderData.id}`"
        :redirect-url="afterDeletionRedirectURL"
        @deleted="handleFolderDeleted"
        type-label="Ordner"
        :entry-name="folderData.name">
        <v-alert
          slot="description"
          :value="true"
          type="warning"
          class="mb-4"
        >
          Beim Löschen dieses Ordners werden auch alle Dateien und Ordner darin gelöscht.
        </v-alert>
      </DeleteDialog>
      <SelectLocationModal
        v-model="locationSelectModalOpen"
        :preselected-folder="folderData.parent_id"
        :allow-root="true"
        :callback="moveFolder"
        :disabled-folder="folderData.id" />
    </div>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog"
import ReusableCloneWarning from "../partials/global/ReusableCloneWarning"
import SelectLocationModal from "./SelectLocationModal"
import TagSelect from "../partials/global/TagSelect"

export default {
  props: ['folder'],
  data() {
    return {
      folderData: null,
      isSaving: false,
      deleteDialogOpen: false,
      locationSelectModalOpen: false,
    }
  },
  watch: {
    material: {
      handler() {
        this.folderData = JSON.parse(JSON.stringify(this.folder))
      },
      immediate: true,
    },
  },
  computed: {
    afterDeletionRedirectURL() {
      let parentId = ''
      if(this.folderData.parent_id) {
        parentId = '/' + this.folderData.parent_id
      }
      return `/learningmaterials#/learningmaterials${parentId}`
    },
  },
  methods: {
    async save() {
      this.isSaving = true
      await this.$store.dispatch('learningmaterials/saveFolder', {
        id: this.folderData.id,
        name: this.folderData.name,
        tags: this.folderData.tags,
      })
      this.isSaving = false
    },
    moveFolder(newFolderId) {
      return this.$store.dispatch('learningmaterials/saveFolder', {
        id: this.folderData.id,
        parent_id: newFolderId,
      }).then(() => {
        this.$router.replace({
          name: 'learningmaterials.index',
          params: {
            folderId: newFolderId,
          },
        })
      })
    },
    remove() {
      this.deleteDialogOpen = true
    },
    handleFolderDeleted() {
      this.$store.commit('learningmaterials/deleteFolder', this.folderData.id)
    },
  },
  components: {
    DeleteDialog,
    ReusableCloneWarning,
    SelectLocationModal,
    TagSelect,
  },
}
</script>

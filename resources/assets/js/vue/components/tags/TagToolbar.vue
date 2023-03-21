<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="tagData"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          color="primary"
          @click="$emit('save')"
        >
          Speichern
        </v-btn>

        <v-spacer/>

        <v-btn
          :loading="isSaving"
          color="red"
          outline
          @click="remove"
        >
          Löschen
        </v-btn>
      </template>
    </details-sidebar-toolbar>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/tags/${tagData.id}`"
      :dependency-url="`/backend/api/v1/tags/${tagData.id}/delete-information`"
      :entry-name="tagData.label"
      :redirect-url="afterDeletionRedirectURL"
      type-label="TAG"
      @deleted="handleTagDeleted"/>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog";

export default {
  props: [
    'tagData',
    'isSaving',
  ],
  data() {
    return {
      deleteDialogOpen: false,
      afterDeletionRedirectURL: "/tags#/tags",
    }
  },
  methods: {
    remove() {
      this.deleteDialogOpen = true
    },
    handleTagDeleted() {
      this.$store.dispatch("tags/loadTags")
    },
  },
  components: {
    DeleteDialog,
  },
}
</script>

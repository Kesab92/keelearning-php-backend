<template>
  <div>
    <details-sidebar-toolbar>
      <template v-slot:default>
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
      <template v-slot:alerts>
        <v-alert
          outline
          type="info"
          :value="!page.visible">
          Die Seite ist erst in der App zu sehen, nachdem sie sichtbar geschalten wurde.
        </v-alert>
      </template>
    </details-sidebar-toolbar>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/pages/${page.id}`"
      :dependency-url="`/backend/api/v1/pages/${page.id}/delete-information`"
      :entry-name="page.title"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Seite"
      @deleted="handlePageDeleted"/>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog";

export default {
  props: [
    'page',
    'isSaving',
  ],
  data() {
    return {
      deleteDialogOpen: false,
    }
  },
  computed: {
    afterDeletionRedirectURL() {
      return "/pages#/pages"
    },
  },
  methods: {
    remove() {
      this.deleteDialogOpen = true
    },
    handlePageDeleted() {
      this.$store.commit("pages/deletePage", this.page.id)
      this.$store.dispatch("pages/loadPages")
    },
  },
  components: {
    DeleteDialog,
  },
}
</script>

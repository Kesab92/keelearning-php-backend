<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="keywordData"
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
      :deletion-url="`/backend/api/v1/keywords/${keywordData.id}`"
      :dependency-url="`/backend/api/v1/keywords/${keywordData.id}/delete-information`"
      :entry-name="keywordData.name"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Schlagwort"
      @deleted="handleKeywordDeleted"/>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog";

export default {
  props: [
    'keywordData',
    'isSaving',
  ],
  data() {
    return {
      deleteDialogOpen: false,
    }
  },
  computed: {
    afterDeletionRedirectURL() {
      return "/keywords#/keywords"
    },
  },
  methods: {
    remove() {
      this.deleteDialogOpen = true
    },
    handleKeywordDeleted() {
      this.$store.commit("keywords/deleteKeyword", this.keywordData.id)
      this.$store.dispatch("keywords/loadKeywords")
    },
  },
  components: {
    DeleteDialog,
  },
}
</script>

<style scoped>

</style>

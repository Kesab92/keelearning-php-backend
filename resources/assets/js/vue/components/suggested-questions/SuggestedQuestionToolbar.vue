<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="suggestedQuestion"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          color="primary"
          @click="accept"
        >
          Frage übernehmen
        </v-btn>

        <v-spacer/>

        <v-btn
          :loading="isSaving"
          color="red"
          outline
          @click="remove"
        >
          Vorschlag löschen
        </v-btn>
      </template>
    </details-sidebar-toolbar>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/suggested-questions/${suggestedQuestion.id}`"
      :dependency-url="`/backend/api/v1/suggested-questions/${suggestedQuestion.id}/delete-information`"
      :entry-name="suggestedQuestion.title"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Frage"
      @deleted="handleSuggestedQuestionDeleted"/>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog";

export default {
  props: ['suggestedQuestion'],
  data() {
    return {
      deleteDialogOpen: false,
      isSaving: false,
    }
  },
  computed: {
    afterDeletionRedirectURL() {
      return "/suggested-questions#/suggested-questions"
    },
  },
  methods: {
    remove() {
      this.deleteDialogOpen = true
    },
    handleSuggestedQuestionDeleted() {
      this.$store.dispatch("suggestedQuestions/loadSuggestedQuestions")
    },
    accept() {
      if (this.isSaving) {
        return
      }

      this.isSaving = true
      this.$store.dispatch("suggestedQuestions/acceptSuggestedQuestion", this.suggestedQuestion).then((response) => {
        window.location.href = `/questions#/questions/${response.data.question.id}/general`
      }).catch(() => {
        alert('Die Frage konnte leider nicht übernommen werden.')
      }).finally(() => {
        this.isSaving = false
      })
    }
  },
  components: {
    DeleteDialog,
  },
}
</script>

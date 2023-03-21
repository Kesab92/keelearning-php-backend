<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="quizTeamData"
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
      :deletion-url="`/backend/api/v1/quiz-teams/${quizTeamData.id}`"
      :dependency-url="`/backend/api/v1/quiz-teams/${quizTeamData.id}/delete-information`"
      :entry-name="quizTeamData.name"
      redirect-url="/quiz-teams#/quiz-teams"
      type-label="Quiz-Team"
      @deleted="handleQuizTeamDeleted"/>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog";

export default {
  props: [
    'quizTeamData',
    'isSaving',
  ],
  data() {
    return {
      deleteDialogOpen: false,
    }
  },
  methods: {
    remove() {
      this.deleteDialogOpen = true
    },
    handleQuizTeamDeleted() {
      this.$store.commit("quizTeams/deleteQuizTeam", this.quizTeamData.id)
      this.$store.dispatch("quizTeams/loadQuizTeams")
    },
  },
  components: {
    DeleteDialog,
  },
}
</script>

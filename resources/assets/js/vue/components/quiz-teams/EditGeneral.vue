<template>
  <div v-if="quizTeamData">
    <QuizTeamToolbar
      :quiz-team-data="quizTeamData"
      :is-saving="isSaving"
      @save="save"
    />
    <div class="pa-4">
      <v-text-field
        v-model="quizTeamData.name"
        label="Name"
        outline />
      <v-autocomplete
        v-model.number="quizTeamData.owner_id"
        :items="quizTeamData.members"
        hide-no-data
        item-text="fullName"
        item-value="id"
        label="Owner"
        outline
      />
      <Members v-model="quizTeamData.members" />
    </div>
  </div>
</template>

<script>
import QuizTeamToolbar from "./QuizTeamToolbar"
import Members from "./components/Members"

export default {
  props: ["quizTeam"],
  data() {
    return {
      quizTeamData: null,
      isSaving: false,
    }
  },
  watch: {
    quizTeam: {
      handler() {
        this.quizTeamData = JSON.parse(JSON.stringify(this.quizTeam))
      },
      immediate: true,
    },
  },
  methods: {
    save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      this.$store.dispatch("quizTeams/saveQuizTeam", {
        id: this.quizTeamData.id,
        name: this.quizTeamData.name.trim(),
        owner_id: this.quizTeamData.owner_id,
        members: this.quizTeamData.members.map(member => member.id),
      }).catch(() => {
        alert('Es gab einen Fehler beim Speichern des Quiz-Teams. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.isSaving = false
      })
    },
  },
  components: {
    Members,
    QuizTeamToolbar,
  },
}
</script>

<template>
  <div>
    <ModuleIntro>
      <template slot="title">
        Benutzer eingereichte Fragen
      </template>
      <template slot="description">
        Hier können Sie Fragenvorschläge aus der App annehmen oder ablehnen.
      </template>
    </ModuleIntro>
    <v-card class="mt-2 mb-4">
      <SuggestedQuestionTable />
    </v-card>
    <SuggestedQuestionSidebar />
  </div>
</template>

<script>
import SuggestedQuestionTable from "./SuggestedQuestionTable"
import SuggestedQuestionSidebar from "./SuggestedQuestionSidebar"
import ModuleIntro from "./../partials/global/ModuleIntro"

export default {
  methods: {
    editSuggestedQuestion(suggestedQuestionId) {
      this.$router.push({
        name: 'suggested-questions.edit.general',
        params: {
          suggestedQuestionId,
        },
      })
    },
    loadData() {
      this.$store.dispatch('suggestedQuestions/loadSuggestedQuestions')
    },
  },
  components:{
    SuggestedQuestionTable,
    SuggestedQuestionSidebar,
    ModuleIntro,
  }
}
</script>

<template>
  <v-tabs
    class="c-moduleTabs"
    color="#cecece"
  >
    <v-tab
      :to="{
        name: 'stats.quiz.players'
      }"
      ripple
      exact
    >
      Benutzer
    </v-tab>
    <v-tab
      :to="{
        name: 'stats.quiz.questions'
      }"
      ripple
      exact
    >
      Fragen
    </v-tab>
    <v-tab
      :to="{
        name: 'stats.quiz.categories'
      }"
      ripple
      exact
    >
      Kategorien
    </v-tab>
    <v-tab
      v-if="isFullAdmin"
      :to="{
        name: 'stats.quiz.quizTeams'
      }"
      ripple
      exact
    >
      Quiz-Teams
    </v-tab>
  </v-tabs>
</template>
<script>
import {mapGetters} from "vuex"

export default {
  computed: {
    ...mapGetters({
      isFullAdmin: 'app/isFullAdmin',
      rights: 'app/myRights',
    }),
  },
}
</script>

<template>
  <div>
    <AddQuizTeamModal v-model="quizTeamModalOpen" />
    <v-layout row>
      <v-btn
        color="primary"
        @click="quizTeamModalOpen = true">
        <v-icon
          dark
          left>add
        </v-icon>
        Neues Quiz-Team
      </v-btn>
    </v-layout>
    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout row>
          <v-spacer></v-spacer>
          <v-flex xs4>
            <v-text-field
              append-icon="search"
              clearable
              placeholder="Name / ID"
              single-line
              v-model="search"/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <QuizTeamTable />
    </v-card>
    <QuizTeamSidebar />
  </div>
</template>

<script>
import {debounce} from "lodash"
import AddQuizTeamModal from "./AddQuizTeamModal"
import QuizTeamTable from "./QuizTeamTable"
import QuizTeamSidebar from "./QuizTeamSidebar"

export default {
  data() {
    return {
      quizTeamModalOpen: false,
    }
  },
  watch: {
    search: debounce(function () {
      this.loadData()
    }, 1000),
  },
  computed: {
    search: {
      get() {
        return this.$store.state.quizTeams.search
      },
      set(data) {
        this.$store.commit('quizTeams/setSearch', data)
      },
    },
  },
  methods: {
    loadData() {
      this.$store.dispatch('quizTeams/loadQuizTeams')
    },
  },
  components:{
    AddQuizTeamModal,
    QuizTeamTable,
    QuizTeamSidebar,
  }
}
</script>

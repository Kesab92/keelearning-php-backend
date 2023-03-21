<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <form @submit.prevent="createQuizTeam">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neues Quiz-Team erstellen
        </v-card-title>
        <v-card-text>
          <v-text-field
            v-model="name"
            autofocus
            label="Name"
            hide-details
            required
            class="mb-3"
            box />
          <v-alert
            :value="this.state === QUIZ_TEAM_STATES.STATE_SIMILAR"
            color="warning"
            outline
            class="mt-4">
            Es gibt bereits ein Quiz-Team mit einem ähnlichen Namen.
          </v-alert>
          <v-alert
            :value="this.state === QUIZ_TEAM_STATES.STATE_EQUAL"
            color="error"
            outline
            class="mt-4">
            Es gibt bereits ein Quiz-Team mit diesem Namen.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-btn
            @click="closeModal"
            flat>
            Abbrechen
          </v-btn>
          <v-spacer />
          <v-btn
            :loading="isLoading"
            :disabled="isDisabledButton"
            color="primary"
            type="submit"
            flat>
            Quiz-Team erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </form>
  </v-dialog>
</template>
<script>
import {debounce} from "lodash"

export default {
  props: ['value'],
  data() {
    return {
      QUIZ_TEAM_STATES: {
        STATE_NOT_SIMILAR: 0,
        STATE_EQUAL: 1,
        STATE_SIMILAR: 2,
      },
      isLoading: false,
      name: null,
      state: null,
    }
  },
  watch: {
    name: debounce(function () {
      this.checkName()
    }, 1000),
  },
  computed: {
    dialog: {
      set(value) {
        this.$emit('input', value)
      },
      get() {
        return this.value
      }
    },
    isDisabledButton() {
      if(this.state === this.QUIZ_TEAM_STATES.STATE_EQUAL) {
        return true
      }
      return this.isLoading
    }
  },
  methods: {
    checkName() {
      this.isLoading = true
      axios.get(`/backend/api/v1/quiz-teams/validQuizTeamName?q=${encodeURIComponent(this.name)}`).then(response => {
        this.state = response.data.state
      }).catch(e => {
        alert('Error')
      }).finally(() => {
        this.isLoading = false
      })
    },
    createQuizTeam() {
      if(this.isLoading) {
        return
      }
      this.isLoading = true

      axios.post('/backend/api/v1/quiz-teams', {
        name: this.name,
      }).then(response => {
        this.$router.push({
          name: 'quizTeams.edit.general',
          params: {
            quizTeamId: response.data.quizTeam.id,
          },
        })
        this.$store.dispatch('quizTeams/loadQuizTeams')
        this.closeModal()
      }).catch(e => {
        alert('Error')
      }).finally(() => {
        this.isLoading = false
      })
    },
    closeModal() {
      this.name = null
      this.dialog = false
    },
  },
}
</script>

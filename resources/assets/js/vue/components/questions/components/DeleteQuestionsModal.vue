<template>
  <div class="text-xs-center">
    <v-dialog v-model="isOpen" width="500">
      <v-card>
        <v-card-title class="headline grey lighten-2" primary-title>
          <template v-if="deletionInProgress">
            Fragen werden gelöscht
          </template>
          <template v-else>
            {{ questionIds.length }}
            <template v-if="questionIds.length === 1">Frage</template>
            <template v-else>Fragen</template>
            löschen
          </template>
        </v-card-title>

        <v-card-text v-if="isLoading">
          <v-progress-circular indeterminate></v-progress-circular> Abhängigkeiten werden geladen
        </v-card-text>
        <v-card-text v-else-if="deleteInformationErrors !== null">
          Die folgenden Fragen können nicht gelöscht werden:
          <ul>
            <li
              v-for="(dependencies, question) in deleteInformationErrors"
              :key="question">
              <strong>{{ question }}</strong><br>
              <span
                v-for="item in dependencies"
                :key="item">
                {{ item }}<br>
              </span>
            </li>
          </ul>
          <p class="mt-2">
            <strong>Hinweis:</strong> Fragen, die in einem Test bereits beantwortet wurden, können nicht entfernt werden. Diese Fragen sind nur deaktivierbar.
          </p>
        </v-card-text>
        <v-card-text v-else-if="deletionLoading">
          <v-progress-circular indeterminate></v-progress-circular> Fragen werden gelöscht
        </v-card-text>
        <v-card-text v-else-if="deletionInProgress">
          Die Fragen wurden nun unsichtbar geschalten und als zu Löschen markiert. Es kann einige Minuten dauern bis alle Fragen entfernt sind.
        </v-card-text>
        <v-card-text v-else>
          Die ausgewählten Frage(n) werden gelöscht. Bei dem Löschvorgang werden relevante Abhängigkeiten ebenfalls gelöscht.
          <p>
            Es werden:<br>
            {{ deleteInformation.games }} Spiele,<br>
            {{ deleteInformation.questionAnswers }} Antworten,<br>
            {{ deleteInformation.trainingAnswers }} Trainingsantworten,<br>
            {{ deleteInformation.questionAttachments }} Anhänge gelöscht.
          </p>
          <p>
            Möchten Sie diese Fragen wirklich inklusive Abhängigkeiten löschen?
          </p>
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <template v-if="canContinue">
            <v-btn color="error" @click="confirm">Fragen und Abhängigkeiten löschen</v-btn>
            <v-btn color="secondary" @click="isOpen = false">Abbrechen</v-btn>
          </template>
          <v-btn v-else color="secondary" @click="isOpen = false">Schließen</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
  export default {
    props: {
      questionIds: {
        type: Array,
        required: true
      }
    },
    data() {
      return {
        isOpen: false,
        isLoading: true,
        deleteInformation: null,
        deleteInformationErrors: null,
        deletionLoading: false,
        deletionInProgress: false,
      }
    },
    methods: {
      open() {
        this.isLoading = true
        this.deleteInformation = null
        this.deleteInformationErrors = null
        this.deletionLoading = false
        this.deletionInProgress = false
        axios.post('/backend/api/v1/questions/deleteMultipleInformation', {
          questions: this.questionIds,
        })
          .then((response) => {
            this.isLoading = false
            if(typeof response.data.errors !== 'undefined') {
              this.deleteInformationErrors = response.data.errors
            } else {
              this.deleteInformation = response.data
            }
          })
          .catch(() => {
            alert("Beim Abrufen der Informationen zum Löschen der gewählten Fragen ist ein Fehler aufgetreten.")
          })
        this.isOpen = true
      },
      confirm() {
        this.deletionLoading = true
        this.deletionInProgress = false
        axios.post('/backend/api/v1/questions/deleteMultiple', {
          questions: this.questionIds,
        })
          .then((response) => {
            this.deletionLoading = false
            if(typeof response.data.errors !== 'undefined') {
              this.deleteInformationErrors = response.data.errors
            } else {
              this.deletionLoading = false
              this.deletionInProgress = true
              this.$emit('done')
            }
          })
          .catch(() => {
            alert("Beim Abrufen der Informationen zum Löschen der gewählten Fragen ist ein Fehler aufgetreten.")
          })
      },
    },
    computed: {
      canContinue() {
        return this.deleteInformation !== null && !this.deletionLoading && !this.deletionInProgress
      },
    },
  }
</script>

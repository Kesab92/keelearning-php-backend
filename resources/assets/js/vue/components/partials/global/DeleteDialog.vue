<template>
  <v-dialog
    v-model="dialog"
    width="500"
  >
    <v-card>
      <v-card-title
        class="headline grey lighten-2"
        primary-title
      >
        <template v-if="entryName">
          "{{ entryName }}" löschen
        </template>
        <template v-else>
          {{ typeLabel}} löschen
        </template>
      </v-card-title>

      <v-card-text v-if="isLoading">
        <v-progress-circular
          indeterminate
          color="primary"
          class="mr-2"
        />
        Informationen werden geladen...
      </v-card-text>

      <v-card-text v-else-if="isDeleting">
        <v-progress-circular
          indeterminate
          color="primary"
          class="mr-2"
        />
        Inhalt wird gelöscht...
      </v-card-text>

      <v-card-text v-else-if="errorMessage">
        <v-alert
          :value="true"
          type="warning"
        >
          {{ errorMessage }}
        </v-alert>
      </v-card-text>

      <v-card-text v-else>
        <template v-if="blockers && blockers.length">
          Dieser Inhalt kann aktuell nicht gelöscht werden.
          <ul>
            <li
              v-for="blocker in blockers"
              :key="blocker">
              {{ blocker }}
            </li>
          </ul>
        </template>
        <template v-else-if="dependencies && Object.keys(dependencies).length">
          <slot name="description" />
          <template v-if="Object.keys(dependencies).length">
            Wenn Sie diesen Inhalt löschen, werden außerdem folgende Daten gelöscht:
            <ul>
              <li
                v-for="(items, label) in dependencies"
                :key="label">
                <template v-if="Array.isArray(items)">
                  {{ getLabelTranslation(label) }}
                  <ul>
                    <li
                      v-for="child in items"
                      :key="child">{{ child }}</li>
                  </ul>
                </template>
                <template v-else>
                  {{ getLabelTranslation(label) }}: {{ items }}
                </template>
              </li>
            </ul>
          </template>
        </template>
        <template v-else>
          <slot name="info">
            Dieser Inhalt kann gelöscht werden.
          </slot>
        </template>
        <v-divider
          v-if="$slots['append-message']"
          class="my-3" />
        <slot name="append-message" />
        <template v-if="requireConfirmation">
          <v-divider class="my-3" />
          Geben Sie bitte das Wort DELETE ein, um den Löschvorgang zu bestätigen.
          <v-text-field
            v-model="confirmationInput"
            label="DELETE"
            class="mt-2"
            hide-details
            outline/>
        </template>
      </v-card-text>

      <v-divider/>

      <v-card-actions>
        <v-btn
          v-if="!isLoading && !isDeleting && !this.errorMessage && !(blockers && blockers.length)"
          :disabled="isDisabledButton"
          color="red"
          class="white--text"
          @click="doDeletion"
        >
          {{ typeLabel }} jetzt löschen
        </v-btn>
        <v-spacer/>
        <v-btn
          color="secondary"
          flat
          @click="dialog = false">
          Abbrechen
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: {
    value: {
      type: Boolean,
      required: true,
    },
    typeLabel: {
      type: String,
      required: true,
    },
    entryName: {
      type: String,
      required: false,
    },
    dependencyUrl: {
      type: String,
      required: true,
    },
    deletionUrl: {
      type: String,
      required: true,
    },
    redirectUrl: {
      type: String,
      required: false,
    },
    requireConfirmation: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      isLoading: false,
      isDeleting: false,
      dependencies: null,
      blockers: null,
      errorMessage: null,
      confirmationInput: '',
    }
  },
  computed: {
    dialog: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
    isDisabledButton() {
      if (!this.requireConfirmation) {
        return false
      }
      return this.confirmationInput.trim().toLowerCase() !== 'delete'
    }
  },
  watch: {
    dialog: {
      handler() {
        if(this.dialog) {
          this.loadData()
        } else {
          this.resetDialog()
        }
      },
      immediate: true,
    },
  },
  methods: {
    loadData() {
      this.isLoading = true
      axios.get(this.dependencyUrl).then(response => {
        this.dependencies = response.data.dependencies
        Object.keys(this.dependencies).forEach(dependencyType => {
          if(!this.dependencies[dependencyType] || (Array.isArray(this.dependencies[dependencyType]) && !this.dependencies[dependencyType].length)) {
            delete this.dependencies[dependencyType]
          }
        })
        this.blockers = response.data.blockers
      }).catch(() => {
        this.errorMessage = 'Die Informationen zum Löschen konnten leider nicht abgerufen werden. Bitte versuchen Sie es später erneut.'
      }).finally(() => {
        this.isLoading = false
      })
    },
    resetDialog() {
      this.isLoading = false
      this.isDeleting = false
      this.dependencies = null
      this.blockers = null
      this.errorMessage = null
      this.confirmationInput = ''
    },
    doDeletion() {
      this.isDeleting = true
      axios.delete(this.deletionUrl).then(() => {
        alert(this.typeLabel + ' wurde gelöscht.')
        if(this.redirectUrl) {
          window.location.href = this.redirectUrl
        }
        this.$emit('deleted')
      }).catch(() => {
        this.errorMessage = 'Das Löschen war leider nicht erfolgreich. Bitte versuchen Sie es später erneut.'
      }).finally(() => {
        this.isDeleting = false
      })
    },
    getLabelTranslation(label) {
      // TODO: Move to a real translation system
      const labels = {
        categories: 'Kategorien',
        suggestions: 'Vorgeschlagene Fragen',
        competitions: 'Gewinnspiele',
        games: 'Quiz-Battles',
        runningGames: 'Laufende Quiz-Battles',
        questionAnswers: 'Beantwortete Fragen',
        trainingAnswers: 'Beantwortete Trainings Fragen',
        questionAttachments: 'Fragenanhänge',
        certificateTemplate: 'Zertifikatsvorlagen',
        testQuestions: 'Testfragen',
        submissions: 'Teilnahmen',
        submissionAnswers: 'Antworten',
        reminders: 'Erinnerungen',
      }
      if(typeof labels[label] !== 'undefined') {
        return labels[label]
      }
      return label
    },
  }
}
</script>

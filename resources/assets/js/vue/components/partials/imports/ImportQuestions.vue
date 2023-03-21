<template>
  <div>
    <v-snackbar
      :timeout="6000"
      :top="true"
      color="error"
      v-model="snackbar"
    >
      {{ errorText }}
      <v-btn
        dark
        flat
        @click.native="snackbar = false">OK
      </v-btn>
    </v-snackbar>
    <v-stepper v-model="stepper">
      <v-stepper-header>
        <v-stepper-step
          @click.native="stepper = 1"
          :complete="stepper > 1"
          step="1">Vorbereitung</v-stepper-step>

        <v-divider/>

        <v-stepper-step
          @click.native="stepper = 2"
          :complete="stepper > 2"
          step="2">Vorschau</v-stepper-step>

        <v-divider/>

        <v-stepper-step
          @click.native="stepper = 3"
          step="3">Importieren</v-stepper-step>
      </v-stepper-header>

      <v-stepper-items>
        <v-stepper-content step="1">
          <h6 class="title">Import vorbereiten</h6>
          <div class="subheading mb-4">
            Wählen Sie den Typ, sowie die Kategorie der zu importierenden Fragen. Anschließend wählen Sie Ihre XLSX oder CSV Datei aus.
          </div>
          <v-select
            :items="types"
            v-model="type"
            outline
            label="Fragentyp"
          />

          <CategorySelect
            v-model="category"
            outline
            label="Kategorie"
            limit-to-tag-rights
          />

          <template>
            <v-layout row>
              <v-flex
                grow
                pa-1>
                <template v-if="type && category">
                  <input
                    type="file"
                    @change="setCSV"
                    accept=".csv,.xls,.xlsx"
                    ref="fileInput"
                    class="hidden-screen-only"
                  >
                  <v-btn
                    v-if="!csvParsing"
                    color="primary"
                    @click="openFileInput"
                  >
                    Import Datei wählen
                  </v-btn>
                  <p v-else>
                    Datei wird gelesen...
                  </p>
                  <v-alert
                    v-for="error in parsingErrors"
                    :key="error.code"
                    :value="true"
                    type="error"
                  >
                    {{ error.message }} (Zeile {{ error.row }})
                  </v-alert>
                </template>
              </v-flex>
              <v-flex
                v-if="type"
                shrink
                pa-1>
                <v-btn
                  :href="csvDownloadLink"
                  target="_blank"
                >
                  <v-icon left>cloud_download</v-icon>
                  Vorlage herunterladen
                </v-btn>
              </v-flex>
            </v-layout>
          </template>
        </v-stepper-content>

        <v-stepper-content step="2">
          <h6 class="title">Import überprüfen</h6>
          <div class="subheading mb-4">
            Hier sehen Sie alle zu importierenden Fragen. Bitte achten Sie darauf alle Spalten richtig zuzuordnen.
          </div>
          <SelectionTable
            v-if="headers && questions"
            :is-question-import="true"
            :type="type"
            :headers="headers"
            :available-headers="availableHeaders"
            :items="questions"
            @setHeader="setHeader"
          />
          <v-btn
            class="mt-4"
            @click="stepper = 1"
          >
            Zurück
          </v-btn>
          <v-btn
            color="primary"
            class="ml-0 mt-4"
            @click="stepper = 3"
          >
            Nächster Schritt
          </v-btn>
        </v-stepper-content>

        <v-stepper-content step="3">
          <h6 class="title">Import durchführen</h6>
          <div
            class="subheading mb-4"
            v-if="questions && category">
            <strong>{{ questions.length }} {{ selectedType }} Fragen</strong> werden in die Kategorie <strong>{{ selectedCategory }}</strong> importiert.
          </div>
          <CSVValidator
            v-if="headers && questions"
            :headers="headers"
            :available-headers="availableHeaders"
            :items="questions"
            :general-error-detector="errorDetector"
            :configuration="{}"
            :loading="importIsLoading"
            @goBack="stepper = 2"
            @startImport="startImport"
            type="QUESTIONS"
          />

          <ImportProgressModal
            :open="importId !== null"
            :import-id="importId" />

        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </div>
</template>

<script>
  import SelectionTable from './SelectionTable'
  import ImportProgressModal from "./ImportProgressModal"
  import CSVParser from '../../../logic/CSVParser'
  import questionImportTableHeaders from '../../../logic/import/questionImportTableHeaders'
  import CSVValidator from "./CSVValidator"
  import CategorySelect from "../global/CategorySelect"
  import {mapGetters} from "vuex";

  export default {
    data() {
      return {
        stepper: 1,
        csvParsing: false,
        importIsLoading: false,
        type: null,
        types: [
          {
            text: "Single Choice",
            value: "singlechoice",
          },
          {
            text: "Multiple Choice",
            value: "multiplechoice"
          },
          {
            text: "Richtig/Falsch",
            value: "boolean"
          },
          {
            text: "Lernkarten",
            value: "indexcards",
          }
        ],
        category: null,
        parsingErrors: null,
        headers: null,
        questions: null,
        snackbar: false,
        errorText: null,
        importId: null,
      }
    },
    methods: {
      openFileInput() {
        this.$refs.fileInput.value = ''
        this.$refs.fileInput.click()
      },
      showError(error) {
        this.errorText = error
        this.snackbar = true
      },
      setHeader(data) {
        this.$set(this.headers, data.idx, data.newValue)
      },
      setCSV($event) {
        if ($event.target.files.length > 0) {
          this.csvParsing = true
          this.$nextTick(() => {
            CSVParser.parse($event.target.files[0], data => {

              if(data.errors.length) {
                this.parsingErrors = data.errors
              } else {
                this.errors = null
                data = data.data
                let headers = data.shift()
                this.headers = CSVParser.matchHeaders(headers, this.availableHeaders)
                this.questions = data
                this.stepper = 2
              }
              this.csvParsing = false
            })
          })
        }
      },
      startImport() {
        if (this.importIsLoading) {
          return
        }
        this.importIsLoading = true
        let data = JSON.stringify({
          category: this.category,
          type: this.type,
          headers: this.headers,
          questions: this.questions,
        })
        axios.post('/backend/api/v1/import/questions', { data: data }).then(response => {
          this.importId = response.data.importId
        })
        .catch(() => {
          this.showError('Die Daten konnten nicht importiert werden.')
        })
        .finally(() => {
          this.importIsLoading = false
        })
      },
      errorDetector() {
        // check for max lengths
        let maxLength = this.$constants.QUESTIONS.MAX_LENGTHS.ANSWER

        if(this.type === 'indexcards') {
          maxLength = this.$constants.QUESTIONS.MAX_LENGTHS.INDEX_CARD_ANSWER
        }

        const titleIndex = this.headers.indexOf('question')
        if (this.questions.find(question => question[titleIndex].length > this.$constants.QUESTIONS.MAX_LENGTHS.TITLE)) {
          return `Der Titel mindestens einer Frage überschreitet die Maximallänge von ${this.$constants.QUESTIONS.MAX_LENGTHS.TITLE} Zeichen.`
        }

        const feedbackIndex = this.headers.indexOf('feedback')
        if (feedbackIndex > -1) {
          if (this.questions.find(question => question[feedbackIndex].length > this.$constants.QUESTIONS.MAX_LENGTHS.FEEDBACK)) {
            return `Das Feedback zu mindestens einer Frage überschreitet die Maximallänge von ${this.$constants.QUESTIONS.MAX_LENGTHS.FEEDBACK} Zeichen.`
          }
        }

        const answerIndizes = []
        this.headers.forEach((header, idx) => {
          if (header !== null && header.includes('answer')) {
            answerIndizes.push(idx)
          }
        })
        const questionWithTooLongAnswer = this.questions.find(question => {
          return !!answerIndizes.find(idx => question[idx].length > maxLength)
        })
        if (questionWithTooLongAnswer) {
          return `Mindestens eine der Antworten überschreitet die Maximallänge von ${maxLength} Zeichen.`
        }

        if(this.type === 'multiplechoice') {
          // Find the indizes of the columns which indicate if an answer is right or wrong
          let isCorrectIndizes = []
          this.headers.forEach((header, idx) => {
            if(header !== null && header.substr(-8) === '_correct') {
              isCorrectIndizes.push(idx)
            }
          })

          // Check for a question which has no correct answer
          let questionWithInvalidCorrectIndicators = this.questions.find(question => {
            let hasAnIncorrectIndicator = !!isCorrectIndizes.find(idx => {
              // This check is only relevant if this answer has content. The content is always stored one column before the "correct" column
              if(!question[idx - 1]) {
                return false
              }
              let indicator = parseInt(question[idx])
              return indicator !== 1 && indicator !== 0
            })
            return hasAnIncorrectIndicator
          })
          if(questionWithInvalidCorrectIndicators) {
            return 'Mindestens eine Frage hat ungültige Angaben ob die Antworten richtig oder falsch sind. Gültige Werte sind 0 oder 1. Eventuell ist eine Spalte falsch zugeordnet oder die Daten aus der Datei sind nicht korrekt.'
          }

          // Check for a question which has no correct answer
          let questionWithoutCorrectAnswer = this.questions.find(question => {
            let hasAtLeastOneCorrectAnswer = !!isCorrectIndizes.find(idx => {
              return parseInt(question[idx]) === 1
            })
            return !hasAtLeastOneCorrectAnswer
          })
          if(questionWithoutCorrectAnswer) {
            return 'Mindestens eine Frage hat keine richtige Antwort. Eventuell ist eine Spalte falsch zugeordnet oder die Daten aus der Datei sind nicht korrekt.'
          }

          // See if there is at least one question which has multiple correct answers
          let questionWithMultipleCorrectAnswers = this.questions.find(question => {
            let correctAnswerCount = isCorrectIndizes.filter(idx => {
              return parseInt(question[idx]) === 1
            }).length
            return correctAnswerCount > 1
          })
          if(!questionWithMultipleCorrectAnswers) {
            return 'Keine der Fragen hat mehr als eine richtige Antwort. Sie sollten einen Single-Choice Import durchführen.'
          }
        }
        return null
      }
    },
    computed: {
      ...mapGetters({
        categories: 'categories/categories',
      }),
      availableHeaders() {
        if(!this.type || typeof questionImportTableHeaders[this.type] === 'undefined') {
          return []
        }
        return questionImportTableHeaders[this.type]
      },
      selectedType() {
        if(!this.type) {
          return ''
        }
        let type = this.types.find(type => type.value === this.type)
        if(type) {
          return type.text
        }
      },
      selectedCategory() {
        if(!this.category) {
          return ''
        }
        let category = this.categories.find(category => category.value === this.category)
        if(category) {
          return category.text
        }
      },
      csvDownloadLink() {
        let csvs = {
          singlechoice: '/demo-imports/demo-import-questions-single.xlsx',
          multiplechoice: '/demo-imports/demo-import-questions-multiple.xlsx',
          boolean: '/demo-imports/demo-import-questions-boolean.xlsx',
          indexcards: '/demo-imports/demo-import-questions-learncards.xlsx'
        }
        return csvs[this.type]
      }
    },
    components: {
      CSVValidator,
      SelectionTable,
      ImportProgressModal,
      CategorySelect,
    },
  }
</script>

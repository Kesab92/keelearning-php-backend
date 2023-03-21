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
            Wählen Sie die Kategorie der zu importierenden Karteikarten. Anschließend wählen Sie Ihre CSV Datei aus.
          </div>

          <CategorySelect
            v-model="category"
            outline
            label="Kategorie"
            limit-to-tag-rights
          />

          <template v-if="category">
            <v-layout row>
              <v-flex
                grow
                pa-1>
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
              </v-flex>
              <v-flex
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
            Hier sehen Sie alle zu importierenden Karteikarten. Bitte achten Sie darauf, alle Spalten richtig zuzuordnen.
          </div>
          <SelectionTable
            v-if="headers && indexcards"
            :headers="headers"
            :available-headers="availableHeaders"
            :items="indexcards"
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
            v-if="indexcards && category">
            <strong>{{ indexcards.length }} Karteikarten</strong> werden in die Kategorie <strong>{{ selectedCategory }}</strong> importiert.
          </div>
          <CSVValidator
            v-if="headers && indexcards"
            :headers="headers"
            :available-headers="availableHeaders"
            :items="indexcards"
            :general-error-detector="errorDetector"
            :configuration="{}"
            :loading="importIsLoading"
            @goBack="stepper = 2"
            @startImport="startImport"
            type="CARDS"
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
  import indexcardImportTableHeaders from '../../../logic/import/indexcardImportTableHeaders'
  import CSVValidator from "./CSVValidator"
  import CategorySelect from "../global/CategorySelect"
  import {mapGetters} from "vuex";

  export default {
    data() {
      return {
        stepper: 1,
        csvParsing: false,
        category: null,
        importIsLoading: false,
        parsingErrors: null,
        headers: null,
        indexcards: null,
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
                this.indexcards = data
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
          headers: this.headers,
          indexcards: this.indexcards,
        })
        axios.post('/backend/api/v1/import/indexcards', { data: data }).then(response => {
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
        return null
      }
    },
    computed: {
      ...mapGetters({
        categories: 'categories/categories',
      }),
      availableHeaders() {
        return indexcardImportTableHeaders
      },
      selectedCategory() {
        if(!this.category) {
          return ''
        }
        let category = this.categories.find(category => category.id === this.category)
        if(category) {
          return category.name
        }
      },
      csvDownloadLink() {
        return '/demo-imports/demo-import-index-cards.xlsx'
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

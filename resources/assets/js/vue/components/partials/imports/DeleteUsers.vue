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
        @click.native="snackbar = false"
        dark
        flat>OK
      </v-btn>
    </v-snackbar>
    <v-stepper v-model="stepper">
      <v-stepper-header>
        <v-stepper-step
          :complete="stepper > 1"
          @click.native="stepper = 1"
          step="1">Vorbereitung</v-stepper-step>
        <v-divider />
        <v-stepper-step
          :complete="stepper > 2"
          @click.native="stepper = 2"
          step="2">Vorschau</v-stepper-step>
        <v-divider />
        <v-stepper-step
          @click.native="stepper = 3"
          step="3">Importieren</v-stepper-step>
      </v-stepper-header>

      <v-stepper-items>
        <v-stepper-content step="1">
          <h6 class="title">Import vorbereiten</h6>
          <div class="subheading mb-4">
            Wählen Sie aus anhand von welchem Feld die Benutzer verglichen werden sollen und anschließend eine CSV Datei.
          </div>

          <v-layout>
            <v-flex grow>
              <v-select
                :items="availableCompareHeaders"
                label="Benutzer vergleichen anhand von"
                outline
                v-model="compareHeader"
              />
              <p
                v-show="isComparingByMeta"
                class="orange--text"
              >
                Bitte beachten Sie, dass der Abgleich anhand von unzuverlässigen Feldern zu Datenverlust führen kann.
              </p>
            </v-flex>
            <v-flex shrink>
              <v-tooltip bottom>
                <div
                  class="pa-3"
                  slot="activator"
                  style="cursor: help">
                  <v-icon>info</v-icon>
                </div>
                <span>Wählen Sie ein Feld anhand dessen die Benutzer aus Ihrer CSV Datei mit den bestehenden Benutzern im System abgeglichen werden sollen</span>
              </v-tooltip>
            </v-flex>
          </v-layout>

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
                :key="error.code"
                :value="true"
                type="error"
                v-for="error in parsingErrors"
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
                CSV Vorlage herunterladen
              </v-btn>
            </v-flex>
          </v-layout>
        </v-stepper-content>

        <v-stepper-content step="2">
          <h6 class="title">Import überprüfen</h6>
          <div class="subheading mb-4">
            Hier sehen Sie alle zu löschenden Benutzer. Bitte achten Sie darauf die Spalte zum Benutzerabgleich richtig zuzuordnen.
          </div>
          <SelectionTable
            :available-headers="availableHeaders"
            :headers="headers"
            :items="users"
            @setHeader="setHeader"
            v-if="headers && users"
          />
          <v-btn
            class="mt-4"
            @click="stepper = 1"
          >
            Zurück
          </v-btn>
          <v-btn
            @click="stepper = 3"
            class="ml-0 mt-4"
            color="primary"
          >
            Nächster Schritt
          </v-btn>
        </v-stepper-content>

        <v-stepper-content step="3">
          <h6 class="title">Import durchführen</h6>
          <template v-if="users">
            <div class="subheading mb-4">
              <template v-if="users.length !== 1">Der Import enthält Daten zu {{ users.length }} Benutzern.</template>
              <template v-else>Der Import enthält Daten zu einem Benutzer.</template>
            </div>
            <CSVValidator
              :available-headers="availableHeaders"
              :backend-validator="backendValidation"
              :configuration="configuration"
              :general-error-detector="errorDetector"
              :headers="headers"
              :items="users"
              :loading="importIsLoading"
              :key="csvValidatorKey"
              @goBack="stepper = 2"
              @startImport="startImport"
              v-if="headers && users"
              type="DELETE_USERS"
            />
          </template>
          <template v-else>
            <p>Bitte wählen Sie zuerst eine CSV Datei aus.</p>
          </template>

          <ImportProgressModal
            :open="importId !== null"
            :import-id="importId" />

        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </div>
</template>

<script>
  import SelectionTable from "./SelectionTable"
  import ImportProgressModal from "./ImportProgressModal"
  import CSVParser from "../../../logic/CSVParser"
  import userDeletionTableHeaders from "../../../logic/import/userDeletionTableHeaders"
  import CSVValidator from "./CSVValidator"

  export default {
    data() {
      return {
        stepper: 1,
        csvParsing: false,
        parsingErrors: null,
        headers: null,
        importIsLoading: false,
        users: null,
        snackbar: false,
        errorText: null,
        configurationLoading: false,
        configuration: null,
        compareHeader: "mail",
        importId: null,
      }
    },
    created() {
      this.fetchImportConfiguration()
    },
    methods: {
      fetchImportConfiguration() {
        axios.get("/backend/api/v1/import/configuration/userimport")
          .then((response) => {
            this.configurationLoading = false
            this.configuration = response.data
          })
          .catch(() => {
            this.configurationLoading = false
            this.showError("Die Import Konfiguration konnten nicht vom Server abgerufen werden. Bitte wenden Sie sich an den Support.")
          })
      },
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
              if (data.errors.length) {
                this.parsingErrors = data.errors
              } else {
                this.errors = null
                data = data.data
                let headers = data.shift()
                this.headers = CSVParser.matchHeaders(headers, this.availableHeaders)
                this.users = data
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
          compare_header: this.compareHeader,
          headers: this.headers,
          users: this.users,
        })
        axios.post("/backend/api/v1/import/users-deletion", {data: data}).then(response => {
          this.importId = response.data.importId
        })
        .catch(() => {
          this.showError("Die Daten konnten nicht importiert werden.")
        })
        .finally(() => {
          this.importIsLoading = false
        })
      },
      backendValidation() {
        let data = JSON.stringify({
          compare_header: this.compareHeader,
          headers: this.headers,
          users: this.users,
        })
        return axios.post("/backend/api/v1/import/collect-changes/userdeletion", {data: data}).then(response => {
          return response.data
        })
          .catch((response) => {
            if (typeof response.data !== "undefined" && typeof response.data.errors !== "undefined") {
              this.showError(response.data.errors.join(' '))
            } else {
              this.showError("Die Daten konnten nicht überprüft werden. Bitte kontaktieren Sie den Support.")
            }
          })
      },
      errorDetector() {
        let compareIdx = null
        this.headers.forEach((header, idx) => {
          if(header === this.compareHeader) {
            compareIdx = idx
          }
        })
        if(compareIdx === null) {
          return 'Das Feld "' + this.availableHeaders[this.compareHeader].title + '"für den Vergleich der Benutzer wurde nicht zugewiesen.'
        }
        let existingUserIdentifications = {}
        let duplicateUserIdentification = null
        this.users.forEach(user => {
          if(duplicateUserIdentification !== null) {
            return
          }
          let userIdentification = user[compareIdx]
          if(typeof existingUserIdentifications[userIdentification] === 'undefined') {
            existingUserIdentifications[userIdentification] = true
          } else {
            duplicateUserIdentification = userIdentification
          }
        })
        if(duplicateUserIdentification !== null) {
          return `Der Benutzer mit dem Vergleichswert ${this.availableHeaders[this.compareHeader].title}: "${duplicateUserIdentification}" ist doppelt vorhanden. Eventuell ist die Spalte falsch zugeordnet, oder der Benutzer ist tatsächlich doppelt vorhanden.`
        }
        return null
      },
    },
    computed: {
      availableHeaders() {
        let headers = userDeletionTableHeaders
        if (!this.configuration) {
          return headers
        }

        Object.keys(this.configuration.meta).forEach(metaKey => {
          headers["meta_" + metaKey] = {
            title: "Meta: " + this.configuration.meta[metaKey],
            required: false,
          }
        })

        return headers
      },
      availableCompareHeaders() {
        let availableHeaders = [
          {
            text: "E-Mail Adresse",
            value: "mail",
          },
        ]
        if (!this.configuration) {
          return availableHeaders
        }
        Object.keys(this.configuration.meta).forEach(metaKey => {
          if (!this.configuration.meta[metaKey].compare) {
            return
          }
          availableHeaders.push({
            text: `Meta: ${this.configuration.meta[metaKey].label}`,
            value: `meta_${metaKey}`,
          })
        })
        return availableHeaders
      },
      csvDownloadLink() {
        return "/demo-imports/demo-import-users-delete.xlsx"
      },
      csvValidatorKey() {
        return this.compareHeader
      },
      isComparingByMeta() {
        return this.compareHeader.indexOf('meta_') === 0
      },
    },
    components: {
      CSVValidator,
      SelectionTable,
      ImportProgressModal,
    },
  }
</script>
